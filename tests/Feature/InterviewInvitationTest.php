<?php

namespace Tests\Feature;

use App\Enums\InterviewSlotStatus;
use App\Enums\InterviewStatus;
use App\Models\AiMatch;
use App\Models\Interview;
use App\Models\InterviewSlot;
use App\Models\Project;
use App\Models\Talent;
use App\Models\User;
use App\Notifications\InterviewInvitation;
use App\Services\InterviewInvitationService;
use App\Services\InterviewSchedulingService;
use App\Services\InterviewSlotGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tasks 5, 6 and 7: shortlist → offer three times → candidate picks one.
 *
 * The behaviour that matters most here is the one that is easy to get wrong and
 * invisible when you do: **offering is not reserving**. Several candidates are
 * legitimately holding an email that proposes the same 10:00 window, and with
 * a single concurrent call line exactly one of them may end up booked into it.
 * A booking that slips through is not a cosmetic bug — it is a candidate
 * sitting by a phone that never rings.
 */
class InterviewInvitationTest extends TestCase
{
    use RefreshDatabase;

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
        config()->set('services.interview.duration_seconds', 300);

        // A fixed Monday so weekend skipping and horizon maths are assertable
        // rather than dependent on the day the suite happens to run.
        CarbonImmutable::setTestNow('2026-09-14 09:00:00');
        \Carbon\Carbon::setTestNow('2026-09-14 09:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        \Carbon\Carbon::setTestNow();
        parent::tearDown();
    }

    /** `users.phone` is unique, so each candidate needs their own number. */
    private static int $phoneSeq = 0;

    private function candidate(string $email = 'candidate@example.test'): Talent
    {
        $user = User::factory()->create([
            'email' => $email,
            'firstname' => 'Taro',
            'lastname' => 'Tanaka',
            'phone' => sprintf('090-1234-%04d', ++self::$phoneSeq),
        ]);

        return Talent::factory()->create(['user_id' => $user->id]);
    }

    private function invited(): Interview
    {
        Notification::fake();

        $project = Project::factory()->create(['title' => 'Backend Engineer']);
        $talent = $this->candidate();

        return app(InterviewInvitationService::class)->invite($project, $talent, 82);
    }

    // ── Task 5: shortlisting ─────────────────────────────────────────────── #

    public function test_shortlist_contains_only_candidates_at_or_above_the_threshold(): void
    {
        $project = Project::factory()->create();

        foreach ([95, 70, 69, 30] as $score) {
            $talent = $this->candidate("c{$score}@example.test");
            AiMatch::create([
                'project_id' => $project->id, 'talent_id' => $talent->id,
                'score' => $score, 'payload' => ['score' => $score],
                'scorer_version' => '1.0.0',
                'jd_source_hash' => str_repeat('a', 64),
                'resume_source_hash' => str_repeat('b', 64),
                'scored_at' => now(),
            ]);
        }

        $shortlist = app(InterviewInvitationService::class)->shortlistFor($project, 70);

        $this->assertSame([95, 70], $shortlist->pluck('score')->all());
    }

    public function test_an_already_invited_candidate_is_not_shortlisted_again(): void
    {
        Notification::fake();
        $project = Project::factory()->create();
        $talent = $this->candidate();

        AiMatch::create([
            'project_id' => $project->id, 'talent_id' => $talent->id,
            'score' => 90, 'payload' => [], 'scorer_version' => '1.0.0',
            'jd_source_hash' => str_repeat('a', 64),
            'resume_source_hash' => str_repeat('b', 64), 'scored_at' => now(),
        ]);

        $service = app(InterviewInvitationService::class);
        $this->assertCount(1, $service->shortlistFor($project, 70));

        $service->invite($project, $talent, 90);

        $this->assertCount(0, $service->shortlistFor($project, 70));
    }

    public function test_a_candidate_with_no_email_is_refused_rather_than_silently_skipped(): void
    {
        Notification::fake();
        $project = Project::factory()->create();
        $user = User::factory()->create(['email' => '']);
        $talent = Talent::factory()->create(['user_id' => $user->id]);

        $this->expectExceptionMessage('has no email address');

        app(InterviewInvitationService::class)->invite($project, $talent);
    }

    // ── Task 6: offering three times ─────────────────────────────────────── #

    public function test_invitation_creates_three_slots_and_sends_one_email(): void
    {
        Notification::fake();
        $project = Project::factory()->create(['title' => 'Backend Engineer']);
        $talent = $this->candidate();

        $interview = app(InterviewInvitationService::class)->invite($project, $talent, 82);

        $this->assertSame(InterviewStatus::SLOT_SELECTION, $interview->status);
        $this->assertCount(3, $interview->slots);
        $this->assertSame(82, $interview->match_score);
        $this->assertNotNull($interview->invitation_token);
        $this->assertSame(64, strlen($interview->invitation_token));
        $this->assertTrue($interview->invitation_expires_at->isFuture());

        Notification::assertSentTo($talent->user, InterviewInvitation::class);
    }

