<?php

namespace App\Enums;

enum InterviewQuestionTypeEnum:string
{
    case CORE = 'core';
    case JD_SPECIFIC = 'jd_specific';
    case CANDIDATE_SPECIFIC = 'candidate_specific';
    case FOLLOW_UP = 'follow_up';

    public static function toName(self $value): string
    {
        return match ($value) {
            self::CORE => __('interview.question_type.core'),
            self::JD_SPECIFIC => __('interview.question_type.jd_specific'),
            self::CANDIDATE_SPECIFIC => __('interview.question_type.candidate_specific'),
            self::FOLLOW_UP => __('interview.question_type.follow_up'),
        };
    }
}
