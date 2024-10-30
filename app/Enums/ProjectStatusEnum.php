<?php

namespace App\Enums;

enum ProjectStatusEnum: int
{
    case open = 5;
    case in_progress = 6;
    case completed = 7;
    case on_hold = 8;

    public static function toName(int $value): string
    {
        return match ($value) {
            self::open->value => __('projects/show.open'),
            self::in_progress->value => __('projects/show.in_progress'),
            self::completed->value => __('projects/show.completed'),
            self::on_hold->value => __('projects/show.on_hold'),
            default => __('projects/show.unknown')
        };
    }
}
