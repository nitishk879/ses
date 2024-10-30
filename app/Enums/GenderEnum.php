<?php

namespace App\Enums;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;

enum GenderEnum: string
{
    case MALE = 'Male';
    case FEMALE  = "Female";
    case OTHER = "Other";

    /**
     * Gender title multilingual
     *
     * @param $value
     * @return array|Translator|Application|string|null
     */
    public static function toName($value): Application|array|string|Translator|null
    {
        return match ($value){
            self::MALE->value => __("talents/registration.MALE"),
            self::FEMALE->value => __("talents/registration.FEMALE"),
            self::OTHER->value => __("talents/registration.OTHER"),
        };
    }
}
