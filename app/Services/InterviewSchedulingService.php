<?php

namespace App\Services;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewSlotStatus;
use App\Enums\InterviewStatus;
use App\Exceptions\Interview\SlotUnavailable;
use App\Models\Interview;
use App\Models\InterviewSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Task 7: turn the candidate's choice into a booking.
 *
 * This is the only place in the feature with a genuine race in it. Several
 * candidates can be holding an email that offers the same 10:00 window, and
 * they may click within the same second. Exactly one must win, the others must
 * be told clearly, and none of them may end up with a booking the dialer will
 * later refuse — because with one concurrent call line, a double booking is a
 * candidate sitting by a phone that never rings.
 *
 * The guard is a row lock plus a re-check inside the transaction. Checking
 * availability before opening the transaction and trusting it afterwards is
 * the classic version of this bug.
 */
class InterviewSchedulingService
{
    /**
     * Find an interview by its invitation token, or null.
     *
     * Deliberately does not filter on expiry: an expired invitation should
     * render a page that says so, not a 404 that leaves the candidate
     * wondering whether they mistyped something.
     */
    public function findByToken(string $token): ?Interview
    {
        if (strlen($token) !== 64 || ! ctype_xdigit($token)) {
            // Cheap shape check before touching the database. Nothing else can
            // be a valid token, so nothing else deserves a query.
            return null;
        }

        return Interview::query()
            ->with(['slots' => fn ($q) => $q->orderBy('position'), 'project', 'talent.user'])
            ->where('invitation_token', $token)
            ->first();
    }

    public function invitationIsOpen(Interview $interview): bool
    {
        return filled($interview->invitation_token)
            && $interview->invitation_expires_at?->isFuture()
            && in_array($interview->status, [
                InterviewStatus::INVITED,
                InterviewStatus::SLOT_SELECTION,
            ], true);
    }

    /**
     * Confirm one slot.
     *
     * Returns the booked slot. Throws {@see SlotUnavailable} when the window
     * has gone — which is an ordinary outcome here, not a fault, and the
     * controller turns it into a "that time was just taken" page rather than
     * an error.
     */
    public function confirm(Interview $interview, InterviewSlot $slot): InterviewSlot
    {
        if ($slot->interview_id !== $interview->id) {
            throw new SlotUnavailable(__('interview.slot_not_for_this_interview'));
        }

        if (! $this->invitationIsOpen($interview)) {
            throw new SlotUnavailable(__('interview.invitation_expired'));
        }

        return DB::transaction(function () use ($interview, $slot) {
            /*
             * Lock this interview's slots first, then re-read the one being
             * confirmed. The lock orders concurrent confirmations against the
             * same interview; the global check below orders them against
             * confirmations of the same instant by *other* interviews.
             */
            $locked = InterviewSlot::query()
                ->whereKey($slot->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $locked->isChoosable()) {
                throw new SlotUnavailable(__('interview.slot_no_longer_available'));
            }

            if ($this->instantIsTaken($locked)) {
                throw new SlotUnavailable(__('interview.slot_just_taken'));
            }

            $locked->update([
                'status' => InterviewSlotStatus::SELECTED,
                'selected_at' => now(),
            ]);

            // The siblings are released rather than deleted: what was offered
            // is part of the record of how this interview came to be booked.
            $interview->slots()
                ->whereKeyNot($locked->id)
                ->where('status', InterviewSlotStatus::OFFERED)
                ->update(['status' => InterviewSlotStatus::RELEASED]);

            $interview->update([
                'status' => InterviewStatus::SCHEDULED,
                'scheduled_at' => $locked->starts_at,
                'slot_selected_at' => now(),
                // Single-use: the link stops working the moment it is used, so
                // a forwarded email cannot rebook or reveal the choice.
                'invitation_token' => null,
                'failure_reason' => null,
            ]);

            /*
             * The attempt the scheduler will pick up. Created here so the
             * booking and the work it implies commit together — an interview
             * that is SCHEDULED with no pending attempt would be claimed by
             * `interviews:dispatch-due` and then have to invent one.
             */
            if (! $interview->attempts()->where('status', InterviewAttemptStatus::PENDING)->exists()) {
                $next = ($interview->attempts()->max('attempt_number') ?? 0) + 1;

                $interview->attempts()->create([
                    'attempt_number' => $next,
                    'status' => InterviewAttemptStatus::PENDING,
                    'channel' => $interview->channel ?: 'phone',
                ]);
            }

            Log::info('interview.slot_confirmed', [
                'interview_id' => $interview->id,
                'slot_id' => $locked->id,
                'starts_at' => $locked->starts_at->toIso8601String(),
            ]);

            return $locked->fresh();
        });
    }


    /**
     * Whether another interview already owns this exact instant.
     *
     * The single-call-line constraint again: `INTERVIEW_MAX_CONCURRENT_CALLS`
     * is 1, so two bookings at the same moment means the second dial is
     * refused. Cheaper to refuse the booking than to refuse the call.
     */
    private function instantIsTaken(InterviewSlot $slot): bool
    {
        $takenBySlot = InterviewSlot::query()
            ->selected()
            ->where('starts_at', $slot->starts_at)
            ->where('interview_id', '!=', $slot->interview_id)
            ->exists();

        if ($takenBySlot) {
            return true;
        }

        return Interview::query()
            ->where('scheduled_at', $slot->starts_at)
            ->where('id', '!=', $slot->interview_id)
            ->whereIn('status', [
                InterviewStatus::SCHEDULED,
                InterviewStatus::STARTING,
                InterviewStatus::IN_PROGRESS,
            ])
            ->exists();
    }

    /**
     * Close an offer nobody answered.
     *
     * Kept separate from the housekeeping command so it can also be called
     * when a recruiter cancels, and so the state transition has one owner.
     */
    public function expire(Interview $interview): void
    {
        DB::transaction(function () use ($interview) {
            $interview->slots()
                ->where('status', InterviewSlotStatus::OFFERED)
                ->update(['status' => InterviewSlotStatus::EXPIRED]);

            $interview->update([
                'status' => InterviewStatus::RESCHEDULE_REQUIRED,
                'invitation_token' => null,
                'failure_reason' => __('interview.invitation_not_answered'),
            ]);
        });

        Log::info('interview.invitation_expired', ['interview_id' => $interview->id]);
    }
}
