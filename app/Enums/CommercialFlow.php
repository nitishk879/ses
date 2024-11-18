<?php

namespace App\Enums;

enum CommercialFlow: int
{
    case Commercial = 1;
    case Intermediary = 2;

    /**
     * Let's set name|title for the Enum
     *
     * @param int $value
     * @return string
     */
    public static function toName(int $value): string
    {
        return match ($value) {
            self::Commercial->value => __("projects/form.Commercial") ?? __("Enter into the business flow"),
            self::Intermediary->value => __("projects/form.Intermediary") ?? __("Intermediary only")
        };
    }
}
