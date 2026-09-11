<?php

namespace Tests\Feature;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewSlotStatus;
use App\Enums\InterviewStatus;
use App\Models\Interview;
use App\Models\Project;
use App\Models\Talent;
use App\Models\User;
use App\Services\InterviewInvitationService;
use App\Services\InterviewSchedulingService;
use App\Services\InterviewSlotGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Adversarial cover for tasks 5-7: the calls nobody makes on purpose.
 *
 * The happy-path tests prove the feature works when used as intended. These
 * ask what happens when it is not — a service method called directly rather
 * than through the command that guards it, a timezone with daylight saving, a
 * forwarded link, an id typed into the address bar.
 */
class InterviewInvitationHardeningTest extends TestCase
{
    use RefreshDatabase;

    private static int $phoneSeq = 1000;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.interview.invitation', [
            'min_match_score' => 70,
            'slots_offered' => 3,
            'offer_valid_hours' => 72,
            'lead_time_hours' => 24,
            'horizon_days' => 7,
            'business_start_hour' => 10,
            'business_end_hour' => 18,
            'slot_minutes' => 30,
            'skip_weekends' => true,
            'timezone' => 'Asia/Tokyo',
        ]);

        CarbonImmutable::setTestNow('2026-09-14 09:00:00');
        \Carbon\Carbon::setTestNow('2026-09-14 09:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        \Carbon\Carbon::setTestNow();
        parent::tearDown();
    }

    private function candidate(): Talent
    {
        $n = ++self::$phoneSeq;

        return Talent::factory()->create([
            'user_id' => User::factory()->create([
                'email' => "c{$n}@example.test",
                'phone' => "090-9{$n}-0001",
            ])->id,
        ]);
    }

    private function invited(): Interview
    {
        Notification::fake();

        return app(InterviewInvitationService::class)->invite(
            Project::factory()->create(['title' => 'Backend Engineer']),
            $this->candidate(),
            85
        );
    }

    // ── the one that would quietly destroy a finished interview ──────────── #

    public function test_re_inviting_a_completed_interview_does_not_wipe_it(): void
    {
        /*
         * `shortlistFor()` excludes talents who already have an interview, so
         * the command path never reaches this. A controller calling the
         * service directly would — and the old code would have reset a
         * finished interview to SLOT_SELECTION and deleted the slot record of
         * how it was booked, because the "already invited" guard only looked
         * for a token, which confirmation deliberately destroys.
         */
        $interview = $this->invited();
        $project = $interview->project;
        $talent = $interview->talent;

        app(InterviewSchedulingService::class)->confirm($interview, $interview->slots->first());

        $interview->refresh();
        $interview->update(['status' => InterviewStatus::COMPLETED]);
        $scheduledAt = $interview->scheduled_at;

        app(InterviewInvitationService::class)->invite($project, $talent, 85);

        $interview->refresh();

        $this->assertSame(InterviewStatus::COMPLETED, $interview->status);
        $this->assertTrue($interview->scheduled_at->equalTo($scheduledAt));
        $this->assertSame(
            1,
            $interview->slots()->where('status', InterviewSlotStatus::SELECTED)->count(),
            'the confirmed slot was destroyed by a re-invite'
        );
        $this->assertNull($interview->invitation_token);
    }

    public function test_re_inviting_a_scheduled_interview_does_not_move_it(): void
    {
        $interview = $this->invited();
        $project = $interview->project;
        $talent = $interview->talent;

        app(InterviewSchedulingService::class)->confirm($interview, $interview->slots->first());
        $interview->refresh();
        $was = $interview->scheduled_at;

        app(InterviewInvitationService::class)->invite($project, $talent, 85);

        $interview->refresh();
        $this->assertSame(InterviewStatus::SCHEDULED, $interview->status);
        $this->assertTrue($interview->scheduled_at->equalTo($was));
    }

    public function test_an_expired_invitation_can_be_re_issued(): void
    {
        // The legitimate re-invite: nobody answered, the offer lapsed, and a
        // recruiter wants to try again. This one MUST work.
        $interview = $this->invited();
        $project = $interview->project;
        $talent = $interview->talent;

        $interview->update(['invitation_expires_at' => now()->subHour()]);
        app(InterviewSchedulingService::class)->expire($interview);

        $this->assertSame(InterviewStatus::RESCHEDULE_REQUIRED, $interview->fresh()->status);

        $again = app(InterviewInvitationService::class)->invite($project, $talent, 85);

        $this->assertSame(InterviewStatus::SLOT_SELECTION, $again->status);
        $this->assertNotNull($again->invitation_token);
        $this->assertCount(3, $again->slots()->where('status', InterviewSlotStatus::OFFERED)->get());
    }

    // ── the confirmation page ────────────────────────────────────────────── #

    public function test_the_confirmation_page_cannot_be_enumerated_by_id(): void
    {
        /*
         * Without a signature, walking /interview/scheduled/1..n would list
         * every booked interview's role and time to anyone who tried.
         */
        $interview = $this->invited();
        app(InterviewSchedulingService::class)->confirm($interview, $interview->slots->first());

        $this->get('/interview/scheduled/'.$interview->id)
            ->assertForbidden();
    }

    public function test_the_candidate_reaches_the_confirmation_page_after_choosing(): void
    {
        $interview = $this->invited();
        $slot = $interview->slots->first();

        $response = $this->post(
            route('interview-slots.store', ['token' => $interview->invitation_token]),
            ['slot_id' => $slot->id]
        );

        $response->assertRedirect();

        // Following the redirect the app itself issued must work.
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee(__('interview.page.confirmed_title'));
    }

    // ── slot generation under stress ─────────────────────────────────────── #

    public function test_slots_are_generated_for_a_timezone_that_observes_dst(): void
    {
        // Asia/Tokyo has no DST, so a zone that does is the only way to find
        // out whether setTime() across a transition blows up.
        $slots = app(InterviewSlotGenerator::class)->generate('Europe/London', 3);

        $this->assertCount(3, $slots);
        foreach ($slots as $slot) {
            $local = $slot['starts_at']->setTimezone('Europe/London');
            $this->assertGreaterThanOrEqual(10, $local->hour);
            $this->assertLessThan(18, $local->hour);
        }
    }

    public function test_asking_for_more_slots_than_a_day_holds_still_spreads(): void
    {
        $slots = app(InterviewSlotGenerator::class)->generate('Asia/Tokyo', 6);

        $this->assertCount(6, $slots);

        $days = $slots->map(fn ($s) => $s['starts_at']->setTimezone('Asia/Tokyo')->format('Y-m-d'));

        // Round-robin over days means six options should not all be one day.
        $this->assertGreaterThan(1, $days->unique()->count());
        // ...and they must still be distinct instants.
        $this->assertSame(6, $slots->map(fn ($s) => $s['starts_at']->toIso8601String())->unique()->count());
    }

    public function test_slots_never_overlap_each_other(): void
    {
        // With one concurrent call line, two offers that overlap are two
        // offers that cannot both be honoured.
        $slots = app(InterviewSlotGenerator::class)->generate('Asia/Tokyo', 6)
            ->sortBy(fn ($s) => $s['starts_at']->getTimestamp())
            ->values();

        for ($i = 1; $i < $slots->count(); $i++) {
            $this->assertTrue(
                $slots[$i]['starts_at'] >= $slots[$i - 1]['ends_at'],
                'slot '.$i.' starts before the previous one ends'
            );
        }
    }

    public function test_asking_for_zero_slots_returns_nothing_rather_than_everything(): void
    {
        $this->assertCount(0, app(InterviewSlotGenerator::class)->generate('Asia/Tokyo', 0));
    }

    // ── the link, misused ────────────────────────────────────────────────── #

    public function test_a_forwarded_link_cannot_book_a_second_time(): void
    {
        $interview = $this->invited();
        $token = $interview->invitation_token;
        $slots = $interview->slots;

        $this->post(route('interview-slots.store', ['token' => $token]), [
            'slot_id' => $slots->first()->id,
        ])->assertRedirect();

        // Somebody forwarded the email; the recipient tries another slot.
        $this->post(route('interview-slots.store', ['token' => $token]), [
            'slot_id' => $slots->last()->id,
        ])->assertRedirect(route('interview-slots.show', ['token' => $token]));

        $interview->refresh();
        $this->assertTrue($interview->scheduled_at->equalTo($slots->first()->starts_at));
        $this->assertSame(
            InterviewSlotStatus::RELEASED,
            $slots->last()->fresh()->status
        );
    }

    public function test_a_malformed_token_never_reaches_the_controller(): void
    {
        // Route constraint is [0-9a-f]{64}; anything else is a 404 from the
        // router, not a database query.
        $this->get('/interview/slots/not-a-token')->assertNotFound();
        $this->get('/interview/slots/'.str_repeat('z', 64))->assertNotFound();
        $this->get('/interview/slots/'.str_repeat('a', 63))->assertNotFound();
    }

    public function test_posting_a_slot_id_from_another_interview_is_refused(): void
    {
        $mine = $this->invited();
        $theirs = $this->invited();

        $this->from(route('interview-slots.show', ['token' => $mine->invitation_token]))
            ->post(route('interview-slots.store', ['token' => $mine->invitation_token]), [
                'slot_id' => $theirs->slots->first()->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('slot_error');

        $this->assertSame(InterviewStatus::SLOT_SELECTION, $mine->fresh()->status);
        $this->assertNull($theirs->fresh()->scheduled_at);
    }

    public function test_a_stale_csrf_token_bounces_back_instead_of_419ing(): void
    {
        /*
         * This page is opened from an email and then sat on — the candidate
         * reads it, checks their calendar, comes back hours later and presses
         * confirm. Past SESSION_LIFETIME that is a "Page Expired" screen,
         * which on any other form is an annoyance and here is a lost
         * interview: no explanation, no reason to try again.
         */
        $interview = $this->invited();

        /*
         * The exception handler is exercised directly rather than through a
         * request: Laravel's CSRF middleware short-circuits whenever
         * `runningUnitTests()` is true, so no test request can ever produce a
         * TokenMismatchException. Building the request and handing it to the
         * handler is the only way to assert the branch that real candidates
         * will hit.
         */
        $url = route('interview-slots.store', ['token' => $interview->invitation_token]);

        $request = \Illuminate\Http\Request::create($url, 'POST');
        $request->setLaravelSession(app('session.store'));
        $request->setRouteResolver(
            fn () => \Illuminate\Support\Facades\Route::getRoutes()->match($request)
        );

        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new \Illuminate\Session\TokenMismatchException());

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertSame(
            route('interview-slots.show', ['token' => $interview->invitation_token]),
            $response->getTargetUrl()
        );

        // Nothing was booked, so the candidate can simply choose again.
        $this->assertNull($interview->fresh()->scheduled_at);
    }

    public function test_a_stale_csrf_token_elsewhere_is_left_alone(): void
    {
        // The handler must only special-case the slot picker; every other form
        // keeps Laravel's normal 419 behaviour.
        $request = \Illuminate\Http\Request::create('/login', 'POST');
        $request->setLaravelSession(app('session.store'));
        $request->setRouteResolver(
            fn () => \Illuminate\Support\Facades\Route::getRoutes()->match($request)
        );

        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new \Illuminate\Session\TokenMismatchException());

        $this->assertSame(419, $response->getStatusCode());
    }

    public function test_the_language_files_have_no_missing_keys_between_them(): void
    {
        // A key present in English and absent in Japanese renders as the raw
        // key in a candidate-facing email, which is the kind of thing nobody
        // notices until a client does.
        $flatten = function (array $a, string $prefix = '') use (&$flatten): array {
            $out = [];
            foreach ($a as $k => $v) {
                $key = $prefix === '' ? (string) $k : "{$prefix}.{$k}";
                $out = is_array($v) ? array_merge($out, $flatten($v, $key)) : array_merge($out, [$key]);
            }

            return $out;
        };

        $en = $flatten(require base_path('lang/en/interview.php'));
        $jp = $flatten(require base_path('lang/jp/interview.php'));

        $this->assertSame([], array_values(array_diff($en, $jp)), 'keys missing from lang/jp');
        $this->assertSame([], array_values(array_diff($jp, $en)), 'keys missing from lang/en');
    }

    public function test_a_missing_slot_id_is_a_validation_error_not_a_crash(): void
    {
        $interview = $this->invited();

        $this->from(route('interview-slots.show', ['token' => $interview->invitation_token]))
            ->post(route('interview-slots.store', ['token' => $interview->invitation_token]), [])
            ->assertSessionHasErrors('slot_id');
    }

    // ── handoff to the dialer ────────────────────────────────────────────── #

    public function test_a_confirmed_interview_is_picked_up_by_the_scheduler(): void
    {
        /*
         * The whole point of tasks 5-7: selection has to hand off to the call
         * pipeline with nothing in between. This asserts the seam.
         */
        config()->set('services.interview.enabled', true);
        config()->set('services.interview.from_number', '+815018076789');

        $interview = $this->invited();
        $slot = $interview->slots->first();

        app(InterviewSchedulingService::class)->confirm($interview, $slot);
        $interview->refresh();

        $this->assertSame(InterviewStatus::SCHEDULED, $interview->status);
        $this->assertCount(1, $interview->attempts);
        $this->assertSame(InterviewAttemptStatus::PENDING, $interview->attempts->first()->status);

        // Wind the clock to the booked time and let the scheduler look.
        \Carbon\Carbon::setTestNow($slot->starts_at->copy()->addMinute());
        CarbonImmutable::setTestNow($slot->starts_at->copy()->addMinute());

        \Illuminate\Support\Facades\Queue::fake();
        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\StartInterviewAttemptJob::class);
        $this->assertSame(InterviewStatus::STARTING, $interview->fresh()->status);
    }

    public function test_confirming_does_not_create_a_second_pending_attempt(): void
    {
        $interview = $this->invited();

        // A retry attempt already exists from an earlier round.
        $interview->attempts()->create([
            'attempt_number' => 1,
            'status' => InterviewAttemptStatus::PENDING,
            'channel' => 'phone',
        ]);

        app(InterviewSchedulingService::class)->confirm($interview, $interview->slots->first());

        $this->assertSame(1, $interview->fresh()->attempts()->count());
    }
}
