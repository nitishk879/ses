<?php

namespace App\Http\Controllers;

use App\Exceptions\Interview\SlotUnavailable;
use App\Models\InterviewSlot;
use App\Services\InterviewSchedulingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

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
    ) {
    }

    /**
     * Show the offered times.
     */
    public function show(string $token): View
    {
        $interview = $this->scheduling->findByToken($token);

        if (! $interview) {
            return $this->page('interviews.slots.unavailable', [
                'reason' => __('interview.link_not_recognised'),
            ]);
        }

        if (! $this->scheduling->invitationIsOpen($interview)) {
            return $this->page('interviews.slots.unavailable', [
                'reason' => $interview->slot_selected_at
                    ? __('interview.already_scheduled_notice', [
                        'date' => $interview->scheduled_at?->setTimezone($interview->timezone)
                            ->translatedFormat('D, j M Y H:i (T)') ?? '',
                    ])
                    : __('interview.invitation_expired'),
            ]);
        }

        $choosable = $interview->slots->filter->isChoosable()->values();

        if ($choosable->isEmpty()) {
            // Every offered time has passed while the invitation sat unopened.
            return $this->page('interviews.slots.unavailable', [
                'reason' => __('interview.all_slots_passed'),
            ]);
        }

        return $this->page('interviews.slots.show', [
            'interview' => $interview,
            'slots' => $choosable,
            'timezone' => $interview->timezone,
            'minutes' => max(1, (int) round(
                ((int) config('services.interview.duration_seconds', 300)) / 60
            )),
        ]);
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

        $validated = $request->validate([
            'slot_id' => ['required', 'integer'],
        ]);

        $slot = InterviewSlot::find($validated['slot_id']);

        if (! $slot) {
            return back()->with('slot_error', __('interview.slot_no_longer_available'));
        }

        try {
            $confirmed = $this->scheduling->confirm($interview, $slot);
        } catch (SlotUnavailable $e) {
            // Expected: somebody else took this window between the page being
            // rendered and the button being pressed. Re-render with what is
            // left rather than reporting a failure.
            return back()->with('slot_error', $e->getMessage());
        }

        // Relative signature: valid for a day, and unaffected by the app being
        // reached on a host that differs from APP_URL.
        return redirect()
            ->to(URL::temporarySignedRoute(
                'interview-slots.confirmed',
                now()->addDay(),
                ['interview' => $interview->id],
                absolute: false
            ))
            ->with('confirmed_slot', $confirmed->id);
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
