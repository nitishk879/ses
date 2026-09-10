<?php

namespace Database\Factories;

use App\Enums\InterviewEvaluationStatusEnum;
use App\Models\InterviewAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InterviewEvaluation>
 */
class InterviewEvaluationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $technicalFit = fake()->randomFloat(2, 50, 100);
        $jdFit = fake()->randomFloat(2, 50, 100);
        $communication = fake()->randomFloat(2, 50, 100);

        $overallScore = (
            ($technicalFit * 0.40) +
            ($jdFit * 0.35) +
            ($communication * 0.25)
        );

        return [
            'interview_attempt_id' => InterviewAttempt::factory(),
            'status' => InterviewEvaluationStatusEnum::COMPLETED,
            'technical_fit' => $technicalFit,
            'jd_fit' => $jdFit,
            'communication' => $communication,
            'overall_score' => round($overallScore, 2),
            'summary' => fake()->paragraph(),
            'strengths' => [
                'Strong technical fundamentals',
                'Relevant project experience',
                'Clear communication',
            ],

            'gaps' => [
                'Limited experience with large-scale systems',
                'Could provide more detailed technical explanations',
            ],

            'evidence' => [
                [
                    'criterion' => 'technical_fit',
                    'question_sequence' => 1,
                    'observation' => 'Demonstrated strong understanding of the required technology.',
                ],
                [
                    'criterion' => 'jd_fit',
                    'question_sequence' => 2,
                    'observation' => 'Previous experience closely matches the job requirements.',
                ],
                [
                    'criterion' => 'communication',
                    'question_sequence' => 3,
                    'observation' => 'Provided clear and structured answers.',
                ],
            ],

            'recommendation' => fake()->randomElement([
                'recommended',
                'maybe_recommended',
                'not_recommended',
            ]),

            'provider' => 'test',
            'model' => 'test-model',
            'prompt_version' => 'v1',
            'evaluated_at' => now(),
            'failure_reason' => null,
            'metadata' => [
                'test' => true,
            ],
        ];
    }

    public function pending(): static
    {
        return $this->state(fn() => [
            'status' => InterviewEvaluationStatusEnum::PENDING,

            'technical_fit' => null,
            'jd_fit' => null,
            'communication' => null,
            'overall_score' => null,

            'summary' => null,
            'strengths' => null,
            'gaps' => null,
            'evidence' => null,

            'recommendation' => null,
            'evaluated_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn() => [
            'status' => InterviewEvaluationStatusEnum::FAILED,

            'technical_fit' => null,
            'jd_fit' => null,
            'communication' => null,
            'overall_score' => null,

            'failure_reason' => 'Evaluation provider failed.',
            'evaluated_at' => null,
        ]);
    }
}
