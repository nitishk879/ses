<?php

namespace App\Services;

use App\Enums\InterviewSlotStatus;
use App\Models\InterviewSlot;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Chooses which times to put in front of a candidate.
 *
 * The hard constraint behind all of this is one line of configuration:
 * `INTERVIEW_MAX_CONCURRENT_CALLS=1`. The shared TTS batches on a 30ms window,
 * and two simultaneous calls have been measured producing 5-14 seconds of dead
 * air — which a candidate hears as a dropped line. So the calendar is a single
 * resource: two interviews must not be scheduled to overlap, or the second one
 * is refused at dial time and a candidate is stood up.
 *
 * That makes slot generation a scheduling problem rather than a formatting one:
 *
 * * windows are discrete and non-overlapping, so "ten o'clock" means one slot
 *   and not a range two bookings can both fall inside;
 * * any window already taken by another interview is excluded before it is
 *   ever offered;
 * * lead time keeps us from offering a slot the candidate cannot realistically
 *   make, and a horizon keeps us from offering one they will have forgotten;
 * * offers are spread across days rather than bunched, because three options
 *   inside one hour is one option with extra steps.
 *
 * Offering still does not reserve — see the migration for why — so the
 * exclusion here is best-effort at offer time and the real guard is the atomic
 * confirmation in {@see InterviewSchedulingService}.
 */
class InterviewSlotGenerator
{
    /**
     * Produce the windows to offer, soonest first.
     *
     * @param  string  $timezone   the candidate's zone; business hours are theirs, not ours
     * @param  int     $count      how many options to return
     * @return Collection<int, array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>
     */
    public function generate(string $timezone, int $count): Collection
    {
        $config = $this->config();

        $windows = $this->candidateWindows($timezone, $config);

        if ($windows->isEmpty()) {
            return collect();
        }

        $taken = $this->takenStarts($windows->first()['starts_at'], $windows->last()['starts_at']);

        $free = $windows->reject(
            fn (array $w) => $taken->contains($w['starts_at']->utc()->format('Y-m-d H:i:s'))
        )->values();

        return $this->spreadAcrossDays($free, $count, $timezone);
    }

    /**
     * Every business-hours window inside the horizon, in the candidate's zone.
     *
     * @return Collection<int, array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>
     */
    private function candidateWindows(string $timezone, array $config): Collection
    {
        $slotMinutes = $config['slot_minutes'];

        // Lead time is applied before rounding, then the first usable window is
        // the next boundary after it — so a 24h lead on a 30-minute grid never
        // yields a slot 23h59m away.
        $earliest = CarbonImmutable::now($timezone)->addHours($config['lead_time_hours']);
        $latest = CarbonImmutable::now($timezone)->addDays($config['horizon_days'])->endOfDay();

        $windows = collect();
        $day = $earliest->startOfDay();

        while ($day <= $latest) {
            if (! $this->isWorkingDay($day, $config)) {
                $day = $day->addDay();
                continue;
            }

            $cursor = $day->setTime($config['business_start'], 0);
            $dayEnd = $day->setTime($config['business_end'], 0);

            while ($cursor->addMinutes($slotMinutes) <= $dayEnd) {
                if ($cursor >= $earliest && $cursor <= $latest) {
                    $windows->push([
                        'starts_at' => $cursor,
                        'ends_at' => $cursor->addMinutes($slotMinutes),
                    ]);
                }
                $cursor = $cursor->addMinutes($slotMinutes);
            }

            $day = $day->addDay();
        }

        return $windows;
    }

    /**
     * Start times already spoken for, as UTC strings for cheap comparison.
     *
     * Counts both confirmed slots and interviews scheduled by any other route
     * (a recruiter setting a time by hand, a retry rescheduled by the
     * orchestrator). Anything that will occupy the single call line belongs
     * here, regardless of how it got there.
     *
     * @return Collection<int, string>
     */
    private function takenStarts(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $fromUtc = $from->copy()->utc();
        $toUtc = $to->copy()->utc()->addDay();

        $selected = InterviewSlot::query()
            ->selected()
            ->whereBetween('starts_at', [$fromUtc, $toUtc])
            ->pluck('starts_at');

        $scheduled = \App\Models\Interview::query()
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$fromUtc, $toUtc])
            ->whereIn('status', [
                \App\Enums\InterviewStatus::SCHEDULED,
                \App\Enums\InterviewStatus::STARTING,
                \App\Enums\InterviewStatus::IN_PROGRESS,
            ])
            ->pluck('scheduled_at');

        return $selected->concat($scheduled)
            ->filter()
            ->map(fn ($t) => $t->copy()->utc()->format('Y-m-d H:i:s'))
            ->unique()
            ->values();
    }

    /**
     * Pick `count` windows, preferring one per day before doubling up.
     *
     * Three slots inside one hour is effectively one option: a candidate who
     * cannot make Tuesday morning cannot make any of them. Walking day by day
     * costs nothing and turns the offer into a real choice.
     *
     * @param  Collection<int, array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>  $free
     * @return Collection<int, array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>
     */
    private function spreadAcrossDays(Collection $free, int $count, string $timezone): Collection
    {
        if ($free->isEmpty() || $count < 1) {
            return collect();
        }

        $byDay = $free->groupBy(
            fn (array $w) => $w['starts_at']->setTimezone($timezone)->format('Y-m-d')
        );

        $picked = collect();
        $round = 0;

        // Round-robin over days: first option from each day, then second, and
        // so on, until we have enough or the calendar runs out.
        while ($picked->count() < $count) {
            $addedThisRound = false;

            foreach ($byDay as $windows) {
                if ($picked->count() >= $count) {
                    break;
                }
                if ($windows->has($round)) {
                    $picked->push($windows->get($round));
                    $addedThisRound = true;
                }
            }

            if (! $addedThisRound) {
                break;
            }

            $round++;
        }

        return $picked
            ->sortBy(fn (array $w) => $w['starts_at']->getTimestamp())
            ->values();
    }

    private function isWorkingDay(CarbonInterface $day, array $config): bool
    {
        return ! ($config['skip_weekends'] && $day->isWeekend());
    }

    /**
     * @return array{slot_minutes:int, lead_time_hours:int, horizon_days:int,
     *               business_start:int, business_end:int, skip_weekends:bool}
     */
    private function config(): array
    {
        $prefix = 'services.interview.invitation.';

        return [
            'slot_minutes' => max(5, (int) config($prefix.'slot_minutes', 30)),
            'lead_time_hours' => max(0, (int) config($prefix.'lead_time_hours', 24)),
            'horizon_days' => max(1, (int) config($prefix.'horizon_days', 7)),
            'business_start' => (int) config($prefix.'business_start_hour', 10),
            'business_end' => (int) config($prefix.'business_end_hour', 18),
            'skip_weekends' => (bool) config($prefix.'skip_weekends', true),
        ];
    }
}
