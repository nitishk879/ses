<?php

namespace App\Services;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewSlotStatus;
use App\Enums\InterviewStatus;
use App\Exceptions\Interview\SlotUnavailable;
use App\Models\Interview;
use App\Models\InterviewSlot;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
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
    public function __construct(
        private readonly InterviewAvailabilityService $availability,
    ) {
    }

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

            if ($this->instantIsTaken($locked->starts_at, $interview->id)) {
                throw new SlotUnavailable(__('interview.slot_just_taken'));
            }

            $this->claim($locked);

            // The siblings are released rather than deleted: what was offered
            // is part of the record of how this interview came to be booked.
            $interview->slots()
                ->whereKeyNot($locked->id)
                ->where('status', InterviewSlotStatus::OFFERED)
                ->update(['status' => InterviewSlotStatus::RELEASED]);

            $this->bookInterviewAt($interview, $locked);

            Log::info('interview.slot_confirmed', [
                'interview_id' => $interview->id,
                'slot_id' => $locked->id,
                'starts_at' => $locked->starts_at->toIso8601String(),
                'mode' => 'fixed',
            ]);

            return $locked->fresh();
        });
    }

    /**
     * Confirm an arbitrary time the candidate picked off the calendar.
     *
     * The counterpart to {@see confirm()} for invitations that offered a
     * window rather than a list. The difference is only in where the row comes
     * from — there it is selected, here it is created — and everything after
     * that point is identical, which is why both end in the same two helpers.
     *
     * @param  string  $localTime  naive `Y-m-d H:i` as the page rendered it
     *
     * @throws SlotUnavailable when the time is not one the calendar offers
     */
    public function confirmAt(Interview $interview, string $localTime): InterviewSlot
    {
        if (! $this->invitationIsOpen($interview)) {
            throw new SlotUnavailable(__('interview.invitation_expired'));
        }

        if (! $interview->offersCalendar()) {
            throw new SlotUnavailable(__('interview.slot_not_for_this_interview'));
        }

        $start = $this->availability->parse($interview, $localTime);

        /*
         * Checked before the transaction opens, and again inside it.
         *
         * Not redundant: this pass exists to produce a *specific* message —
         * the time has passed, it is outside the window, it is off the grid —
         * and doing that work inside a transaction holds a lock open while
         * formatting a sentence. The pass inside is the one that must be
         * right, and it is backed by a constraint rather than a query.
         */
        $this->availability->assertBookable($interview, $start);

        $startUtc = $start->utc();
        $endUtc = $this->availability->endOf($start)->utc();

        return DB::transaction(function () use ($interview, $start, $startUtc, $endUtc) {
            // Orders this candidate against *themselves* — a double-submitted
            // form, a retried request — before anything is written.
            $locked = Interview::query()->whereKey($interview->id)->lockForUpdate()->first();

            if (! $locked || ! $this->invitationIsOpen($locked)) {
                throw new SlotUnavailable(__('interview.invitation_expired'));
            }

            if ($this->instantIsTaken($startUtc, $interview->id)) {
                throw new SlotUnavailable(__('interview.slot_just_taken'));
            }

            $slot = $this->claim(new InterviewSlot([
                'interview_id' => $interview->id,
                'starts_at' => $startUtc,
                'ends_at' => $endUtc,
                // The calendar writes no offered rows, so this is the first
                // and only position — unless a reschedule left some behind.
                'position' => min(255, ($interview->slots()->max('position') ?? 0) + 1),
            ]));

            // Nothing was offered, so there are no siblings to release — but a
            // reschedule can have left some, and they are not on the table any
            // more now that a time is booked.
            $interview->slots()
                ->whereKeyNot($slot->id)
                ->where('status', InterviewSlotStatus::OFFERED)
                ->update(['status' => InterviewSlotStatus::RELEASED]);

            $this->bookInterviewAt($interview, $slot);

            Log::info('interview.slot_confirmed', [
                'interview_id' => $interview->id,
                'slot_id' => $slot->id,
                'starts_at' => $startUtc->toIso8601String(),
                'mode' => 'calendar',
                'local' => $start->toIso8601String(),
            ]);

            return $slot->fresh();
        });
    }

    /**
     * Take the instant, or lose the race.
     *
     * `reserved_instant` carries a unique index, and writing `starts_at` into
     * it is what actually reserves the call line. This is the only point in
     * the feature where concurrency is *decided* rather than narrowed: every
     * check before it reads a snapshot and can be stale by the time it is
     * acted on, which is how two candidates used to end up booked for the same
     * 10:00 with one telephone line between them.
     *
     * A duplicate key here is an ordinary outcome, not a fault — somebody else
     * confirmed first, half a second ago — so it becomes the same
     * {@see SlotUnavailable} the candidate would have seen a moment earlier,
     * and the page offers them what is left.
     *
     * @throws SlotUnavailable when another booking already holds this instant
     */
    private function claim(InterviewSlot $slot): InterviewSlot
    {
        $slot->fill([
            'status' => InterviewSlotStatus::SELECTED,
            'selected_at' => now(),
            'reserved_instant' => $slot->starts_at,
        ]);

        try {
            $slot->save();
        } catch (UniqueConstraintViolationException) {
            throw new SlotUnavailable(__('interview.slot_just_taken'));
        }

        return $slot;
    }

    /**
     * Everything that follows from a time being taken.
     *
     * Shared by both confirmation paths so the two cannot drift: an interview
     * booked off the calendar and one booked off a pinned list must reach the
     * dialler in exactly the same state.
     */
    private function bookInterviewAt(Interview $interview, InterviewSlot $slot): void
    {
        $interview->update([
            'status' => InterviewStatus::SCHEDULED,
            'scheduled_at' => $slot->starts_at,
            'slot_selected_at' => now(),
            // Single-use: the link stops working the moment it is used, so a
            // forwarded email cannot rebook or reveal the choice.
            'invitation_token' => null,
            'failure_reason' => null,
        ]);

        /*
         * The attempt the scheduler will pick up. Created here so the booking
         * and the work it implies commit together — an interview that is
         * SCHEDULED with no pending attempt would be claimed by
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
    }

    /**
     * Whether another interview already owns this exact instant.
     *
     * A fast path, and nothing more. It catches the overwhelmingly common
     * case — the window was taken minutes ago and is simply gone — so the
     * candidate gets a clear page instead of a constraint violation. It cannot
     * catch two confirmations landing in the same instant, because it reads a
     * snapshot; {@see claim()} is what handles those.
     *
     * The second query is not redundant with the unique index: an interview
     * can be given a `scheduled_at` with no slot row at all, by a recruiter
     * setting a time by hand or by the orchestrator moving a retry. Those
     * occupy the call line too, and no index spans both tables.
     */
    private function instantIsTaken(\Carbon\CarbonInterface $startsAt, int $exceptInterviewId): bool
    {
        $instant = CarbonImmutable::parse($startsAt)->utc();

        $takenBySlot = InterviewSlot::query()
            ->selected()
            ->where('starts_at', $instant)
            ->where('interview_id', '!=', $exceptInterviewId)
            ->exists();

        if ($takenBySlot) {
            return true;
        }

        return Interview::query()
            ->where('scheduled_at', $instant)
            ->where('id', '!=', $exceptInterviewId)
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
