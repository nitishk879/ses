<?php

namespace App\Enums;

enum InterviewSlotStatus: string
{
    /** Presented to the candidate. Not reserved — someone else may still take it. */
    case OFFERED = 'offered';

    /** The candidate chose this one. Exactly one per interview. */
    case SELECTED = 'selected';

    /** The offer window closed, or the time passed, without a choice. */
    case EXPIRED = 'expired';

    /**
     * Withdrawn while still valid — the candidate picked a sibling slot, or a
     * recruiter cancelled the interview. Distinct from EXPIRED so "nobody
     * replied" and "they replied, just not this one" stay tellable apart.
     */
    case RELEASED = 'released';

    public static function toName(self $value): string
    {
        return match ($value) {
            self::OFFERED => __('interview.slot_status.offered'),
            self::SELECTED => __('interview.slot_status.selected'),
            self::EXPIRED => __('interview.slot_status.expired'),
            self::RELEASED => __('interview.slot_status.released'),
        };
    }
}
