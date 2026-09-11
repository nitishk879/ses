<?php

namespace Tests\Feature;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewQuestionTypeEnum;
use App\Enums\InterviewStatus;
use App\Jobs\PollInterviewCallJob;
use App\Jobs\StartInterviewAttemptJob;
use App\Models\AiJdParse;
use App\Models\AiMatch;
use App\Models\AiResumeParse;
use App\Models\Interview;
use App\Models\InterviewAttempt;
use App\Models\Project;
use App\Models\Talent;
use App\Models\User;
use App\Services\InterviewAiService;
use App\Services\InterviewAttemptLifeCycleService;
use App\Services\InterviewOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * End-to-end cover for the wiring that did not exist: plan → store → dial →
 * poll → record.
 *
 * The AI service is faked at the HTTP boundary rather than mocked at the class
 * boundary, so these also pin the request bodies SES sends. A field renamed on
 * either side fails here instead of on a live call with a candidate listening.
 */
class InterviewPipelineTest extends TestCase
{
    use RefreshDatabase;

    private const AI = 'http://ai.test';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ai_parser.url', self::AI);
        config()->set('services.ai_parser.secret', str_repeat('s', 48));
        config()->set('services.interview.enabled', true);
        config()->set('services.interview.from_number', '+815012345678');
        config()->set('services.interview.default_phone_region', 'JP');
        config()->set('services.interview.max_attempts', 3);
    }

    // ── fixtures ─────────────────────────────────────────────────────────── #

    private function makeInterview(string $phone = '090-1234-5678'): InterviewAttempt
    {
        $user = User::factory()->create(['phone' => $phone]);
        $project = Project::factory()->create();
        $talent = Talent::factory()->create(['user_id' => $user->id]);

        AiJdParse::create([
            'project_id' => $project->id,
            'parser_version' => '1.0.0',
            'source_hash' => str_repeat('a', 64),
            'payload' => ['project_id' => $project->id, 'title' => 'Backend Engineer'],
            'parsed_at' => now(),
        ]);

        AiResumeParse::create([
            'talent_id' => $talent->id,
            'parser_version' => '1.0.0',
            'source_hash' => str_repeat('b', 64),
            'payload' => ['talent_id' => $talent->id, 'total_experience_months' => 36],
            'parsed_at' => now(),
        ]);

        AiMatch::create([
            'project_id' => $project->id,
            'talent_id' => $talent->id,
            'score' => 72,
            'payload' => [
                'score' => 72,
                'matched_required_skills' => ['Java'],
                'missing_required_skills' => ['AWS'],
            ],
            'scorer_version' => '1.0.0',
            'jd_source_hash' => str_repeat('a', 64),
            'resume_source_hash' => str_repeat('b', 64),
            'scored_at' => now(),
        ]);

        $interview = Interview::create([
            'project_id' => $project->id,
            'talent_id' => $talent->id,
            'status' => InterviewStatus::SCHEDULED,
            'channel' => 'phone',
            'scheduled_at' => now(),
        ]);

        return $interview->attempts()->create([
            'attempt_number' => 1,
            'status' => InterviewAttemptStatus::STARTING,
            'channel' => 'phone',
        ]);
    }

    private function planResponse(): array
    {
        return [
            'project_id' => 1,
            'talent_id' => 1,
            'greeting' => 'Hello, this is the recruitment assistant.',
            'closing' => 'Thank you for your time.',
            'system_prompt' => 'You are a recruitment assistant.',
            'max_call_duration_seconds' => 360,
            'language' => 'english',
            'warnings' => [],
            'budget' => ['total_seconds' => 300, 'max_questions' => 4],
            'questions' => [
                ['order' => 1, 'text' => 'Do you have AWS experience?',
                 'intent' => 'probe_missing_skill', 'subject' => 'AWS', 'expected_seconds' => 48],
                ['order' => 2, 'text' => 'Tell me about a Java project.',
                 'intent' => 'verify_claimed_skill', 'subject' => 'Java', 'expected_seconds' => 48],
                ['order' => 3, 'text' => 'How many years of experience?',
                 'intent' => 'clarify_experience', 'subject' => null, 'expected_seconds' => 48],
                ['order' => 4, 'text' => 'When can you start?',
                 'intent' => 'confirm_availability', 'subject' => null, 'expected_seconds' => 48],
            ],
        ];
    }

    private function orchestrator(): InterviewOrchestrator
    {
        return app(InterviewOrchestrator::class);
    }

    // ── the happy path ───────────────────────────────────────────────────── #

    public function test_start_plans_stores_questions_and_dials(): void
    {
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response([
                'call_config_id' => 'cfg-1', 'twilio_sid' => 'CA999',
            ]),
        ]);

        $attempt = $this->orchestrator()->start($this->makeInterview());

        $this->assertSame('CA999', $attempt->call_sid);
        $this->assertSame('cfg-1', $attempt->call_config_id);
        $this->assertSame(InterviewAttemptStatus::IN_PROGRESS, $attempt->status);
        $this->assertCount(4, $attempt->questions);
        $this->assertSame(InterviewStatus::IN_PROGRESS, $attempt->interview->status);
    }

    public function test_questions_are_stored_before_the_call_is_placed(): void
    {
        // A call placed against questions that were never written down leaves
        // a transcript nobody can interpret.
        $questionsAtDialTime = null;

        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => function () use (&$questionsAtDialTime) {
                $questionsAtDialTime = \App\Models\InterviewQuestion::count();

                return Http::response(['call_config_id' => 'cfg-1', 'twilio_sid' => 'CA999']);
            },
        ]);

        $this->orchestrator()->start($this->makeInterview());

        $this->assertSame(4, $questionsAtDialTime);
    }

    public function test_question_intent_is_mapped_onto_the_ses_type(): void
    {
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response(['twilio_sid' => 'CA999']),
        ]);

        $attempt = $this->orchestrator()->start($this->makeInterview());
        $types = $attempt->questions->pluck('type', 'sequence');

        $this->assertSame(InterviewQuestionTypeEnum::JD_SPECIFIC, $types[1]);
        $this->assertSame(InterviewQuestionTypeEnum::CANDIDATE_SPECIFIC, $types[3]);
        $this->assertSame(InterviewQuestionTypeEnum::CORE, $types[4]);
    }

    public function test_the_plan_request_carries_the_match_gaps(): void
    {
        // The questions worth five minutes are the ones about requirements the
        // CV did not evidence, so the gaps have to reach the planner.
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response(['twilio_sid' => 'CA999']),
        ]);

        $this->orchestrator()->start($this->makeInterview());

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/v1/interview/plan')) {
                return true;
            }

            return $request['missing_required_skills'] === ['AWS']
                && $request['matched_required_skills'] === ['Java'];
        });
    }

    public function test_the_dialled_number_is_normalised_to_e164(): void
    {
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response(['twilio_sid' => 'CA999']),
        ]);

        $this->orchestrator()->start($this->makeInterview('090-1234-5678'));

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/v1/interview/call')) {
                return true;
            }

            return $request['to_phone'] === '+819012345678'
                && $request['from_phone'] === '+815012345678';
        });
    }

    // ── refusing to dial ─────────────────────────────────────────────────── #

    public function test_an_undialable_number_fails_before_any_model_call(): void
    {
        Http::fake();

        $attempt = $this->orchestrator()->start($this->makeInterview('not a phone'));

        $this->assertSame(InterviewAttemptStatus::FAILED, $attempt->status);
        $this->assertStringContainsString('dialable', $attempt->failure_reason);
        Http::assertNothingSent();
    }

    public function test_a_missing_jd_parse_fails_with_a_readable_reason(): void
    {
        Http::fake();

        $attempt = $this->makeInterview();
        AiJdParse::query()->delete();

        $attempt = $this->orchestrator()->start($attempt);

        $this->assertSame(InterviewAttemptStatus::FAILED, $attempt->status);
        $this->assertStringContainsString('job description', $attempt->failure_reason);
        Http::assertNothingSent();
    }

    public function test_an_unconfigured_ai_service_fails_instead_of_retrying_forever(): void
    {
        // 501 means five environment variables are missing over there. That
        // resolves on a deploy, not on a timer, so retrying is pointless.
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response(
                ['detail' => 'missing settings: DENAI_BASE_URL'], 501
            ),
        ]);

        $attempt = $this->orchestrator()->start($this->makeInterview());

        $this->assertSame(InterviewAttemptStatus::FAILED, $attempt->status);
        $this->assertStringContainsString('DENAI_BASE_URL', $attempt->failure_reason);
    }

    public function test_a_busy_service_raises_so_the_job_retries(): void
    {
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response(
                ['detail' => 'all slots in use'], 429, ['Retry-After' => '120']
            ),
        ]);

        $this->expectException(\App\Exceptions\Interview\InterviewAiBusy::class);

        $this->orchestrator()->start($this->makeInterview());
    }

    // ── polling ──────────────────────────────────────────────────────────── #

    private function liveAttempt(): InterviewAttempt
    {
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response(['twilio_sid' => 'CA999']),
        ]);

        return $this->orchestrator()->start($this->makeInterview());
    }

    public function test_a_pending_call_is_not_treated_as_finished(): void
    {
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'pending', 'status' => 'pending', 'completed' => false,
            ]),
        ]);

        $this->assertFalse($this->orchestrator()->poll($attempt));
        $this->assertSame(InterviewAttemptStatus::IN_PROGRESS, $attempt->fresh()->status);
        $this->assertSame(1, $attempt->fresh()->poll_count);
    }

    public function test_a_completed_call_stores_the_transcript_recording_and_real_duration(): void
    {
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'completed',
                'status' => 'completed',
                'completed' => true,
                'duration_seconds' => 247,
                'recording_url' => 'https://api.twilio.com/rec.mp3',
                'transcript' => [
                    ['speaker' => 'BOT', 'text' => 'Do you have AWS experience?'],
                    ['speaker' => 'HUMAN', 'text' => 'Yes, two years running EKS.'],
                ],
            ]),
        ]);

        $this->assertTrue($this->orchestrator()->poll($attempt));

        $attempt->refresh();
        $this->assertSame(InterviewAttemptStatus::COMPLETED, $attempt->status);
        $this->assertSame(247, $attempt->duration_seconds);
        $this->assertSame('https://api.twilio.com/rec.mp3', $attempt->recording_url);
        $this->assertCount(2, $attempt->transcript);
        $this->assertTrue($attempt->hasScreeningTranscript());
        $this->assertSame(InterviewStatus::COMPLETED, $attempt->interview->status);
    }

    public function test_an_overrunning_call_records_its_real_length_not_a_clamped_one(): void
    {
        // The old `min($duration, 300)` did not shorten a call, it only made
        // the record lie about one.
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'completed', 'status' => 'completed', 'completed' => true,
                'duration_seconds' => 431,
                'transcript' => [['speaker' => 'HUMAN', 'text' => 'A real answer here.']],
            ]),
        ]);

        $this->orchestrator()->poll($attempt);
        $attempt->refresh();

        $this->assertSame(431, $attempt->duration_seconds);
        $this->assertSame(131, $attempt->metadata['duration_overrun_seconds']);
    }

    public function test_an_unanswered_call_schedules_a_retry_rather_than_a_bad_score(): void
    {
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'no_answer', 'status' => 'no-answer',
                'completed' => false, 'transcript' => [],
            ]),
        ]);

        $this->orchestrator()->poll($attempt);

        $interview = $attempt->interview->fresh();
        $this->assertSame(InterviewAttemptStatus::NO_ANSWER, $attempt->fresh()->status);
        $this->assertSame(InterviewStatus::SCHEDULED, $interview->status);
        $this->assertCount(2, $interview->attempts);
        $this->assertTrue($interview->scheduled_at->isFuture());
    }

    public function test_a_call_that_ended_with_nothing_said_is_a_retry_not_a_rejection(): void
    {
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'no_transcript', 'status' => 'completed',
                'completed' => false, 'transcript' => [],
            ]),
        ]);

        $this->orchestrator()->poll($attempt);

        $this->assertSame(InterviewAttemptStatus::NO_ANSWER, $attempt->fresh()->status);
        $this->assertSame(InterviewStatus::SCHEDULED, $attempt->interview->fresh()->status);
    }

    public function test_retries_stop_at_the_configured_ceiling(): void
    {
        config()->set('services.interview.max_attempts', 1);
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'no_answer', 'status' => 'no-answer',
                'completed' => false, 'transcript' => [],
            ]),
        ]);

        $this->orchestrator()->poll($attempt);

        $interview = $attempt->interview->fresh();
        $this->assertSame(InterviewStatus::NO_ANSWER, $interview->status);
        $this->assertCount(1, $interview->attempts);
    }

    public function test_a_failed_call_is_distinguished_from_an_unanswered_one(): void
    {
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'failed', 'status' => 'failed',
                'completed' => false, 'transcript' => [],
            ]),
        ]);

        $this->orchestrator()->poll($attempt);

        $this->assertSame(InterviewAttemptStatus::FAILED, $attempt->fresh()->status);
    }

    // ── the scheduler (task 9) ───────────────────────────────────────────── #

    public function test_the_scheduler_starts_an_interview_whose_time_has_come(): void
    {
        Queue::fake();
        $attempt = $this->makeInterview();
        $attempt->update(['status' => InterviewAttemptStatus::PENDING]);

        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        Queue::assertPushed(StartInterviewAttemptJob::class);
        $this->assertSame(InterviewStatus::STARTING, $attempt->interview->fresh()->status);
    }

    public function test_the_scheduler_ignores_an_interview_that_is_not_due(): void
    {
        Queue::fake();
        $attempt = $this->makeInterview();
        $attempt->interview->update(['scheduled_at' => now()->addHour()]);

        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        Queue::assertNothingPushed();
    }

    public function test_an_interview_that_missed_its_slot_is_rescheduled_not_dialled(): void
    {
        // A candidate who agreed to 10:00 will not welcome a call at 14:00.
        Queue::fake();
        $attempt = $this->makeInterview();
        $attempt->interview->update(['scheduled_at' => now()->subHours(4)]);

        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        Queue::assertNothingPushed();
        $this->assertSame(
            InterviewStatus::RESCHEDULE_REQUIRED,
            $attempt->interview->fresh()->status
        );
    }

    public function test_a_second_tick_does_not_dial_the_same_candidate_twice(): void
    {
        Queue::fake();
        $attempt = $this->makeInterview();
        $attempt->update(['status' => InterviewAttemptStatus::PENDING]);

        $this->artisan('interviews:dispatch-due')->assertSuccessful();
        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        Queue::assertPushed(StartInterviewAttemptJob::class, 1);
    }

    public function test_the_scheduler_refuses_to_run_without_an_outbound_number(): void
    {
        Queue::fake();
        config()->set('services.interview.from_number', null);
        $this->makeInterview();

        $this->artisan('interviews:dispatch-due')->assertFailed();

        Queue::assertNothingPushed();
    }

    public function test_the_scheduler_is_a_no_op_when_the_feature_is_off(): void
    {
        Queue::fake();
        config()->set('services.interview.enabled', false);
        $this->makeInterview();

        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        Queue::assertNothingPushed();
    }

    public function test_dry_run_changes_nothing(): void
    {
        Queue::fake();
        $attempt = $this->makeInterview();
        $attempt->update(['status' => InterviewAttemptStatus::PENDING]);

        $this->artisan('interviews:dispatch-due', ['--dry-run' => true])->assertSuccessful();

        Queue::assertNothingPushed();
        $this->assertSame(InterviewStatus::SCHEDULED, $attempt->interview->fresh()->status);
    }

    // ── the lifecycle service ────────────────────────────────────────────── #

    public function test_completing_prefers_the_providers_measured_duration(): void
    {
        // Our own started_at..now() includes queue latency and however long the
        // poller took to notice, neither of which the candidate was on the
        // phone for.
        $attempt = $this->makeInterview();
        $lifecycle = app(InterviewAttemptLifeCycleService::class);

        $attempt = $lifecycle->beginInterview($attempt);
        $attempt->update(['started_at' => now()->subMinutes(30)]);

        $attempt = $lifecycle->complete($attempt->fresh(), 212);

        $this->assertSame(212, $attempt->duration_seconds);
    }

    public function test_the_overrun_threshold_follows_the_configured_window(): void
    {
        // A deployment that plans 7-minute interviews must not have every
        // normal call flagged as a 2-minute overrun. A warning that always
        // fires is one nobody reads, including on the day it matters.
        config()->set('services.interview.duration_seconds', 420);

        $lifecycle = app(InterviewAttemptLifeCycleService::class);
        $attempt = $lifecycle->beginInterview($this->makeInterview());

        $attempt = $lifecycle->complete($attempt, 400);

        $this->assertSame(400, $attempt->duration_seconds);
        $this->assertArrayNotHasKey('duration_overrun_seconds', $attempt->metadata ?? []);
    }

    public function test_a_genuine_overrun_is_still_measured_against_that_window(): void
    {
        config()->set('services.interview.duration_seconds', 420);

        $lifecycle = app(InterviewAttemptLifeCycleService::class);
        $attempt = $lifecycle->beginInterview($this->makeInterview());

        $attempt = $lifecycle->complete($attempt, 500);

        $this->assertSame(80, $attempt->metadata['duration_overrun_seconds']);
        $this->assertSame(420, $attempt->metadata['max_duration_seconds']);
    }

    public function test_an_out_of_order_transition_is_refused(): void
    {
        $attempt = $this->makeInterview();
        $lifecycle = app(InterviewAttemptLifeCycleService::class);

        $this->expectException(\LogicException::class);

        // STARTING -> complete skips IN_PROGRESS.
        $lifecycle->complete($attempt);
    }

    // ── the job ──────────────────────────────────────────────────────────── #

    public function test_the_start_job_queues_a_poll_once_the_call_is_live(): void
    {
        Queue::fake();
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response(['twilio_sid' => 'CA999']),
        ]);

        $attempt = $this->makeInterview();

        (new StartInterviewAttemptJob($attempt->id))->handle(
            $this->orchestrator(),
            app(InterviewAttemptLifeCycleService::class),
        );

        Queue::assertPushed(PollInterviewCallJob::class);
    }

    public function test_the_start_job_will_not_dial_a_cancelled_attempt(): void
    {
        Http::fake();

        $attempt = $this->makeInterview();
        $attempt->update(['status' => InterviewAttemptStatus::CANCELLED]);

        (new StartInterviewAttemptJob($attempt->id))->handle(
            $this->orchestrator(),
            app(InterviewAttemptLifeCycleService::class),
        );

        Http::assertNothingSent();
    }

    // ── the client's own contract ────────────────────────────────────────── #

    public function test_every_ai_request_carries_the_shared_secret(): void
    {
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response($this->planResponse()),
            self::AI.'/v1/interview/call' => Http::response(['twilio_sid' => 'CA999']),
        ]);

        $this->orchestrator()->start($this->makeInterview());

        Http::assertSent(fn ($request) => $request->hasHeader(
            'X-Internal-Secret', str_repeat('s', 48)
        ));
    }

    public function test_calling_status_reports_what_the_ai_service_is_missing(): void
    {
        Http::fake([
            self::AI.'/health' => Http::response([
                'status' => 'ok',
                'interview_calling' => [
                    'configured' => false,
                    'missing_settings' => ['DENAI_BASE_URL', 'TWILIO_AUTH_TOKEN_DX'],
                ],
            ]),
        ]);

        $status = app(InterviewAiService::class)->callingStatus();

        $this->assertFalse($status['configured']);
        $this->assertContains('DENAI_BASE_URL', $status['missing']);
    }

    public function test_an_unreachable_ai_service_reports_as_unconfigured_not_as_a_crash(): void
    {
        Http::fake([self::AI.'/health' => fn () => throw new \Illuminate\Http\Client\ConnectionException('refused')]);

        $status = app(InterviewAiService::class)->callingStatus();

        $this->assertFalse($status['configured']);
    }
}
