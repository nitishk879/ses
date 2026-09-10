<?php

namespace App\Enums;

enum InterviewEvaluationStatusEnum: string
{
    case PENDING = 'pending';
    case EVALUATING = 'evaluating';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('interview.status.pending') ?? 'Pending',
            self::EVALUATING => __('interview.status.evaluating') ?? 'Evaluating',
            self::COMPLETED => __('interview.status.completed') ?? 'Completed',
            self::FAILED => __('interview.status.failed') ?? 'Failed',
        };
    }
}
