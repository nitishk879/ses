<?php

namespace App\Services;

use App\Contracts\InterviewEvaluationProvider;
use App\Enums\InterviewEvaluationStatusEnum;
use App\Models\InterviewAttempt;
use App\Models\InterviewEvaluation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class InterviewEvaluationService
{
    public function __construct(
        protected InterviewEvaluationProvider $provider,
        protected InterviewEvaluationDigestService $digests,
    ) {
    }

    public function evaluate(
        InterviewAttempt $attempt
    ): InterviewEvaluation {
        $attempt->load([
            'interview',
            'questions.answer',
        ]);

        if ($attempt->status->value !== 'completed') {
            throw new RuntimeException(
                'Only completed interview attempts can be evaluated.'
            );
        }

        $evaluation = $attempt->evaluation;

        if (!$evaluation) {
            $evaluation = InterviewEvaluation::create([
                'interview_attempt_id' => $attempt->id,
                'status' => InterviewEvaluationStatusEnum::PENDING,
            ]);
        }

        $this->markEvaluating($evaluation);

        try {
            $result = $this->provider->evaluate($attempt);

            $this->validateProviderResult($result);

            $overallScore = $this->calculateOverallScore(
                $result['technical_fit'],
                $result['jd_fit'],
                $result['communication']
            );

            $stored = DB::transaction(function () use (
                $evaluation,
                $result,
                $overallScore
            ) {
                $evaluation->update([
                    'status' => InterviewEvaluationStatusEnum::COMPLETED,

                    'technical_fit' => $result['technical_fit'],
                    'jd_fit' => $result['jd_fit'],
                    'communication' => $result['communication'],
                    'overall_score' => $overallScore,

                    'summary' => $result['summary'] ?? null,
                    'strengths' => $result['strengths'] ?? [],
                    'gaps' => $result['gaps'] ?? [],
                    'evidence' => $result['evidence'] ?? [],

                    'recommendation' =>
                        $result['recommendation'] ?? null,

                    'provider' => $result['provider'] ?? null,
                    'model' => $result['model'] ?? null,
                    'prompt_version' =>
                        $result['prompt_version'] ?? null,

                    // Carries how much of the interview was actually answered.
                    'metadata' => $result['metadata'] ?? null,

                    'evaluated_at' => now(),
                    'failure_reason' => null,
                ]);

                return $evaluation->fresh();
            });

            // Join the digest that emails the recruiter about this batch.
            try {
                $this->digests->record($stored);
            } catch (\Throwable $e) {
                Log::error('interview.digest.record_failed', [
                    'evaluation_id' => $stored->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $stored;
        } catch (\Throwable $exception) {
            $evaluation->update([
                'status' => InterviewEvaluationStatusEnum::FAILED,
                'failure_reason' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function markEvaluating(
        InterviewEvaluation $evaluation
    ): void {
        $evaluation->update([
            'status' => InterviewEvaluationStatusEnum::EVALUATING,
            'failure_reason' => null,
        ]);
    }

    protected function calculateOverallScore(
        float $technicalFit,
        float $jdFit,
        float $communication
    ): float {
        return round(
            ($technicalFit * 0.40) +
            ($jdFit * 0.35) +
            ($communication * 0.25),
            2
        );
    }

    protected function validateProviderResult(array $result): void
    {
        $required = [
            'technical_fit',
            'jd_fit',
            'communication',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $result)) {
                throw new RuntimeException(
                    "AI evaluation result is missing: {$field}"
                );
            }

            if (!is_numeric($result[$field])) {
                throw new RuntimeException(
                    "AI evaluation field {$field} must be numeric."
                );
            }

            if ($result[$field] < 0 || $result[$field] > 100) {
                throw new RuntimeException(
                    "AI evaluation field {$field} must be between 0 and 100."
                );
            }
        }
    }
}
