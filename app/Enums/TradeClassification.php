<?php

namespace App\Enums;

enum TradeClassification: int
{
    //
    case End = 1;
    case Primary = 2;
    case Secondary = 3;
    case Third = 4;


    public static function toName($value): string
    {
        // TODO: Implement cases() method.
        return match ($value) {
            self::End => __("projects/form.End") ?? "End contract",
            self::Primary => __("projects/form.Primary") ?? "Prime contractor",
            self::Secondary => __("projects/form.Secondary") ?? "Secondary contract",
//            self::Third => __("projects/form.Third") ?? "Third and Subsequent order"
        };
    }

    /**
     * Now, let's set specific color for each progress
     *
     * @return string
     */
    public function color(): string
    {
        return match($this)
        {
            self::End => 'red',
            self::Primary => 'blue',
            self::Secondary => 'gray',
            self::Third => 'danger',
        };
    }
}
