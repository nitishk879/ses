<?php

namespace App\Jobs;

use App\Models\AiJdParse;
use App\Models\Project;
use App\Services\AiParsingService;
use App\Services\ProjectRequirementService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Structure a project's job description into skills and requirements. */
class ParseProjectJd implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    /** Batched alongside the resume parses by a matching run. */
    use Batchable, Queueable;

    public int $tries = 3;

    /** Give the model room to answer before a retry piles a second call on. */
    public int $timeout = 180;

    /**
     * Spread retries out.
     *
     * @return array<int, int>
     */
    public array $backoff = [30, 120];

    public function __construct(
        public int $projectId,
        public bool $force = false,
    ) {
    }

    /** One job per project in flight; a burst of edits must not fan out. */
    public function uniqueId(): string
    {
        return (string) $this->projectId;
    }

    public function handle(AiParsingService $parser): void
    {
        $project = Project::find($this->projectId);
        if (! $project) {
            return; // deleted while queued — nothing to do
        }

        // Decide BEFORE calling: the fingerprint is computed from what we are
        // about to send, so an unchanged JD costs nothing at all. Comparing
        // the hash the service returns would mean paying for the parse first.
        //
        // A parser upgrade is not visible from here, so re-parsing after one
        // is an explicit --force rather than something guessed at.
        $inputHash = $parser->projectInputHash($project);
        $existing = AiJdParse::firstWhere('project_id', $this->projectId);

        if (! $this->force && $existing && $existing->source_hash === $inputHash) {
            // No language-model call needed — but the requirement list is still reconciled.
            app(ProjectRequirementService::class)->syncFromParse($project, $existing);

            Log::info('ai.jd_parse.unchanged', ['project_id' => $this->projectId]);

            return;
        }

        $result = $parser->parseProject($project);

        $parse = AiJdParse::updateOrCreate(
            ['project_id' => $this->projectId],
            [
                'parser_version' => $result['meta']['parser_version'] ?? 'unknown',
                'source_hash' => $inputHash,
                'payload' => $result,
                'parsed_at' => now(),
            ]
        );

        // Reconcile the recruiter-facing requirement list against what was just extracted.
        app(ProjectRequirementService::class)->syncFromParse($project, $parse);

        Log::info('ai.jd_parse.stored', [
            'project_id' => $this->projectId,
            'required' => count($result['required_skills'] ?? []),
            'unmapped' => count($result['unmapped_skills'] ?? []),
        ]);

        // The JD changed, so every score derived from it is now explaining a
        // document that no longer says what it quoted.
        ScoreProjectMatches::dispatch($this->projectId);
    }

    public function failed(Throwable $e): void
    {
        // No document text in the log line — a JD can carry client names.
        Log::error('ai.jd_parse.failed', [
            'project_id' => $this->projectId,
            'error' => $e->getMessage(),
        ]);
    }
}
