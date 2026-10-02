<?php

namespace App\Services;

use App\Enums\InterviewStatus;
use App\Exceptions\Interview\SlotUnavailable;
use App\Models\Interview;
use App\Models\InterviewSlot;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * The calendar a candidate books from, and the rules that decide what in it
 * can actually be booked.
 *
 * The invitation used to be a list of three times a recruiter typed. That
 * works — a candidate picks one or none — but "none" is the common answer, and
 * a declined offer costs a second email, a second wait, and often the
 * candidate. So the offer became a window instead: every half-hour between 8am
 * and 8pm for the next fortnight, and the candidate picks the one that suits
 * them.
 *
 * None of that relaxes the constraint underneath. `INTERVIEW_MAX_CONCURRENT_CALLS`
 * is 1: the shared TTS batches on a 30ms window, and two simultaneous calls
 * have been measured producing 5-14 seconds of dead air, which a candidate
 * hears as a dropped line. So the calendar is still a single shared resource,
 * and this class is where "shown as available" is kept honest about it.
 *
 * Two rules are worth stating, because getting either wrong is invisible until
 * somebody is standing by a phone:
 *
 * **Availability shown is a snapshot, not a reservation.** Two candidates can
 * be looking at the same free 10:00 right now. This class narrows the race; it
 * does not win it. The unique index on `interview_slots.reserved_instant` is
 * what decides, in {@see InterviewSchedulingService::confirmAt()}.
 *
 * **What is offered and what is accepted are the same function.** The grid the
 * page renders and the check that runs on POST are both {@see cellsFor()},
 * because a candidate can edit a form value and a page can sit open over
 * lunch. Anything the server will accept must be something it would have
 * drawn.
 */
class InterviewAvailabilityService
{
    /**
     * The bookable range for this invitation, in the candidate's zone.
     *
     * Read from the interview rather than recomputed from config. The window
     * was fixed when the email went out, and that email says a date — so a
     * setting changed afterwards must not move the deadline a candidate is
     * holding in their inbox.
     *
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function windowFor(Interview $interview): array
    {
        $timezone = $this->timezoneFor($interview);

        $start = $interview->offer_window_starts_at
            ?? $interview->invitation_sent_at
            ?? $interview->created_at
            ?? now();

        $end = $interview->offer_window_ends_at
            ?? $interview->invitation_expires_at
            ?? $start->copy()->addDays($this->config()['window_days']);

        return [
            'start' => CarbonImmutable::parse($start)->setTimezone($timezone),
            'end' => CarbonImmutable::parse($end)->setTimezone($timezone),
        ];
    }

    /**
     * The window a *new* invitation should open, from now.
     *
     * Separate from {@see windowFor()} because they answer opposite questions:
     * this one decides what to write, that one reads what was written.
     *
     * The end is the close of the last day rather than the same clock time a
     * fortnight later. "Two weeks" read by a person means fourteen whole days,
     * and an offer that quietly dies at 09:14 on the last one is an offer that
     * loses a candidate who opened the email that evening.
     *
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public function openWindow(string $timezone): array
    {
        $now = CarbonImmutable::now($timezone);
        $days = $this->config()['window_days'];

        return [
            'start' => $now,
            // -1: the day of the invitation is the first of the fourteen, not
            // the one before them.
            'end' => $now->addDays(max(1, $days) - 1)->endOfDay(),
        ];
    }

    /**
     * The calendar to draw: every day in the window, each with its half-hours.
     *
     * Days with nothing left in them are returned too, flagged rather than
     * dropped. A fortnight that silently renders as eleven days reads as a
     * bug, and the candidate cannot tell "we are not free then" from "the page
     * is broken".
     *
     * @return Collection<int, array{
     *     date: string, label: string, weekday: string, day: string, month: string,
     *     is_weekend: bool, available_count: int,
     *     cells: array<int, array{value: string, label: string, available: bool}>
     * }>
     */
    public function calendar(Interview $interview): Collection
    {
        $timezone = $this->timezoneFor($interview);
        $config = $this->config();
        $window = $this->windowFor($interview);

        // Days before today are gone whatever the window said when it opened.
        $earliest = $this->earliestBookable($timezone);

        $taken = $this->takenInstants(
            $window['start']->min($earliest),
            $window['end'],
            $interview->id,
        );

        $days = collect();
        $cursor = $window['start']->setTimezone($timezone)->startOfDay();
        $lastDay = $window['end']->setTimezone($timezone)->startOfDay();

        while ($cursor <= $lastDay) {
            $day = $cursor;
            $cursor = $cursor->addDay();

            if ($day->endOfDay() < $earliest) {
                // Wholly in the past — not "unavailable", simply over.
                continue;
            }

            if ($config['skip_weekends'] && $day->isWeekend()) {
                continue;
            }

            $cells = [];
            $availableCount = 0;

            foreach ($this->cellsFor($day, $config) as $start) {
                $available = $start >= $earliest
                    && $start <= $window['end']
                    && ! $taken->has($this->instantKey($start));

                $availableCount += $available ? 1 : 0;

                $cells[] = [
                    'value' => $start->format('Y-m-d H:i'),
                    'label' => $start->format('H:i'),
                    'available' => $available,
                ];
            }

            $days->push([
                'date' => $day->format('Y-m-d'),
                'label' => $day->translatedFormat('j M'),
                'weekday' => $day->translatedFormat('D'),
                'day' => $day->format('j'),
                'month' => $day->translatedFormat('M'),
                'is_weekend' => $day->isWeekend(),
                'available_count' => $availableCount,
                'cells' => $cells,
            ]);
        }

        return $days;
    }

