<?php

namespace App\Jobs;

use App\Models\AiJdParse;
use App\Models\AiResumeParse;
use App\Models\Talent;
use App\Services\AiParsingService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Structure a candidate's resume into skills and experience.
 *
 * Slower than the JD parse — measured at 4.6-8.8s, and longer again for a CV
 * that has to be split across several model calls to fit the 8k context.
 */
class ParseTalentResume implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** Generous: a long skill sheet is several sequential model calls. */
    public int $timeout = 600;

    /** @return array<int, int> */
    public array $backoff = [30, 120];

    public function __construct(
        public int $talentId,
        public bool $force = false,
    ) {
    }

    public function uniqueId(): string
    {
        return (string) $this->talentId;
    }

    public function handle(AiParsingService $parser): void
    {
        $talent = Talent::find($this->talentId);
        if (! $talent) {
            return;
        }

        // Resolve the source once: it is either a CV file or, when none is
        // readable, the candidate's own profile. Hashing it before sending
        // means an unchanged candidate never costs a second parse, and a
        // resume parse is the most expensive call in the pipeline (several
        // sequential model requests for a long skill sheet).
        $source = $parser->resumeSource($talent);
        if ($source === null) {
            // Genuinely nothing to work with — no readable file and a blank
            // profile. Not retryable, and not an error either: it is a
            // candidate who has not filled anything in.
            Log::warning('ai.resume_parse.no_source', ['talent_id' => $this->talentId]);

            return;
        }

        $inputHash = $source['hash'];

        $existing = AiResumeParse::firstWhere('talent_id', $this->talentId);
        if (! $this->force && $existing && $existing->source_hash === $inputHash) {
            Log::info('ai.resume_parse.unchanged', ['talent_id' => $this->talentId]);

            return;
        }

        try {
            $result = $parser->parseResume($talent, $source);
        } catch (RuntimeException $e) {
            // A scanned or missing CV is a data problem, not a transient one.
            // Retrying burns model time and always lands in the same place, so
            // record it and stop.
            Log::warning('ai.resume_parse.unusable', [
                'talent_id' => $this->talentId,
                'reason' => $e->getMessage(),
            ]);
            $this->fail($e);

            return;
        }

        // Record what this was read from. A score derived from a one-line
        // profile and one derived from a six-page CV are not equally
        // trustworthy, and the recruiter is entitled to see which they are
        // looking at — so the provenance is stored next to the result rather
        // than inferred later from whether a file happens to exist today.
        $result['meta']['source_kind'] = $source['kind'];

        AiResumeParse::updateOrCreate(
            ['talent_id' => $this->talentId],
            [
                'parser_version' => $result['meta']['parser_version'] ?? 'unknown',
                'source_hash' => $inputHash,
                'payload' => $result,
                'parsed_at' => now(),
            ]
        );

        // Shapes only — never the extracted content, which is personal data.
        Log::info('ai.resume_parse.stored', [
            'talent_id' => $this->talentId,
            'source_kind' => $source['kind'],
            'skills' => count($result['skills'] ?? []),
            'truncated' => $result['meta']['truncated'] ?? false,
        ]);

        // This candidate now has a parse they did not have a moment ago, and
        // nothing else will notice. {@see ParseProjectJd} re-scores when the
        // *JD* side changes; without the mirror image of that here, a newly
        // parsed candidate stays unscored until somebody happens to edit the
        // project — which is exactly how a freshly added candidate ends up
        // invisible on a shortlist.
        //
        // Scoring is arithmetic over two stored parses and makes no model
        // call, so this is cheap; and the job is unique-until-processing, so a
        // batch of parses collapses into one run per project instead of one
        // per candidate.
        AiJdParse::query()->pluck('project_id')->each(
            fn ($projectId) => ScoreProjectMatches::dispatch((int) $projectId)
        );
    }

    public function failed(Throwable $e): void
    {
        Log::error('ai.resume_parse.failed', [
            'talent_id' => $this->talentId,
            'error' => $e->getMessage(),
        ]);
    }
}
