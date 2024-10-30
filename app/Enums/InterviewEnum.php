<?php

namespace App\Enums;

enum InterviewEnum :int
{
    case once = 1;
    case one_two = 2;
    case twice = 3;
//    case thrice = 4;

    /**
     * @param $value
     * @return string
     */
    public static function toName($value): string
    {
        return match ($value){
            self::once => __("common/sidebar.interview_once") ?? 'Once',
            self::one_two => __("common/sidebar.interview_one_two") ?? '1-2',
            self::twice => __("common/sidebar.interview_twice") ?? '2 or More',
//            self::thrice => __("common/sidebar.interview_thrice") ?? 'Thrice'
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
            self::once->value => [1],
            self::one_two->value => [1,2],
            self::twice->value => [2, 3, 4, 5],
//            self::thrice->value => [3,4,5],
        };
    }
}
