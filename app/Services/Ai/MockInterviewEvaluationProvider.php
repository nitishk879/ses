<?php

namespace App\Services\Ai;

use App\Contracts\InterviewEvaluationProvider;
use App\Models\InterviewAttempt;

class MockInterviewEvaluationProvider implements InterviewEvaluationProvider
{
    public function evaluate(InterviewAttempt $attempt): array
    {
        return [
            'technical_fit' => 85.00,
            'jd_fit' => 90.00,
            'communication' => 82.00,

            'summary' => 'The candidate demonstrated strong technical knowledge and good alignment with the role.',

            'strengths' => [
                'Strong technical fundamentals',
                'Relevant professional experience',
                'Clear communication',
            ],

            'gaps' => [
                'Limited experience with large-scale distributed systems',
            ],

            'evidence' => [
                [
                    'criterion' => 'technical_fit',
                    'question_id' => null,
                    'observation' => 'Candidate demonstrated good understanding of the required technical concepts.',
                ],
                [
                    'criterion' => 'jd_fit',
                    'question_id' => null,
                    'observation' => 'Candidate experience is closely aligned with the job requirements.',
                ],
                [
                    'criterion' => 'communication',
                    'question_id' => null,
                    'observation' => 'Candidate provided structured and understandable answers.',
                ],
            ],

            'recommendation' => 'recommended',

            'provider' => 'mock',
            'model' => 'mock-interview-evaluator',
            'prompt_version' => 'v1',
        ];
    }
}
