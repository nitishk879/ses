<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use App\Support\InterviewTime;

/**
 * Turn times a recruiter typed into bookable windows.
 *
 * This used to also *choose* times — walk the calendar, skip what was taken,
 * spread the offers across days. That job now belongs to
 * {@see InterviewAvailabilityService}, which draws the calendar the candidate
 * books from; having a second thing decide independently which windows exist
 * is how a page comes to offer a time the server will refuse.
 *
 * What is left is the recruiter's override, and it deliberately obeys almost
 * none of the calendar's rules: no lead time, no business hours, no weekend
 * skipping. A recruiter asking for Saturday at seven has a reason, and a
 * scheduler that silently moves it is one they will stop using.
 *
 * **Two things it does enforce.**
 *
 * The zone. A `datetime-local` field posts a naive "2026-09-16T14:00" with no
 * offset at all, and reading that on a UTC server yields a time nine hours
 * away from the one the recruiter meant. It is parsed in the interview's
 * timezone — the candidate's — because that is the clock both of them will
 * read the email in.
 *
 * The past. Checked here, on the server, against the same `now()` the rest of
 * the flow uses. The form also carries a `min` attribute, but that is a
 * convenience for the person typing, not a control: it is trivially edited,
 * and it cannot see a form left open over lunch.
 */
class InterviewSlotGenerator
{
    /**
     * @param  array<int, string>  $localTimes  naive "Y-m-d\TH:i" strings
     * @return Collection<int, array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>
     *
     * @throws \InvalidArgumentException when a time is unreadable or in the past
     */
    public function fromExplicit(array $localTimes, string $timezone): Collection
    {
        $minutes = max(5, (int) config('services.interview.invitation.slot_minutes', 30));
        $now = CarbonImmutable::now($timezone);

        $windows = [];

        foreach ($localTimes as $raw) {
            $raw = trim((string) $raw);

            if ($raw === '') {
                // A blank row is an unused row, not an error. The form offers
                // more boxes than most invitations need.
                continue;
            }

            try {
                $start = CarbonImmutable::parse($raw, $timezone);
            } catch (\Throwable) {
                throw new \InvalidArgumentException(
                    __('interview.slot_time_unreadable', ['value' => $raw])
                );
            }

            if ($start->lessThanOrEqualTo($now)) {
                throw new \InvalidArgumentException(
                    __('interview.slot_time_in_past', [
                        'value' => InterviewTime::full($start, $timezone),
                    ])
                );
            }

            // Keyed on the instant, so the same time typed twice — or typed
            // once as 14:00 and once as 14:00:00 — offers one slot, not two.
            $windows[$start->utc()->format('Y-m-d H:i:s')] = [
                'starts_at' => $start,
                'ends_at' => $start->addMinutes($minutes),
            ];
        }

        // Soonest first, which is the order they are numbered in the email.
        ksort($windows);

        return collect(array_values($windows));
    }
}
