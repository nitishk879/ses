<?php

namespace App\Enums;

/**
 * How an invitation puts times in front of a candidate.
 *
 * The two differ in what is written at invite time, which is why this is a
 * stored column and not a config lookup: an invitation issued under one rule
 * must keep behaving that way even after the setting changes under it.
 */
enum InterviewOfferMode: string
{
    /**
     * The candidate books anywhere inside an open window.
     *
     * No slot rows exist until one is confirmed — a fortnight of half-hours is
     * ~340 windows per candidate, and writing those as `offered` rows would
     * mean six figures of rows describing times nobody asked for.
     */
    case CALENDAR = 'calendar';

    /**
     * A short list the recruiter pinned by hand.
     *
     * The rows are written up front, because what was offered is part of the
     * record of how the interview came to be booked.
     */
    case FIXED = 'fixed';

    public function isCalendar(): bool
    {
        return $this === self::CALENDAR;
    }
}
