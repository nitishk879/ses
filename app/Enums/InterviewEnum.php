<?php

namespace App\Enums;

use function PHPUnit\Framework\matches;

enum InterviewEnum :int
{
    case once = 1;
    case one_two = 2;
    case twice = 3;
    case thrice = 4;

    /**
     * @param $value
     * @return string
     */
    public static function toName($value): string
    {
        return match ($value){
            self::once => 'Once',
            self::one_two => 'One Two',
            self::twice => 'Twice',
            self::thrice => 'Thrice'
        };
    }

}
