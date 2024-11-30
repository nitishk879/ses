<?php

namespace App\Enums;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;

enum GenderEnum: string
{
    case MALE = 'male';
    case FEMALE  = "female";
    case OTHER = "other";

    /**
     * Gender title multilingual
     *
     * @param $value
     * @return array|Translator|Application|string|null
     */
    public static function toName($value): Application|array|string|Translator|null
    {
        return match ($value){
            self::MALE->value => __("talents/registration.male"),
            self::FEMALE->value => __("talents/registration.female"),
            self::OTHER->value => __("talents/registration.other"),
        };
    }
}
