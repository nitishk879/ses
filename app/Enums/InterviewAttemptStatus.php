<?php

namespace App\Enums;

enum InterviewAttemptStatus: string
{
    case PENDING = 'pending';
    case STARTING = 'starting';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    // Failure states
    case NO_ANSWER = 'no_answer';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public static function toName(self $value): string
    {
        return match ($value) {
            self::PENDING => __('common/interviews.pending'),
            self::STARTING => __('common/interviews.starting'),
            self::IN_PROGRESS => __('common/interviews.in_progress'),
            self::COMPLETED => __('common/interviews.completed'),
            self::NO_ANSWER => __('common/interviews.no_answer'),
            self::FAILED => __('common/interviews.failed'),
            self::CANCELLED => __('common/interviews.cancelled'),
        };
    }
}
