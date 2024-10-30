<?php

namespace App\Enums;

enum TalentStatusEnum: int
{
// Before talent picked up
    case invited = 1;
    case hired = 2;
    case rejected = 3;
    case in_review = 4;

    public static function toName(int $value): string
    {
        return match ($value) {
            self::invited->value => __('projects/show.invited'),
            self::hired->value => __('projects/show.hired'),
            self::rejected->value => __('projects/show.rejected'),
            self::in_review->value => __('projects/show.in_review'),
            default => __('projects/show.unknown')
        };
    }
}