    public function test_offered_times_respect_lead_time_business_hours_and_weekends(): void
    {
        $slots = app(InterviewSlotGenerator::class)->generate('Asia/Tokyo', 3);

        $this->assertCount(3, $slots);

        foreach ($slots as $slot) {
            $local = $slot['starts_at']->setTimezone('Asia/Tokyo');

            $this->assertTrue(
                $local >= CarbonImmutable::now('Asia/Tokyo')->addHours(24),
                "slot {$local} is inside the 24h lead time"
            );
            $this->assertGreaterThanOrEqual(10, $local->hour, "slot {$local} is before business hours");
            $this->assertLessThan(18, $local->hour, "slot {$local} is after business hours");
            $this->assertFalse($local->isWeekend(), "slot {$local} falls on a weekend");
        }
    }

    public function test_offers_are_spread_across_days_rather_than_bunched(): void
    {
        // Three options inside one hour is one option with extra steps: a
        // candidate who cannot make that morning cannot make any of them.
        $slots = app(InterviewSlotGenerator::class)->generate('Asia/Tokyo', 3);

        $days = $slots
            ->map(fn ($s) => $s['starts_at']->setTimezone('Asia/Tokyo')->format('Y-m-d'))
            ->unique();

        $this->assertGreaterThan(1, $days->count(), 'all three slots landed on one day');
    }

    public function test_a_window_another_interview_already_booked_is_not_offered(): void
    {
        $taken = CarbonImmutable::now('Asia/Tokyo')->addDay()->setTime(10, 0)->utc();

        $other = Interview::create([
            'project_id' => Project::factory()->create()->id,
            'talent_id' => $this->candidate('other@example.test')->id,
            'status' => InterviewStatus::SCHEDULED,
            'scheduled_at' => $taken,
        ]);

        $slots = app(InterviewSlotGenerator::class)->generate('Asia/Tokyo', 6);

        $this->assertNotContains(
            $taken->format('Y-m-d H:i:s'),
            $slots->map(fn ($s) => $s['starts_at']->utc()->format('Y-m-d H:i:s'))->all(),
            'a window already booked by interview #'.$other->id.' was offered again'
        );
    }

    public function test_a_full_calendar_is_reported_not_papered_over_with_an_empty_offer(): void
    {
        // An email listing no times is worse than no email at all.
        config()->set('services.interview.invitation.horizon_days', 1);
        config()->set('services.interview.invitation.lead_time_hours', 400);

        Notification::fake();

        $this->expectExceptionMessage('No interview slots are available');

        app(InterviewInvitationService::class)->invite(
            Project::factory()->create(), $this->candidate()
        );
    }

    public function test_re_inviting_does_not_send_a_second_email(): void
    {
        Notification::fake();
        $project = Project::factory()->create();
        $talent = $this->candidate();
        $service = app(InterviewInvitationService::class);

        $first = $service->invite($project, $talent, 80);
        $second = $service->invite($project, $talent, 80);

        $this->assertSame($first->id, $second->id);
        Notification::assertSentToTimes($talent->user, InterviewInvitation::class, 1);
    }

    // ── Task 7: choosing ─────────────────────────────────────────────────── #

    public function test_the_candidate_can_open_the_link_and_see_their_times(): void
    {
        $interview = $this->invited();

        $response = $this->get(route('interview-slots.show', ['token' => $interview->invitation_token]));

        $response->assertOk();
        foreach ($interview->slots as $slot) {
            $response->assertSee($slot->presentIn('Asia/Tokyo'));
        }
    }

    public function test_the_slot_page_is_not_indexed_or_cached(): void
    {
        $interview = $this->invited();

        $this->get(route('interview-slots.show', ['token' => $interview->invitation_token]))
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertHeader('Referrer-Policy', 'no-referrer');
    }

    public function test_choosing_a_slot_schedules_the_interview_and_queues_an_attempt(): void
    {
        $interview = $this->invited();
        $slot = $interview->slots->first();

        $response = $this->post(
            route('interview-slots.store', ['token' => $interview->invitation_token]),
            ['slot_id' => $slot->id]
        );

        // The confirmation page is signature-gated, so the redirect carries a
        // signature rather than being a bare named route.
        $response->assertRedirectContains('/interview/scheduled/'.$interview->id);
        $response->assertRedirectContains('signature=');

        $interview->refresh();

        $this->assertSame(InterviewStatus::SCHEDULED, $interview->status);
        $this->assertTrue($interview->scheduled_at->equalTo($slot->starts_at));
        $this->assertNotNull($interview->slot_selected_at);
        // Single use: the link stops working the moment it is used.
        $this->assertNull($interview->invitation_token);
        // The scheduler needs something to claim.
        $this->assertCount(1, $interview->attempts);
        $this->assertSame(
            InterviewSlotStatus::SELECTED,
            $slot->fresh()->status
        );
    }

