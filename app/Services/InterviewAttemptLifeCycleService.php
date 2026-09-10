<?php

namespace App\Services;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewStatus;
use App\Models\InterviewAttempt;
use Illuminate\Support\Facades\DB;
use LogicException;

class InterviewAttemptLifeCycleService
{
    private const MAX_DURATION_SECONDS = 300;
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

    public function complete(
        InterviewAttempt $attempt
    ): InterviewAttempt {
        return DB::transaction(function () use ($attempt) {
            $this->ensureStatus(
                $attempt,
                InterviewAttemptStatus::IN_PROGRESS
            );

            $endedAt = now();

            $duration = $attempt->started_at
                ? $attempt->started_at->diffInSeconds($endedAt)
                : null;

            // Set maximum minutes for interview 5 minutes
            $duration = min(
                $duration ?? self::MAX_DURATION_SECONDS,
                self::MAX_DURATION_SECONDS
            );

            $attempt->update([
                'status' => InterviewAttemptStatus::COMPLETED,
                'ended_at' => $endedAt,
                'duration_seconds' => $duration,
            ]);

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
