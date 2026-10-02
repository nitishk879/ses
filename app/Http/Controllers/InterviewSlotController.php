<?php

namespace App\Http\Controllers;

use App\Exceptions\Interview\SlotUnavailable;
use App\Models\InterviewSlot;
use App\Services\InterviewAvailabilityService;
use App\Services\InterviewSchedulingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Support\InterviewTime;

/**
 * The page a candidate lands on from the invitation email (task 7).
 *
 * Unauthenticated on purpose. A shortlisted talent may not have logged into
 * SES for months, and putting a password prompt between an invitation and a
 * reply is the single most reliable way to lose the reply. The token is the
 * credential: 32 bytes of CSPRNG output, single-use, and expiring.
 *
 * Consequences of that, handled here rather than assumed away:
 *
 * * an unknown or used token renders an explanatory page, never a 404 that
 *   leaves someone wondering whether they mistyped a link they never typed;
 * * the page is marked `noindex` and sent with no-store, so a forwarded or
 *   crawled URL does not end up in a search result or a shared cache;
 * * confirming is a POST behind CSRF, so a link preview or an email client
 *   prefetching the URL cannot book a slot by accident.
 */
class InterviewSlotController extends Controller
{
    public function __construct(
        private readonly InterviewSchedulingService $scheduling,
        private readonly InterviewAvailabilityService $availability,
    ) {
    }

    /**
     * Show the times on offer — a calendar, or a short pinned list.
     */
    public function show(Request $request, string $token): View|RedirectResponse
    {
        $interview = $this->scheduling->findByToken($token);

        if (! $interview) {
            /*
             * The token is destroyed on booking, so a second tap that arrives
             * after the first has committed — or the candidate re-opening the
             * email a minute later in the same browser — finds nothing. Sent
             * to their confirmation instead of "this link is no longer valid",
             * which reads as the booking having failed.
             *
             * Session-scoped, so it only ever recognises the browser that made
             * the booking; anyone else holding the link still gets the
             * not-recognised page and learns nothing.
             */
            $bookedId = $request->session()->get(self::bookedKey($token));

            if ($bookedId) {
                return $this->toConfirmation((int) $bookedId);
            }

            return $this->page('interviews.slots.unavailable', [
                'reason' => __('interview.link_not_recognised'),
            ]);
        }

        if (! $this->scheduling->invitationIsOpen($interview)) {
            return $this->page('interviews.slots.unavailable', [
                'reason' => $interview->slot_selected_at
                    ? __('interview.already_scheduled_notice', [
                        'date' => InterviewTime::full(
                            $interview->scheduled_at,
                            $interview->timezone ?: config('services.interview.invitation.timezone', 'Asia/Tokyo')
                        ),
                    ])
                    : __('interview.invitation_expired'),
            ]);
        }

        $common = [
            'interview' => $interview,
            'timezone' => $interview->timezone,
            'minutes' => max(1, (int) round(
                ((int) config('services.interview.duration_seconds', 300)) / 60
            )),
        ];

        if ($interview->offersCalendar()) {
            $days = $this->availability->calendar($interview);

            /*
             * Nothing left to offer. Two different causes, one sentence.
             *
             * Either every remaining half-hour is spoken for, or the window is
             * still formally open but has run out of usable time — at 19:00 on
             * the last day, a two-hour lead puts the earliest bookable instant
             * past the 20:00 close.
             *
             * Deliberately does not say "fully booked". In the second case
             * nobody took anything, and a candidate told otherwise would
             * reasonably ask who beat them to it. It also cannot say "all the
             * times offered have passed", which would be a lie about a window
             * that has not closed.
             */
            if ($days->sum('available_count') === 0) {
                return $this->page('interviews.slots.unavailable', [
                    'reason' => __('interview.calendar_nothing_left'),
                ]);
            }

            $config = $this->availability->config();
            $window = $this->availability->windowFor($interview);

            return $this->page('interviews.slots.calendar', $common + [
                'days' => $days,
                // The day to open on: the first with anything left in it, so a
                // candidate reading on day nine does not land on an empty one.
                'openDate' => $days->firstWhere('available_count', '>', 0)['date'] ?? null,
                'opensAt' => sprintf('%02d:00', $config['day_start_hour']),
                'closesAt' => sprintf('%02d:00', $config['day_end_hour'] % 24),
                'until' => $window['end'],
            ]);
        }

        $choosable = $interview->slots->filter->isChoosable()->values();

        if ($choosable->isEmpty()) {
            // Every offered time has passed while the invitation sat unopened.
            return $this->page('interviews.slots.unavailable', [
                'reason' => __('interview.all_slots_passed'),
            ]);
        }

        return $this->page('interviews.slots.show', $common + ['slots' => $choosable]);
    }