    public function test_the_siblings_are_released_not_deleted(): void
    {
        // What was offered is part of how the booking came about.
        $interview = $this->invited();
        $chosen = $interview->slots->first();

        app(InterviewSchedulingService::class)->confirm($interview, $chosen);

        $this->assertCount(3, $interview->fresh()->slots);
        $this->assertSame(
            2,
            $interview->fresh()->slots->where('status', InterviewSlotStatus::RELEASED)->count()
        );
    }

    public function test_a_used_link_no_longer_works(): void
    {
        $interview = $this->invited();
        $token = $interview->invitation_token;

        app(InterviewSchedulingService::class)->confirm($interview, $interview->slots->first());

        $this->get(route('interview-slots.show', ['token' => $token]))
            ->assertOk()
            ->assertSee(__('interview.link_not_recognised'));
    }

    public function test_an_unknown_token_explains_itself_rather_than_404ing(): void
    {
        // A candidate who never typed the link should not be told they
        // mistyped it.
        $this->get(route('interview-slots.show', ['token' => str_repeat('a', 64)]))
            ->assertOk()
            ->assertSee(__('interview.link_not_recognised'));
    }

    public function test_an_expired_invitation_says_so(): void
    {
        $interview = $this->invited();
        $interview->update(['invitation_expires_at' => now()->subHour()]);

        $this->get(route('interview-slots.show', ['token' => $interview->invitation_token]))
            ->assertOk()
            ->assertSee(__('interview.invitation_expired'));
    }

    // ── the race that actually matters ───────────────────────────────────── #

    public function test_two_candidates_cannot_book_the_same_instant(): void
    {
        /*
         * The single-concurrent-call constraint, enforced at booking time.
         * Both candidates were legitimately offered 10:00; only one may have
         * it, and the loser must be told plainly rather than double-booked
         * into a call the dialer will refuse.
         */
        Notification::fake();
        $project = Project::factory()->create();

        $first = app(InterviewInvitationService::class)
            ->invite($project, $this->candidate('a@example.test'), 90);
        $second = app(InterviewInvitationService::class)
            ->invite(Project::factory()->create(), $this->candidate('b@example.test'), 90);

        $instant = $first->slots->first()->starts_at;

        // Force the second candidate's offer to collide on the same instant.
        $secondSlot = $second->slots->first();
        $secondSlot->update(['starts_at' => $instant, 'ends_at' => $instant->copy()->addMinutes(30)]);

        $scheduling = app(InterviewSchedulingService::class);
        $scheduling->confirm($first, $first->slots->first());

        $this->expectException(\App\Exceptions\Interview\SlotUnavailable::class);
        $scheduling->confirm($second->fresh(), $secondSlot->fresh());
    }

    public function test_losing_the_race_re_renders_the_page_instead_of_erroring(): void
    {
        Notification::fake();
        $project = Project::factory()->create();

        $first = app(InterviewInvitationService::class)
            ->invite($project, $this->candidate('a@example.test'), 90);
        $second = app(InterviewInvitationService::class)
            ->invite(Project::factory()->create(), $this->candidate('b@example.test'), 90);

        $instant = $first->slots->first()->starts_at;
        $secondSlot = $second->slots->first();
        $secondSlot->update(['starts_at' => $instant, 'ends_at' => $instant->copy()->addMinutes(30)]);

        app(InterviewSchedulingService::class)->confirm($first, $first->slots->first());

        $this->from(route('interview-slots.show', ['token' => $second->invitation_token]))
            ->post(
                route('interview-slots.store', ['token' => $second->invitation_token]),
                ['slot_id' => $secondSlot->id]
            )
            ->assertRedirect(route('interview-slots.show', ['token' => $second->invitation_token]))
            ->assertSessionHas('slot_error');

        // And crucially: the loser is still bookable, not left in limbo.
        $this->assertSame(InterviewStatus::SLOT_SELECTION, $second->fresh()->status);
    }

    public function test_a_slot_belonging_to_another_interview_is_refused(): void
    {
        Notification::fake();
        $mine = app(InterviewInvitationService::class)
            ->invite(Project::factory()->create(), $this->candidate('a@example.test'), 90);
        $theirs = app(InterviewInvitationService::class)
            ->invite(Project::factory()->create(), $this->candidate('b@example.test'), 90);

        $this->expectException(\App\Exceptions\Interview\SlotUnavailable::class);

        app(InterviewSchedulingService::class)->confirm($mine, $theirs->slots->first());
    }

