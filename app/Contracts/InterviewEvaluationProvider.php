<?php

namespace App\Contracts;

use App\Models\InterviewAttempt;

interface InterviewEvaluationProvider
{
    /**
     * Evaluate a completed interview attempt.
     *
     * The provider must return a normalized array matching
     * the application's interview evaluation schema.
     */
    public function evaluate(InterviewAttempt $attempt): array;
}
