<?php

namespace Database\Factories;

use App\Enums\InterviewAttemptStatus;
use App\Models\Interview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InterviewAttempt>
 */
class InterviewAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interview_id' => Interview::factory(),
            'attempt_number' => 1,
            'status' => InterviewAttemptStatus::PENDING,
            'channel' => null,
            'started_at' => null,
            'ended_at' => null,
            'duration_seconds' => null,
            'failure_reason' => null,
            'provider_reference' => null,
            'metadata' => null,
        ];
    }

    public function started(): static
    {
        return $this->state(function () {
            return [
                'status' => InterviewAttemptStatus::IN_PROGRESS,
                'started_at' => now(),
            ];
        });
    }

    public function completed(): static
    {
        $startedAt = now()->subMinutes(5);

        return $this->state(function () use ($startedAt) {
            return [
                'status' => InterviewAttemptStatus::COMPLETED,
                'started_at' => $startedAt,
                'ended_at' => $startedAt->copy()->addMinutes(5),
                'duration_seconds' => 300,
            ];
        });
    }

    public function failed(): static
    {
        return $this->state(function () {
            return [
                'status' => InterviewAttemptStatus::FAILED,
                'failure_reason' => fake()->sentence(),
            ];
        });
    }
}
