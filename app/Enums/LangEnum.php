<?php

namespace App\Enums;

enum LangEnum: int
{
    case en = 1;
    case jp = 2;
    case bl = 3;

    /**
     * @param int $value
     * @return string
     */
    public static function toName(int $value): string
    {
        return match ($value){
            self::en->value => __("common/sidebar.en") ?? 'English',
            self::jp->value => __("common/sidebar.jp") ?? 'Japanese',
            self::bl->value => __("common/sidebar.bl") ?? 'Bilingual',
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
            self::en->value => [1],
            self::jp->value => [2],
            self::bl->value => [1, 2],
            default => [],
        };
    }
}
