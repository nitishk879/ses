<?php

namespace App\Enums;

enum WorkLocationEnum: int
{
    case abroad = 1;
    case consignment = 2;
    case remote = 3;

    /**
     * @param int $value
     * @return string
     */
    public static function toName(int $value): string
    {
        return match ($value){
            self::abroad->value => __("common/sidebar.abroad") ?? 'Abroad',
            self::consignment->value => __("common/sidebar.consignment") ?? 'Request for consignment',
            self::remote->value => __("common/sidebar.remote") ?? 'Remote work preferred',
        };
    }
}
