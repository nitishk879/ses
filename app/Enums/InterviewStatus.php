<?php

namespace App\Enums;

enum InterviewStatus: string
{
    case PENDING = "pending";
    case INVITED = "invited";
    case SLOT_SELECTION = "slot_selection";
    case SCHEDULED = "scheduled";
    case STARTING = "starting";
    case IN_PROGRESS = "in_progress";
    case COMPLETED = "completed";
    case EVALUATING = "evaluating";
    case EVALUATED = "evaluated";

    // failure status
    case NO_ANSWER = "no_answer";
    case MISSED = "missed";
    case FAILED = "failed";
    case CANCELLED = "cancelled";
    case RESCHEDULE_REQUIRED = "rescheduled_required";

    /**
     * Bootstrap colour for this status on the recruiter dashboard.
     *
     * Grouped by what the reader should do about it, not by where the status
     * sits in the lifecycle: anything needing a human is warning or danger,
     * anything in flight is informational, and anything finished is quiet.
     * A row that is merely waiting must not look like a row that is stuck.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::COMPLETED, self::EVALUATED => 'success',
            self::SCHEDULED, self::STARTING, self::IN_PROGRESS, self::EVALUATING => 'primary',
            self::INVITED, self::SLOT_SELECTION => 'info',
            self::NO_ANSWER, self::MISSED, self::RESCHEDULE_REQUIRED => 'warning',
            self::FAILED => 'danger',
            self::PENDING, self::CANCELLED => 'secondary',
        };
    }

    /**
     * @param InterviewStatus $value
     * @return string
     */
    public static function toName(self $value): string
    {
        return match ($value) {
            self::PENDING => __("common/interviews.pending") ?? 'Pending',
            self::INVITED => __("common/interviews.invited") ?? 'Invited',
            self::SLOT_SELECTION => __("common/interviews.slot_selection") ?? 'Slot Selection',
            self::SCHEDULED => __("common/interviews.scheduled") ?? 'Scheduled',
            self::STARTING => __("common/interviews.starting") ?? 'Starting',
            self::IN_PROGRESS => __("common/interviews.in_progress") ?? 'In Progress',
            self::COMPLETED => __("common/interviews.completed") ?? 'Completed',
            self::EVALUATING => __("common/interviews.evaluating") ?? 'Evaluating',
            self::EVALUATED => __("common/interviews.evaluated") ?? 'Evaluated',
            self::NO_ANSWER => __("common/interviews.no_answer") ?? 'No Answer',
            self::MISSED => __("common/interviews.missed") ?? 'Missed',
            self::FAILED => __("common/interviews.failed") ?? 'Failed',
            self::CANCELLED => __("common/interviews.cancelled") ?? 'Cancelled',
            self::RESCHEDULE_REQUIRED => __("common/interviews.rescheduled_required") ?? 'Rescheduled',
        };
    }

}