    public function test_a_slot_whose_time_has_passed_cannot_be_chosen(): void
    {
        $interview = $this->invited();
        $slot = $interview->slots->first();
        $slot->update([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->subMinutes(30),
        ]);

        $this->expectException(\App\Exceptions\Interview\SlotUnavailable::class);

        app(InterviewSchedulingService::class)->confirm($interview, $slot->fresh());
    }

    // ── expiry housekeeping ──────────────────────────────────────────────── #

    public function test_an_unanswered_invitation_is_closed_and_flagged_for_a_human(): void
    {
        $interview = $this->invited();
        $interview->update(['invitation_expires_at' => now()->subHour()]);

        $this->artisan('interviews:expire-invitations')->assertSuccessful();

        $interview->refresh();

        $this->assertSame(InterviewStatus::RESCHEDULE_REQUIRED, $interview->status);
        $this->assertNull($interview->invitation_token);
        $this->assertSame(3, $interview->slots->where('status', InterviewSlotStatus::EXPIRED)->count());
    }

    public function test_expiry_leaves_a_live_invitation_alone(): void
    {
        $interview = $this->invited();

        $this->artisan('interviews:expire-invitations')->assertSuccessful();

        $this->assertSame(InterviewStatus::SLOT_SELECTION, $interview->fresh()->status);
        $this->assertNotNull($interview->fresh()->invitation_token);
    }

    // ── the invite command ───────────────────────────────────────────────── #

    public function test_the_invite_command_dry_run_sends_nothing(): void
    {
        Notification::fake();
        $project = Project::factory()->create();
        $talent = $this->candidate();

        AiMatch::create([
            'project_id' => $project->id, 'talent_id' => $talent->id,
            'score' => 88, 'payload' => [], 'scorer_version' => '1.0.0',
            'jd_source_hash' => str_repeat('a', 64),
            'resume_source_hash' => str_repeat('b', 64), 'scored_at' => now(),
        ]);

        $this->artisan('interviews:invite', ['project' => $project->id, '--dry-run' => true])
            ->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertSame(0, Interview::count());
    }

    public function test_the_invite_command_invites_the_shortlist(): void
    {
        Notification::fake();
        $project = Project::factory()->create();

        foreach ([91, 75, 40] as $score) {
            $talent = $this->candidate("s{$score}@example.test");
            AiMatch::create([
                'project_id' => $project->id, 'talent_id' => $talent->id,
                'score' => $score, 'payload' => [], 'scorer_version' => '1.0.0',
                'jd_source_hash' => str_repeat('a', 64),
                'resume_source_hash' => str_repeat('b', 64), 'scored_at' => now(),
            ]);
        }

        $this->artisan('interviews:invite', ['project' => $project->id])->assertSuccessful();

        $this->assertSame(2, Interview::count());
        $this->assertSame(6, InterviewSlot::count());
    }

    // ── the email itself ─────────────────────────────────────────────────── #

    public function test_the_invitation_email_actually_renders(): void
    {
        /*
         * `Notification::fake()` records that a notification was sent without
         * ever building it, so every other test in this file would still pass
         * with a broken Blade template — and the first sign of it would be a
         * real candidate's invitation failing to send. This renders it.
         */
        $interview = $this->invited();

        $mail = (new InterviewInvitation($interview))->toMail($interview->talent->user);
        $html = $mail->render();

        $this->assertStringContainsString('Backend Engineer', $mail->subject);

        foreach ($interview->slots as $slot) {
            $this->assertStringContainsString(
                $slot->presentIn('Asia/Tokyo'),
                $html,
                'an offered time is missing from the email body'
            );
        }

        // The link has to be in there, and it has to be absolute.
        $this->assertStringContainsString($interview->invitation_token, $html);
        $this->assertStringContainsString(route('interview-slots.show', [
            'token' => $interview->invitation_token,
        ]), $html);

        // Recording disclosure is a legal requirement, not a courtesy.
        $this->assertStringContainsString(__('interview.mail.recorded_notice'), $html);

        // No unresolved translation keys leaked into a customer-facing email.
        $this->assertStringNotContainsString('interview.mail.', strip_tags($html));
    }

    public function test_the_japanese_invitation_email_renders_too(): void
    {
        $interview = $this->invited();

        app()->setLocale('jp');

        $html = (new InterviewInvitation($interview))->toMail($interview->talent->user)->render();

        $this->assertStringContainsString('一次スクリーニング面接', $html);
        $this->assertStringNotContainsString('interview.mail.', strip_tags($html));

        app()->setLocale('en');
    }

    public function test_the_token_is_never_exposed_through_the_api(): void
    {
        // The recruiter-facing endpoints serialise Interview models; a token
        // in that payload would be a credential to book someone else's slot.
        $interview = $this->invited();

        $this->assertArrayNotHasKey('invitation_token', $interview->toArray());
    }
}
