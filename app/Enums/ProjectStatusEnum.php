<?php

namespace App\Enums;

enum ProjectStatusEnum: int
{
    case open = 5;
    case in_progress = 6;
    case on_hold = 7;
    case completed = 8;

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


    /**
     * Now, let's set specific color for each progress
     *
     * @param int $value
     * @return string
     */
    public static function color(int $value): string
    {
        return match($value)
        {
            self::open->value => 'primary',
            self::in_progress->value => 'info',
            self::completed->value => 'success',
            self::on_hold->value => 'danger',
            default => 'secondary',
        };
    }


    /**
     * Now, let's set specific color for each progress
     *
     * @param int $value
     * @return string
     */
    public static function percentage(int $value): string
    {
        return match($value)
        {
            self::open->value => 30,
            self::in_progress->value => 40,
            self::on_hold->value => 50,
            self::completed->value => 100,
            default => 0,
        };
    }
}
