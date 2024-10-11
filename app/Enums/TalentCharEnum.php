<?php

namespace App\Enums;

enum TalentCharEnum: int
{
    /**
     * Let's define characters for the talent
     */
    case user_support = 1; //= "Experience in user support",
    case price_negotiable = 2; //= "Price negotiable",
    case full_time = 3; //= "Full-time staff available",
    case over_100 = 4; //= "Experience in projects with over 100 people",
    case english_exp = 5; //= "English business use experience",
    case consulting_firm = 6; //= "From a consulting firm",
    case full_remote = 7; //= "Full remote work preferred",
    case long_term = 8; //= "Long-term hope",
    case leadership = 9; //= "Leadership experience",
    case local_project = 10; //= "Local projects available",
    case permanent_staff = 11; //= "Some permanent staff available",
    case shift_work = 12; //= "Shift work request",

    public static function toName($value): string
    {
        return match ($value) {
            self::user_support => "Experience in user support",
            self::price_negotiable => "Price negotiable",
            self::full_time => "Full-time staff available",
            self::over_100 => "Experience in projects with over 100 people",
            self::english_exp => "English business use experience",
            self::consulting_firm => "From a consulting firm",
            self::full_remote => "Full remote work preferred",
            self::long_term => "Long-term hope",
            self::leadership => "Leadership experience",
            self::local_project => "Local projects available",
            self::permanent_staff => "Some permanent staff available",
            self::shift_work => "Shift work request"
        };
    }
}
