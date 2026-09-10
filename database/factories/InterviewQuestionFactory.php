<?php

namespace Database\Factories;

use App\Enums\InterviewQuestionTypeEnum;
use App\Models\InterviewAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InterviewQuestion>
 */
class InterviewQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interview_attempt_id' => InterviewAttempt::factory(),
            'sequence' => fake()->numberBetween(1, 10),
            'type' => fake()->randomElement(
                InterviewQuestionTypeEnum::cases()
            ),
            'skill_area' => fake()->randomElement([
                'technical',
                'communication',
                'problem_solving',
                'experience',
            ]),
            'question_text' => fake()->sentence() . '?',
            'source' => 'ai',
            'asked_at' => null,
            'metadata' => null,
        ];
    }

    public function core(): static
    {
        return $this->state(fn () => [
            'type' => InterviewQuestionTypeEnum::CORE,
            'source' => 'system',
        ]);
    }

    public function jdSpecific(): static
    {
        return $this->state(fn () => [
            'type' => InterviewQuestionTypeEnum::JD_SPECIFIC,
            'source' => 'ai',
        ]);
    }

    public function candidateSpecific(): static
    {
        return $this->state(fn () => [
            'type' => InterviewQuestionTypeEnum::CANDIDATE_SPECIFIC,
            'source' => 'ai',
        ]);
    }

    public function followUp(): static
    {
        return $this->state(fn () => [
            'type' => InterviewQuestionTypeEnum::FOLLOW_UP,
            'source' => 'ai',
        ]);
    }

    public function asked(): static
    {
        return $this->state(fn () => [
            'asked_at' => now(),
        ]);
    }
}