    /**
     * Confirm one of them.
     */
    public function store(Request $request, string $token): RedirectResponse
    {
        $interview = $this->scheduling->findByToken($token);

        if (! $interview || ! $this->scheduling->invitationIsOpen($interview)) {
            return redirect()->route('interview-slots.show', ['token' => $token]);
        }

        try {
            $confirmed = $interview->offersCalendar()
                ? $this->confirmFromCalendar($request, $interview)
                : $this->confirmPinned($request, $interview);
        } catch (SlotUnavailable $e) {
            /*
             * A double tap that lost to itself.
             *
             * On a slow phone connection "Confirm" gets pressed twice. Both
             * requests pass the checks above; the first books and destroys the
             * token while the second waits on the row lock, then finds the
             * invitation closed and throws. Reported as an error, the
             * candidate reads "this invitation has expired" a second after
             * successfully booking — and either rings to complain or, worse,
             * assumes it failed and does not pick up.
             *
             * Re-read rather than trusted: if this interview is now booked, it
             * was booked through this candidate's own link, so the
             * confirmation is theirs to see.
             */
            $now = $interview->fresh();

            if ($now?->slot_selected_at && $now->status === \App\Enums\InterviewStatus::SCHEDULED) {
                return $this->toConfirmation($now->id);
            }

            // Otherwise expected: somebody else took this window between the
            // page being rendered and the button being pressed, or the page
            // sat open until the time passed. Re-render with what is left
            // rather than reporting a failure.
            return back()->with('slot_error', $e->getMessage());
        }

        // Remembered in this browser's session, keyed by a hash of the token
        // rather than the token — see show(). Lets a second tap that arrives
        // after this one has fully committed land on the same confirmation.
        $request->session()->put(self::bookedKey($token), $interview->id);

        return $this->toConfirmation($interview->id)
            ->with('confirmed_slot', $confirmed->id);
    }

    /**
     * Where a successful booking lands.
     *
     * Relative signature: valid for a day, and unaffected by the app being
     * reached on a host that differs from APP_URL.
     */
    private function toConfirmation(int $interviewId): RedirectResponse
    {
        return redirect()->to(URL::temporarySignedRoute(
            'interview-slots.confirmed',
            now()->addDay(),
            ['interview' => $interviewId],
            absolute: false
        ));
    }

    /**
     * Session key recording that this browser booked with this token.
     *
     * Hashed so the session store never holds a usable credential; the token
     * is dead by the time this is written anyway, but a session table is the
     * kind of thing that ends up in a backup nobody thought about.
     */
    private static function bookedKey(string $token): string
    {
        return 'interview.booked.'.hash('sha256', $token);
    }

    /**
     * A time the candidate picked off the calendar.
     *
     * Validated as a shape here and as a *time* in the service. The two are
     * different jobs: this one rejects a field that is missing or absurdly
     * long before any of it reaches a date parser, and the service decides
     * whether the instant it describes is one this invitation may book.
     *
     * @throws SlotUnavailable
     */
    private function confirmFromCalendar(Request $request, \App\Models\Interview $interview): InterviewSlot
    {
        $start = trim((string) $request->input('slot_start', ''));

        if ($start === '') {
            // Nothing was chosen. Not an error worth a stack trace — the
            // candidate pressed the button before picking a time.
            throw new SlotUnavailable(__('interview.slot_none_chosen'));
        }

        if (strlen($start) > 32) {
            throw new SlotUnavailable(__('interview.slot_time_unreadable', ['value' => '…']));
        }

        return $this->scheduling->confirmAt($interview, $start);
    }

    /**
     * One of the times a recruiter pinned.
     *
     * @throws SlotUnavailable
     */
    private function confirmPinned(Request $request, \App\Models\Interview $interview): InterviewSlot
    {
        $slotId = $request->input('slot_id');

        if (! is_numeric($slotId)) {
            throw new SlotUnavailable(__('interview.slot_none_chosen'));
        }

        $slot = InterviewSlot::find((int) $slotId);

        if (! $slot) {
            throw new SlotUnavailable(__('interview.slot_no_longer_available'));
        }

        return $this->scheduling->confirm($interview, $slot);
    }

    /**
     * The "you're booked" page.
     *
     * Addressed by interview id rather than token because the token is
     * deliberately destroyed on confirmation — the booking is no longer a
     * secret worth a credential, and the page reveals only a time the
     * candidate just chose.
     */
    public function confirmed(int $interview): View
    {
        $model = \App\Models\Interview::with('project')->find($interview);

        if (! $model || ! $model->scheduled_at) {
            return $this->page('interviews.slots.unavailable', [
                'reason' => __('interview.link_not_recognised'),
            ]);
        }

        return $this->page('interviews.slots.confirmed', [
            'interview' => $model,
            'timezone' => $model->timezone
                ?: (string) config('services.interview.invitation.timezone', 'Asia/Tokyo'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function page(string $view, array $data): View
    {
        // noindex / no-store / no-referrer come from the NoIndexNoStore
        // middleware on the route group, so every path out of this controller
        // carries them without each one having to remember.
        return view($view, $data);
    }
}
