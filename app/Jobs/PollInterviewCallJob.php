<?php

namespace App\Jobs;

use App\Enums\InterviewAttemptStatus;
use App\Exceptions\Interview\InterviewAiUnavailable;
use App\Models\InterviewAttempt;
use App\Services\InterviewAttemptLifeCycleService;
use App\Services\InterviewOrchestrator;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Asks how a placed call ended, and re-queues itself until it knows.
 *
 * The voicebot exposes no post-call webhook, so there is nothing to subscribe
 * to and polling is the only option available without changing that repo.
 *
 * Self-rescheduling rather than a loop with sleeps: a worker blocked for five
 * minutes inside one job is a worker not doing anything else, and a deploy
 * during that window loses the call's outcome entirely. Each poll is a short
 * job that either finishes the attempt or queues the next one, so the state
 * lives in the database where a restart can pick it up.
 *
 * **`ShouldBeUniqueUntilProcessing`, not `ShouldBeUnique`.** This distinction
 * is the difference between polling working and polling stopping dead after
 * one attempt. A plain unique job holds its lock until `handle()` returns
 * (`CallQueuedHandler::call()` releases it after the middleware pipeline), so
 * the next poll — dispatched from *inside* `handle()` with the same unique id
 * — is silently refused. The call then completes, nobody reads the result, and
 * the attempt sits IN_PROGRESS forever with a transcript that is never stored.
 * `UntilProcessing` releases the lock before `handle()` runs, which still
 * prevents two identical polls sitting in the queue at once while allowing
 * this one to queue its own successor.
 *
 * Worth knowing when testing this: `Queue::fake()` does not model the unique
 * lock at all, so the broken version passes a faked-queue assertion. Only a
 * real dispatch catches it — see InterviewPollChainTest.
 */
class PollInterviewCallJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Reaching the AI service. The polling itself is the re-dispatch below. */
    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 60;

    public int $uniqueFor = 120;

    public function __construct(
        public int $interviewAttemptId
    ) {
    }

    public function uniqueId(): string
    {
        return 'interview-poll-'.$this->interviewAttemptId;
    }

    public function handle(
        InterviewOrchestrator $orchestrator,
        InterviewAttemptLifeCycleService $lifecycle,
    ): void {
        $attempt = InterviewAttempt::find($this->interviewAttemptId);

        if (! $attempt) {
            return;
        }

        // Already resolved — by a later poll that overtook this one, or by an
        // operator marking it by hand.
        if (in_array($attempt->status, [
            InterviewAttemptStatus::COMPLETED,
            InterviewAttemptStatus::NO_ANSWER,
            InterviewAttemptStatus::FAILED,
            InterviewAttemptStatus::CANCELLED,
        ], true)) {
            return;
        }

        $maxAttempts = (int) config('services.interview.poll_max_attempts', 45);

        if ($attempt->poll_count >= $maxAttempts) {
            // The call outlived every reasonable expectation of it. Stopping
            // is right, but the reason must say it was *us* who gave up —
            // otherwise this is indistinguishable from a candidate who never
            // picked up, and it would consume one of their retry attempts.
            Log::error('interview.poll_exhausted', [
                'attempt_id' => $attempt->id,
                'polls' => $attempt->poll_count,
                'call_sid' => $attempt->call_sid,
            ]);

            $lifecycle->fail($attempt, __('interview.poll_timeout'));

            return;
        }

        try {
            $finished = $orchestrator->poll($attempt);
        } catch (InterviewAiUnavailable $e) {
            // Transient: let the job's own retry handle it. Re-queuing a fresh
            // poll here instead would reset the backoff and hammer a service
            // that is already struggling.
            throw $e;
        }

        if ($finished) {
            $attempt->refresh();

            // Evaluation is queued separately so a scoring failure cannot lose
            // the transcript we just stored — it is already committed.
            if ($attempt->status === InterviewAttemptStatus::COMPLETED
                && $attempt->hasScreeningTranscript()) {
                EvaluateInterviewAttemptJob::dispatch($attempt->id);
            }

            return;
        }

        self::dispatch($this->interviewAttemptId)
            ->delay(now()->addSeconds(
                (int) config('services.interview.poll_interval_seconds', 20)
            ));
    }

    /**
     * Every retry is spent and the outcome is still unknown.
     *
     * Without this the attempt would sit IN_PROGRESS with no poll queued and
     * nothing left to move it — a permanent limbo produced by a transient
     * outage, invisible on any screen because nothing reports a row that is
     * merely *stuck*. The attempt is failed instead, with a reason that says
     * the poll gave up rather than that the candidate did.
     */
    public function failed(Throwable $exception): void
    {
        $attempt = InterviewAttempt::find($this->interviewAttemptId);

        if (! $attempt) {
            return;
        }

        if (in_array($attempt->status, [
            InterviewAttemptStatus::COMPLETED,
            InterviewAttemptStatus::NO_ANSWER,
            InterviewAttemptStatus::FAILED,
            InterviewAttemptStatus::CANCELLED,
        ], true)) {
            return;
        }

        Log::error('interview.poll_abandoned', [
            'attempt_id' => $this->interviewAttemptId,
            'call_sid' => $attempt->call_sid,
            'error' => $exception->getMessage(),
        ]);

        app(InterviewAttemptLifeCycleService::class)
            ->fail($attempt, __('interview.poll_timeout'));
    }
}
