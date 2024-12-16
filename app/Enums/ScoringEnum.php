<?php

namespace App\Enums;

enum ScoringEnum: int
{
    case hundred = 100;
    case ninety = 90;
    case eighty = 80;
    case seventy = 70;
    case sixty = 60;
    case key_feature = 50;


    static function toName(int $value): string
    {
        return match ($value) {
            self::hundred->value => __("projects/invitation.x_percent", ['percentage' => 100]),
            self::ninety->value => __("projects/invitation.x_percent", ['percentage' => 90]),
            self::eighty->value => __("projects/invitation.x_percent", ["percentage" => 80]),
            self::seventy->value => __("projects/invitation.x_percent", ["percentage" => 70]),
            self::sixty->value => __("projects/invitation.x_percent", ["percentage" => 60]),
            self::key_feature->value => __("Key Features only"),
        };
    }

    /**
     * This will make array set for matching values accordingly
     *
     * @param int $value
     * @return array
     */
    public static function toArray(int $value): array
    {
        return match ($value){
            self::hundred->value => [100],
            self::ninety->value => [90],
            self::eighty->value => [80],
            self::seventy->value => [70],
            self::sixty->value => [60],
            self::key_feature->value => [50],
            default => [],
        };
    }

}
