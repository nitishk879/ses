<?php

namespace Database\Factories;

use App\Models\InterviewQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InterviewAnswer>
 */
class InterviewAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interview_question_id' => InterviewQuestion::factory(),
            'answer_text' => fake()->paragraph(),
            'transcript' => fake()->paragraph(),
            'audio_path' => null,
            'audio_duration_seconds' => null,
            'started_at' => null,
            'ended_at' => null,
            'metadata' => null,
        ];
    }
    public function text(): static
    {
        return $this->state(fn () => [
            'answer_text' => fake()->paragraph(),
            'transcript' => null,
            'audio_path' => null,
        ]);
    }

    public function voice(): static
    {
        return $this->state(fn () => [
            'answer_text' => null,
            'transcript' => fake()->paragraph(),
            'audio_path' => 'interviews/sample/answer.mp3',
            'audio_duration_seconds' => fake()->numberBetween(10, 120),
        ]);
    }

    public function completed(): static
    {
        $startedAt = now()->subSeconds(45);

        return $this->state(fn () => [
            'started_at' => $startedAt,
            'ended_at' => $startedAt->copy()->addSeconds(45),
        ]);
    }
}
