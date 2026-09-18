<?php

namespace App\Jobs;

use App\Models\AiJdParse;
use App\Models\AiMatch;
use App\Models\AiMatchRun;
use App\Models\AiResumeParse;
use App\Models\Project;
use App\Models\Talent;
use App\Services\AiParsingService;
use App\Services\ProjectRequirementService;
use App\Support\ProjectLanguages;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/** Score every parsed candidate against one project. */
class ScoreProjectMatches implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 900;

    /** @return array<int, int> */
    public array $backoff = [30, 120];

    /** Talents pulled per batch — bounds memory on a large pool. */
    private const CHUNK = 100;

    public function __construct(
        public int $projectId,
        public bool $force = false,
        /** The run this scoring belongs to, when a recruiter started one. */
        public ?int $runId = null,
    ) {
    }

    /** One scoring run per project in flight. */
    public function uniqueId(): string
    {
        return $this->runId !== null
            ? "{$this->projectId}:run:{$this->runId}"
            : (string) $this->projectId;
    }

    public function handle(AiParsingService $parser, ProjectRequirementService $requirements): void
    {
        $jdParse = AiJdParse::firstWhere('project_id', $this->projectId);
        if (! $jdParse) {
            // The JD has not been structured yet. Parsing dispatches this job
            // again on completion, so there is nothing to wait for here.
            Log::info('ai.match.skipped_no_jd', ['project_id' => $this->projectId]);

            // A run waiting on this must still be closed out, or it sits at
            // "running" forever and no email is ever sent.
            $this->finalizeRun(__('interview.match_run.no_jd'));

            return;
        }

        $project = Project::find($this->projectId);

        if (! $project) {
            $this->finalizeRun(__('interview.match_run.project_gone'));

            return;
        }

        // The gate, read once for the whole pool.
        $mandatory = $requirements->mandatoryPayload($project);

        // Fold the project form's language choice into the parsed JD.
        $jdPayload = $this->withFormLanguages($jdParse->payload, $project);

        $existing = AiMatch::where('project_id', $this->projectId)
            ->get()
            ->keyBy('talent_id');

        // A changed gate invalidates every stored verdict.
        $gateHash = hash('sha256', json_encode($mandatory, JSON_UNESCAPED_UNICODE));

        // A score stored before gating existed carries no fingerprint at all.
        $emptyGateHash = hash('sha256', json_encode([], JSON_UNESCAPED_UNICODE));

        $gateChanged = $existing->contains(
            fn (AiMatch $m) => ($m->payload['gate_hash'] ?? $emptyGateHash) !== $gateHash
        );

        if ($gateChanged) {
            Log::info('ai.match.gate_changed', [
                'project_id' => $this->projectId,
                'mandatory' => count($mandatory),
            ]);
        }

        $rescoreAll = $this->force || $gateChanged;

        $scored = $skipped = $failed = 0;

        AiResumeParse::query()
            ->orderBy('talent_id')
            ->chunkById(self::CHUNK, function ($parses) use (
                $parser, $jdParse, $jdPayload, $existing, $mandatory, $gateHash, $rescoreAll,
                &$scored, &$skipped, &$failed
            ) {
                $talents = Talent::with(['locations:id', 'subCategories:id'])
                    ->whereIn('id', $parses->pluck('talent_id'))
                    ->get()
                    ->keyBy('id');

                foreach ($parses as $resumeParse) {
                    $talent = $talents->get($resumeParse->talent_id);
                    if (! $talent) {
                        continue;
                    }

                    $current = $existing->get($resumeParse->talent_id);
                    if (! $rescoreAll
                        && $current
                        && ! $current->isStale($jdParse->source_hash, $resumeParse->source_hash)) {
                        $skipped++;

                        continue;
                    }

                    try {
                        $result = $parser->match(
                            $jdPayload,
                            $resumeParse->payload,
                            $talent,
                            $mandatory
                        );
                    } catch (RuntimeException $e) {
                        // One unscoreable candidate must not abandon the pool.
                        $failed++;
                        Log::warning('ai.match.candidate_failed', [
                            'project_id' => $this->projectId,
                            'talent_id' => $resumeParse->talent_id,
                            'error' => $e->getMessage(),
                        ]);

                        continue;
                    }

                    AiMatch::updateOrCreate(
                        [
                            'project_id' => $this->projectId,
                            'talent_id' => $resumeParse->talent_id,
                        ],
                        [
                            'score' => (int) ($result['score'] ?? 0),
                            // Promoted out of the payload so the results
                            // screen's default view is one indexed read.
                            'meets_mandatory' => (bool) ($result['meets_mandatory'] ?? true),
                            'unverified_mandatory' => (int) ($result['unverified_mandatory'] ?? 0),
                            // The gate this verdict was reached under, so the
                            // next run can tell whether it still applies.
                            'payload' => $result + ['gate_hash' => $gateHash],
                            'scorer_version' => $result['scorer_version'] ?? 'unknown',
                            'jd_source_hash' => $jdParse->source_hash,
                            'resume_source_hash' => $resumeParse->source_hash,
                            'scored_at' => now(),
                        ]
                    );
                    $scored++;
                }
            }, 'talent_id');

        Log::info('ai.match.completed', [
            'project_id' => $this->projectId,
            'scored' => $scored,
            'skipped_fresh' => $skipped,
            'failed' => $failed,
        ]);

        $this->finalizeRun();
    }

    /**
     * The parsed JD, plus any language the project form requires that the prose extraction did not already list.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withFormLanguages(array $payload, Project $project): array
    {
        $formLanguages = ProjectLanguages::forProject($project);

        if ($formLanguages === []) {
            return $payload;
        }

        $existing = [];

        foreach ($payload['languages'] ?? [] as $entry) {
            $existing[$this->languageKey((string) ($entry['language'] ?? ''))] = true;
        }

        foreach ($formLanguages as $name) {
            if (isset($existing[$this->languageKey($name)])) {
                continue;
            }

            $payload['languages'][] = [
                'language' => $name,
                // No level demanded: the form asks which language, not how
                // well. The scorer reads a null level as "speaking it at all
                // is the bar".
                'level' => null,
                // The wire contract requires evidence on every language entry,
                // and it has to be truthful about where this came from — it is
                // not a span from the job description, because the job
                // description never said it.
                'evidence' => __('interview.requirement.from_project_form'),
            ];
        }

        return $payload;
    }

    /** Compare two language names the way the scorer does. */
    private function languageKey(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/[^\p{L}]+/u', '', $name) ?? $name));
    }

    /**
     * Close out the run this scoring belonged to, and email the recruiter.
     *
     * @param  string|null  $failureReason  set when the run could not score
     */
    private function finalizeRun(?string $failureReason = null): void
    {
        if ($this->runId === null) {
            return;
        }

        $run = AiMatchRun::find($this->runId);

        if (! $run || $run->isFinished()) {
            // Already closed by a previous attempt. Returning here is what
            // stops a queue retry from sending a second summary email.
            return;
        }

        if ($failureReason !== null) {
            $run->forceFill([
                'status' => AiMatchRun::STATUS_FAILED,
                'failure_reason' => $failureReason,
                'completed_at' => now(),
            ])->save();

            return;
        }

        $threshold = (int) $run->threshold;

        $totals = AiMatch::query()
            ->where('project_id', $this->projectId)
            ->selectRaw('COUNT(*) as scored')
            ->selectRaw(
                'SUM(CASE WHEN score >= ? AND meets_mandatory = 1 THEN 1 ELSE 0 END) as matched',
                [$threshold]
            )
            // Needs-review is scoped to candidates who would otherwise have
            // qualified. A candidate far below the threshold with an
            // unverifiable must-have is not someone the recruiter has to
            // adjudicate — they were not going to be invited either way.
            ->selectRaw(
                'SUM(CASE WHEN score >= ? AND meets_mandatory = 0 '
                .'AND unverified_mandatory > 0 THEN 1 ELSE 0 END) as needs_review',
                [$threshold]
            )
            ->first();

        $run->forceFill([
            'status' => AiMatchRun::STATUS_COMPLETED,
            'scored' => (int) ($totals->scored ?? 0),
            'matched' => (int) ($totals->matched ?? 0),
            'needs_review' => (int) ($totals->needs_review ?? 0),
            'completed_at' => now(),
        ])->save();

        NotifyMatchRunComplete::dispatch($run->id);
    }

    public function failed(Throwable $e): void
    {
        Log::error('ai.match.failed', [
            'project_id' => $this->projectId,
            'error' => $e->getMessage(),
        ]);

        // The run is waiting on a job that is not coming back. Left alone it
        // would sit at "running" indefinitely, and the recruiter would keep
        // refreshing a page that never changes.
        $this->finalizeRun($e->getMessage());
    }
}
