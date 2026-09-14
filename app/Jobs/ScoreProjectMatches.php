<?php

namespace App\Jobs;

use App\Models\AiJdParse;
use App\Models\AiMatch;
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
 * Score every parsed candidate against one project.
 *
 * Scoring makes no language-model call — it is arithmetic over two stored
 * parses — so a whole pool can be re-scored cheaply. It is queued anyway
 * because it is N HTTP round trips, and because the same job re-runs whenever
 * either side is re-parsed.
 *
 * Skips work it does not need: a stored score whose two source hashes still
 * match the current parses is already the answer.
 */
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
    ) {
    }

    /**
     * One scoring run per project in flight.
     *
     * `UntilProcessing`, not `ShouldBeUnique`: the lock is released when the
     * job starts rather than when it finishes, so a candidate parsed *during*
     * a scoring run still queues a re-score instead of being silently dropped
     * by a lock the running job is still holding. Getting this wrong is how a
     * newly parsed CV ends up permanently unscored.
     */
    public function uniqueId(): string
    {
        return (string) $this->projectId;
    }

    public function handle(AiParsingService $parser): void
    {
        $jdParse = AiJdParse::firstWhere('project_id', $this->projectId);
        if (! $jdParse) {
            // The JD has not been structured yet. Parsing dispatches this job
            // again on completion, so there is nothing to wait for here.
            Log::info('ai.match.skipped_no_jd', ['project_id' => $this->projectId]);

            return;
        }

        $existing = AiMatch::where('project_id', $this->projectId)
            ->get()
            ->keyBy('talent_id');

        $scored = $skipped = $failed = 0;

        AiResumeParse::query()
            ->orderBy('talent_id')
            ->chunkById(self::CHUNK, function ($parses) use (
                $parser, $jdParse, $existing, &$scored, &$skipped, &$failed
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
                    if (! $this->force
                        && $current
                        && ! $current->isStale($jdParse->source_hash, $resumeParse->source_hash)) {
                        $skipped++;

                        continue;
                    }

                    try {
                        $result = $parser->match(
                            $jdParse->payload,
                            $resumeParse->payload,
                            $talent
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
                            'payload' => $result,
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
    }

    public function failed(Throwable $e): void
    {
        Log::error('ai.match.failed', [
            'project_id' => $this->projectId,
            'error' => $e->getMessage(),
        ]);
    }
}