    /**
     * Read a time the candidate posted.
     *
     * The form posts `Y-m-d H:i` with no offset, which on a UTC server reads
     * as a moment nine hours from the one the candidate tapped. It is parsed
     * in the interview's zone, which is the clock the page rendered.
     *
     * @throws SlotUnavailable when the value is not a time at all
     */
    public function parse(Interview $interview, string $value): CarbonImmutable
    {
        $value = trim($value);

        // Checked before parsing: Carbon reads "now", "tomorrow" and a good
        // deal else, and none of those are things this form can emit.
        if (! preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}$/', $value)) {
            throw new SlotUnavailable(__('interview.slot_time_unreadable', ['value' => $value]));
        }

        try {
            return CarbonImmutable::parse($value, $this->timezoneFor($interview));
        } catch (\Throwable) {
            throw new SlotUnavailable(__('interview.slot_time_unreadable', ['value' => $value]));
        }
    }

    /**
     * Refuse anything the calendar would not have drawn.
     *
     * Every branch carries its own message. "That time is not available" is
     * true of a slot in the past, a slot outside the window, a slot off the
     * grid and a slot somebody else just took — and the candidate's next
     * action is different in all four.
     *
     * @throws SlotUnavailable
     */
    public function assertBookable(Interview $interview, CarbonImmutable $start): void
    {
        $timezone = $this->timezoneFor($interview);
        $config = $this->config();
        $window = $this->windowFor($interview);
        $local = $start->setTimezone($timezone);

        if ($local > $window['end']) {
            throw new SlotUnavailable(__('interview.slot_outside_window', [
                'date' => $window['end']->translatedFormat('j M Y'),
            ]));
        }

        if ($config['skip_weekends'] && $local->isWeekend()) {
            throw new SlotUnavailable(__('interview.slot_not_a_working_day'));
        }

        // Grid membership rather than modular arithmetic on the minutes. The
        // two agree on a zone with no DST and disagree on one that has it, and
        // the grid is the thing the page actually drew.
        $onGrid = collect($this->cellsFor($local->startOfDay(), $config))
            ->contains(fn (CarbonImmutable $cell) => $cell->equalTo($local));

        if (! $onGrid) {
            throw new SlotUnavailable(__('interview.slot_off_grid', [
                'from' => $this->hourLabel($config['day_start_hour']),
                'to' => $this->hourLabel($config['day_end_hour']),
            ]));
        }

        if ($local < $this->earliestBookable($timezone)) {
            throw new SlotUnavailable(__('interview.slot_too_soon', [
                'hours' => $config['lead_time_hours'],
            ]));
        }

        // Looked up by exact instant, not by "the query returned rows": the
        // range `takenInstants` scans is a day wide, so a non-empty result
        // means somebody booked *something* nearby, not this.
        if ($this->takenInstants($local, $local, $interview->id)->has($this->instantKey($local))) {
            throw new SlotUnavailable(__('interview.slot_just_taken'));
        }
    }

    /** The end of a booking that starts here. */
    public function endOf(CarbonImmutable $start): CarbonImmutable
    {
        return $start->addMinutes($this->config()['slot_minutes']);
    }

    /**
     * The soonest instant worth offering.
     *
     * Lead time exists so a candidate is not invited to a call forty minutes
     * from now, which is a call nobody can take somewhere quiet.
     */
    public function earliestBookable(string $timezone): CarbonImmutable
    {
        return CarbonImmutable::now($timezone)->addHours($this->config()['lead_time_hours']);
    }

    /**
     * Instants already spoken for, keyed for cheap lookup.
     *
     * Counts confirmed slots and interviews scheduled by any other route — a
     * recruiter setting a time by hand, a retry moved by the orchestrator.
     * Anything that will occupy the single call line belongs here regardless
     * of how it got there.
     *
     * `$exceptInterviewId` keeps an interview from colliding with its own
     * booking, which matters on a reschedule.
     *
     * @return Collection<string, true>
     */
    public function takenInstants(
        CarbonInterface $from,
        CarbonInterface $to,
        ?int $exceptInterviewId = null,
    ): Collection {
        $fromUtc = CarbonImmutable::parse($from)->utc();
        $toUtc = CarbonImmutable::parse($to)->utc()->addDay();

        $selected = InterviewSlot::query()
            ->selected()
            ->whereBetween('starts_at', [$fromUtc, $toUtc])
            ->when($exceptInterviewId, fn ($q) => $q->where('interview_id', '!=', $exceptInterviewId))
            ->pluck('starts_at');

        $scheduled = Interview::query()
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$fromUtc, $toUtc])
            ->whereIn('status', [
                InterviewStatus::SCHEDULED,
                InterviewStatus::STARTING,
                InterviewStatus::IN_PROGRESS,
            ])
            ->when($exceptInterviewId, fn ($q) => $q->where('id', '!=', $exceptInterviewId))
            ->pluck('scheduled_at');

        return $selected->concat($scheduled)
            ->filter()
            ->mapWithKeys(fn ($t) => [$this->instantKey($t) => true]);
    }

    /**
     * One day's windows, in the candidate's zone.
     *
     * Built by stepping from the day's opening hour rather than by generating
     * clock times, so a day that is 23 or 25 hours long yields the windows
     * that actually exist on it.
     *
     * @param  array<string, mixed>  $config
     * @return array<int, CarbonImmutable>
     */
    public function cellsFor(CarbonImmutable $day, ?array $config = null): array
    {
        $config ??= $this->config();

        $midnight = $day->startOfDay();
        $open = $midnight->addHours($config['day_start_hour']);
        $close = $midnight->addHours($config['day_end_hour']);

        $cells = [];
        $cursor = $open;

        // `addMinutes(...) <= $close`, not `$cursor < $close`: the last window
        // has to *finish* by closing time. With 8-20 and half-hours the final
        // offer is 19:30, not 20:00.
        while ($cursor->addMinutes($config['slot_minutes']) <= $close) {
            $cells[] = $cursor;
            $cursor = $cursor->addMinutes($config['slot_minutes']);
        }

        return $cells;
    }

    public function timezoneFor(Interview $interview): string
    {
        return $interview->timezone
            ?: (string) config('services.interview.invitation.timezone', 'Asia/Tokyo');
    }

    /**
     * Settings for the calendar, clamped into shapes that produce a calendar.
     *
     * A misconfigured `day_end_hour` below `day_start_hour` would otherwise
     * yield zero windows on every day, which reads as "fully booked" to a
     * candidate and as "the feature is broken" to everybody else.
     *
     * @return array{slot_minutes:int, lead_time_hours:int, window_days:int,
     *               day_start_hour:int, day_end_hour:int, skip_weekends:bool}
     */
    public function config(): array
    {
        $prefix = 'services.interview.invitation.';

        $start = max(0, min(23, (int) config($prefix.'business_start_hour', 8)));
        $end = max($start + 1, min(24, (int) config($prefix.'business_end_hour', 20)));

        return [
            'slot_minutes' => max(5, (int) config($prefix.'slot_minutes', 30)),
            'lead_time_hours' => max(0, (int) config($prefix.'lead_time_hours', 2)),
            'window_days' => max(1, (int) config($prefix.'horizon_days', 14)),
            'day_start_hour' => $start,
            'day_end_hour' => $end,
            'skip_weekends' => (bool) config($prefix.'skip_weekends', false),
        ];
    }

    /** One instant, written one way, so two sources compare. */
    private function instantKey(CarbonInterface $when): string
    {
        return CarbonImmutable::parse($when)->utc()->format('Y-m-d H:i:s');
    }

    private function hourLabel(int $hour): string
    {
        return sprintf('%02d:00', $hour % 24);
    }
}
