<?php

namespace Database\Factories;

use App\Enums\InterviewStatus;
use App\Models\Project;
use App\Models\Talent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interview>
 */
class InterviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'talent_id' => Talent::factory(),

            'status' => InterviewStatus::PENDING,

            'channel' => fake()->randomElement([
                'google_meet',
                'phone',
            ]),

            'timezone' => fake()->randomElement([
                'Asia/Tokyo',
                'Asia/Kolkata',
            ]),

            'scheduled_at' => null,
            'started_at' => null,
            'ended_at' => null,

            'duration_seconds' => null,

            'failure_reason' => null,

            'provider_reference' => null,

            'metadata' => null,
        ];
    }
    public function invited(): static
    {
        return $this->state(fn () => [
            'status' => InterviewStatus::INVITED,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => InterviewStatus::SCHEDULED,
            'scheduled_at' => now()->addDays(2),
        ]);
    }

    public function completed(): static
    {
        $startedAt = now()->subMinutes(5);

        return $this->state(fn () => [
            'status' => InterviewStatus::COMPLETED,
            'started_at' => $startedAt,
            'ended_at' => $startedAt->copy()->addMinutes(5),
            'duration_seconds' => 300,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => InterviewStatus::FAILED,
            'failure_reason' => fake()->sentence(),
        ]);
    }
}
