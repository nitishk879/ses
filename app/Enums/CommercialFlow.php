<?php

namespace App\Enums;

enum CommercialFlow: int
{
    case Commercial = 1;
    case Intermediary = 2;

    /**
     * Let's set name|title for the Enum
     *
     * @param $value
     * @return string
     */
    public static function toName($value): string
    {
        return match ($value) {
            self::Commercial => __("Enter into the business flow"),
            self::Intermediary => __("Intermediary only")
        };
    }
}
