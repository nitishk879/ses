<?php

namespace App\Jobs;

use App\Models\AiMatchRun;
use App\Notifications\MatchRunCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Tell the recruiter their matching run finished, once. */
class NotifyMatchRunComplete implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public function __construct(public int $runId)
    {
    }

    public function handle(): void
    {
        $run = AiMatchRun::with(['project', 'user'])->find($this->runId);

        if (! $run) {
            return; // the project was deleted while this sat in the queue
        }

        if (! $run->user || blank($run->user->email)) {
            // A run started from the console has nobody to write to. Not an
            // error — the results are on the dashboard either way.
            Log::info('ai.match_run.no_recipient', ['run_id' => $run->id]);

            return;
        }

        // Claim the send. Exactly one caller sees a non-zero result.
        $claimed = DB::table('ai_match_runs')
            ->where('id', $run->id)
            ->whereNull('notified_at')
            ->update(['notified_at' => now()]);

        if ($claimed === 0) {
            Log::info('ai.match_run.already_notified', ['run_id' => $run->id]);

            return;
        }

        $run->user->notify(new MatchRunCompleted($run));

        Log::info('ai.match_run.notified', [
            'run_id' => $run->id,
            'project_id' => $run->project_id,
            'matched' => $run->matched,
            'needs_review' => $run->needs_review,
        ]);
    }

    public function failed(Throwable $e): void
    {
        // Release the claim so a manual re-dispatch can still send. The run
        // itself stays completed — the results are not in doubt, only the
        // notice about them.
        DB::table('ai_match_runs')
            ->where('id', $this->runId)
            ->update(['notified_at' => null]);

        Log::error('ai.match_run.notify_failed', [
            'run_id' => $this->runId,
            'error' => $e->getMessage(),
        ]);
    }
}
