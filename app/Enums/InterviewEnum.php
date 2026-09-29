<?php

namespace App\Enums;

enum InterviewEnum :int
{
    case once = 1;
    case one_two = 2;
    case twice = 3;
//    case thrice = 4;

    /**
     * A label for a stored interview count, including when there is none.
     *
     * `number_of_interviewers` is a nullable column and a project registered
     * without an answer legitimately holds null — which this `match` had no arm
     * for, so it raised UnhandledMatchError and every screen listing such a
     * project returned a 500. A `match` over an enum needs a default the moment
     * the column it reads can be empty.
     *
     * @param  self|int|string|null  $value
     */
    public static function toName($value): string
    {
        $case = $value instanceof self ? $value : self::tryFrom((int) $value);

        return match ($case) {
            self::once => __("common/sidebar.interview_once"),
            self::one_two => __("common/sidebar.interview_one_two"),
            self::twice => __("common/sidebar.interview_twice"),
            default => __('projects/form.interview_none'),
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
        return match ($value) {
            self::once->value => [1],
            self::one_two->value => [1, 2],
            self::twice->value => [2, 3, 4, 5],
            // An unknown count filters nothing rather than throwing; the caller
            // is building a search filter, not validating input.
            default => [],
        };
    }
}
