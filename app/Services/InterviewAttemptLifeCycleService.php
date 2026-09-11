<?php

namespace App\Services;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewStatus;
use App\Models\InterviewAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LogicException;

class InterviewAttemptLifeCycleService
{
    /**
     * Fallback for the first-screening window from the task sheet.
     *
     * Only used when `services.interview.duration_seconds` is unset. It is not
     * a clamp applied to stored data — see {@see complete()} — but the
     * threshold that decides whether a finished call overran.
     */
    public const DEFAULT_MAX_DURATION_SECONDS = 300;

    /**
     * The window this deployment actually plans its interviews against.
     *
     * Read from config rather than fixed, because the *planned* length already
     * is: `INTERVIEW_DURATION_SECONDS` drives the question budget on the AI
     * side. Leaving this constant at 300 while that moved to, say, 420 would
     * have flagged every single normal call as a two-minute overrun — a
     * warning that fires always is one that gets ignored, including on the day
     * something is genuinely wrong.
     */
    private function maxDurationSeconds(): int
    {
        return (int) config(
            'services.interview.duration_seconds',
            self::DEFAULT_MAX_DURATION_SECONDS
        );
    }

    public function start(InterviewAttempt $attempt): InterviewAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $this->ensureStatus(
                $attempt,
                InterviewAttemptStatus::PENDING
            );

            $attempt->update([
                'status' => InterviewAttemptStatus::STARTING,
            ]);

            return $attempt->fresh();
        });
    }

    public function beginInterview(
        InterviewAttempt $attempt
    ): InterviewAttempt {
        return DB::transaction(function () use ($attempt) {
            $this->ensureStatus(
                $attempt,
                InterviewAttemptStatus::STARTING
            );

            $attempt->update([
                'status' => InterviewAttemptStatus::IN_PROGRESS,
                'started_at' => now(),
            ]);

            return $attempt->fresh();
        });
    }

    /**
     * Mark an attempt finished and record how long it actually ran.
     *
     * `$measuredDuration` is the call length as reported by the telephony
     * provider. Prefer it when present: the gap between our own `started_at`
     * and `now()` includes queue latency and the time the poller took to
     * notice the call had ended, neither of which the candidate was on the
     * phone for.
     *
     * **The duration stored is the real one.** This used to be
     * `min($duration, 300)`, which does not shorten a call — it only makes the
     * record lie about one, writing 300 for a seven-minute interview. The
     * five-minute limit is enforced where it can actually be enforced: the
     * question budget is computed from the window before the call is placed,
     * and the voicebot is handed a hard `max_call_duration_seconds` that cuts
     * the line. An overrun that reaches here is a fact worth keeping, so it is
     * stored and flagged rather than rounded away.
     */
    public function complete(
        InterviewAttempt $attempt,
        ?int $measuredDuration = null
    ): InterviewAttempt {
        return DB::transaction(function () use ($attempt, $measuredDuration) {
            $this->ensureStatus(
                $attempt,
                InterviewAttemptStatus::IN_PROGRESS
            );

            $endedAt = now();

            $duration = $measuredDuration ?? ($attempt->started_at
                ? (int) $attempt->started_at->diffInSeconds($endedAt)
                : null);

            $duration = $duration !== null ? max(0, $duration) : null;

            $attributes = [
                'status' => InterviewAttemptStatus::COMPLETED,
                'ended_at' => $endedAt,
                'duration_seconds' => $duration,
            ];

            $limit = $this->maxDurationSeconds();

            if ($duration !== null && $duration > $limit) {
                // Surfaced rather than silently corrected: an interview that
                // overran the window means the budget or the voicebot's cap
                // did not hold, and that is worth someone looking at.
                $attributes['metadata'] = array_merge(
                    $attempt->metadata ?? [],
                    [
                        'duration_overrun_seconds' => $duration - $limit,
                        'max_duration_seconds' => $limit,
                    ]
                );

                Log::warning('interview.duration_overrun', [
                    'interview_attempt_id' => $attempt->id,
                    'duration_seconds' => $duration,
                    'limit_seconds' => $limit,
                ]);
            }

            $attempt->update($attributes);

            return $attempt->fresh();
        });
    }

    public function noAnswer(
        InterviewAttempt $attempt,
        ?string $reason = null
    ): InterviewAttempt {
        return DB::transaction(function () use ($attempt, $reason) {
            $this->ensureNotFinished($attempt);

            $attempt->update([
                'status' => InterviewAttemptStatus::NO_ANSWER,
                'ended_at' => now(),
                'failure_reason' => $reason,
            ]);

            return $attempt->fresh();
        });
    }

    public function fail(
        InterviewAttempt $attempt,
        string $reason
    ): InterviewAttempt {
        return DB::transaction(function () use ($attempt, $reason) {
            $this->ensureNotFinished($attempt);

            $attempt->update([
                'status' => InterviewAttemptStatus::FAILED,
                'ended_at' => now(),
                'failure_reason' => $reason,
            ]);

            return $attempt->fresh();
        });
    }

    public function cancel(
        InterviewAttempt $attempt,
        ?string $reason = null
    ): InterviewAttempt {
        return DB::transaction(function () use ($attempt, $reason) {
            $this->ensureNotFinished($attempt);

            $attempt->update([
                'status' => InterviewAttemptStatus::CANCELLED,
                'ended_at' => now(),
                'failure_reason' => $reason,
            ]);

            return $attempt->fresh();
        });
    }

    private function ensureStatus(
        InterviewAttempt $attempt,
        InterviewAttemptStatus $expected
    ): void {
        if ($attempt->status !== $expected) {
            throw new LogicException(
                sprintf(
                    'Invalid interview attempt transition. Expected "%s", got "%s".',
                    $expected->value,
                    $attempt->status->value
                )
            );
        }
    }

    private function ensureNotFinished(
        InterviewAttempt $attempt
    ): void {
        $finishedStatuses = [
            InterviewAttemptStatus::COMPLETED,
            InterviewAttemptStatus::NO_ANSWER,
            InterviewAttemptStatus::FAILED,
            InterviewAttemptStatus::CANCELLED,
        ];

        if (in_array($attempt->status, $finishedStatuses, true)) {
            throw new LogicException(
                sprintf(
                    'Interview attempt is already finished with status "%s".',
                    $attempt->status->value
                )
            );
        }
    }
}
