<?php

namespace App\Enums;

enum WorkLocationEnum: int
{
    case abroad = 1;
    case consignment = 2;
    case remote = 3;

    /**
     * @param $value
     * @return string
     */
    public static function toName($value): string
    {
        return match ($value){
            self::abroad => __("common/sidebar.abroad") ?? 'Abroad',
            self::consignment => __("common/sidebar.consignment") ?? 'Request for consignment',
            self::remote => __("common/sidebar.remote") ?? 'Remote work preferred',
        };
    }

    /**
     * This will make array set for matching values accordingly
     *
     * @param $value
     * @return array
     */
    public static function toArray($value): array
    {
        return match ($value){
            self::abroad->value => [1],
            self::consignment->value => [2],
            self::remote->value => [1, 2]
        };
    }
}
