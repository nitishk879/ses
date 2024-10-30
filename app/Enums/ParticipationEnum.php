<?php

namespace App\Enums;

enum ParticipationEnum: string
{
    case IMMEDIATELY = 'immediately';
    case FUTURE = 'future';
    case FROM_DATE = 'from_date';

    public static function toName($value): string
    {
        // TODO: Implement cases() method.
        return match ($value) {
            self::IMMEDIATELY->value => __("common/sidebar.immediately") ?? "Ready to operate immediately",
            self::FUTURE->value =>  __("common/sidebar.future") ?? "Possible to participate in the future",
            self::FROM_DATE->value =>  __("common/sidebar.from_date") ?? "Please inquire about participation date"
        };
    }
}
