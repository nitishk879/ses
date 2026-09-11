<?php

namespace App\Jobs;

use App\Enums\InterviewAttemptStatus;
use App\Exceptions\Interview\InterviewAiBusy;
use App\Exceptions\Interview\InterviewAiUnavailable;
use App\Models\InterviewAttempt;
use App\Services\InterviewAttemptLifeCycleService;
use App\Services\InterviewOrchestrator;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Places one interview call.
 *
 * Task 9's "start the call automatically at the selected time" ends here: the
 * scheduler decides *when*, this does the *what*.
 *
 * `ShouldBeUnique` is load-bearing rather than tidy. Without it a scheduler
 * tick that overlaps a slow previous tick would queue the same attempt twice
 * and phone the candidate twice, which is the kind of bug they notice and
 * complain about.
 */
class StartInterviewAttemptJob implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Retries here are for reaching the AI service, never for re-dialling. A
     * call that was placed and went unanswered is a *new attempt* with its own
     * row, because it is a second time the candidate's phone rang.
     */
    public int $tries = 3;

    /**
     * Widening, not uniform.
     *
     * The two retryable failures need different waits. An unreachable AI
     * service usually recovers in seconds; a busy line only frees up when a
     * call ends, which is minutes. One 60-second backoff served the first case
     * and burned all three attempts before the second could possibly clear.
     *
     * @var array<int, int>
     */
    public array $backoff = [60, 180];

    /** Long enough for planning (an LLM round trip) plus the dial. */
    public int $timeout = 180;

    /**
     * Guards against a duplicate dial for a whole interview, not just a tick.
     */
    public int $uniqueFor = 600;

    public function __construct(
        public int $interviewAttemptId
    ) {
    }

    public function uniqueId(): string
    {
        return 'interview-attempt-'.$this->interviewAttemptId;
    }

    public function handle(
        InterviewOrchestrator $orchestrator,
        InterviewAttemptLifeCycleService $lifecycle,
    ): void {
        $attempt = InterviewAttempt::find($this->interviewAttemptId);

        if (! $attempt) {
            return;
        }

        // Someone cancelled it between scheduling and running, or a previous
        // try already got through. Either way the phone must not ring.
        if (! in_array($attempt->status, [
            InterviewAttemptStatus::PENDING,
            InterviewAttemptStatus::STARTING,
        ], true)) {
            Log::info('interview.start_skipped', [
                'attempt_id' => $attempt->id,
                'status' => $attempt->status->value,
            ]);

            return;
        }

        if ($attempt->status === InterviewAttemptStatus::PENDING) {
            $attempt = $lifecycle->start($attempt);
        }

        $attempt = $orchestrator->start($attempt);

        if (filled($attempt->call_sid)) {
            // The voicebot has no post-call webhook, so the outcome has to be
            // asked for. The first poll is delayed by roughly the call length:
            // polling a call that has just started only produces `pending`.
            PollInterviewCallJob::dispatch($attempt->id)
                ->delay(now()->addSeconds(
                    (int) config('services.interview.poll_interval_seconds', 20)
                ));
        }
    }

    public function failed(Throwable $exception): void
    {
        $attempt = InterviewAttempt::find($this->interviewAttemptId);

        if (! $attempt) {
            return;
        }

        $reason = match (true) {
            $exception instanceof InterviewAiBusy => __('interview.all_lines_busy'),
            $exception instanceof InterviewAiUnavailable => __('interview.ai_unavailable'),
            default => $exception->getMessage(),
        };

        // The orchestrator already fails the attempt for non-retryable
        // problems; this covers the case where every retry was exhausted.
        if (! in_array($attempt->status, [
            InterviewAttemptStatus::FAILED,
            InterviewAttemptStatus::NO_ANSWER,
            InterviewAttemptStatus::COMPLETED,
            InterviewAttemptStatus::CANCELLED,
        ], true)) {
            app(InterviewAttemptLifeCycleService::class)->fail($attempt, $reason);
        }

        Log::error('interview.start_failed', [
            'attempt_id' => $this->interviewAttemptId,
            'reason' => $reason,
        ]);
    }
}
