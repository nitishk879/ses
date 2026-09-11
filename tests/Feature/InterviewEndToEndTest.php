<?php

namespace Tests\Feature;

use App\Contracts\InterviewEvaluationProvider;
use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewStatus;
use App\Jobs\StartInterviewAttemptJob;
use App\Models\AiJdParse;
use App\Models\AiMatch;
use App\Models\AiResumeParse;
use App\Models\Interview;
use App\Models\InterviewAttempt;
use App\Models\InterviewEvaluation;
use App\Models\Project;
use App\Models\Talent;
use App\Models\User;
use App\Services\Ai\SesAiInterviewEvaluationProvider;
use App\Services\InterviewAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The whole thing, start to evaluated, with nothing faked but HTTP.
 *
 * The per-stage tests each assert one hop. This asserts they compose: the
 * scheduler's job dials, the dial queues a poll, the poll chain runs to a
 * transcript, the transcript is evaluated, and a row lands in
 * `interview_evaluations`. Every seam between those is somewhere a field name
 * can drift, and a drift there is invisible until a real candidate is on the
 * phone.
 *
 * QUEUE_CONNECTION is `sync` under phpunit, so each dispatch runs inline and
 * the entire chain unwinds inside one call.
 */
class InterviewEndToEndTest extends TestCase
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
        config()->set('services.interview.poll_max_attempts', 10);
    }

    private function scheduledInterview(): InterviewAttempt
    {
        // `name` is an accessor over firstname+lastname, not a column — so it
        // is set through the real fields, the way the application does.
        $user = User::factory()->create([
            'phone' => '090-1234-5678',
            'firstname' => 'Taro',
            'lastname' => 'Tanaka',
        ]);
        $project = Project::factory()->create(['title' => 'Backend Engineer']);
        $talent = Talent::factory()->create(['user_id' => $user->id]);

        AiJdParse::create([
            'project_id' => $project->id, 'parser_version' => '1.0.0',
            'source_hash' => str_repeat('a', 64),
            'payload' => [
                'project_id' => $project->id,
                'title' => 'Backend Engineer',
                'required_skills' => [
                    ['raw' => 'Java', 'evidence' => 'Java 8+', 'kind' => 'required', 'source' => 'text'],
                ],
                'meta' => ['parser_version' => '1.0.0', 'source_hash' => 'a', 'model' => 'm'],
            ],
            'parsed_at' => now(),
        ]);

        AiResumeParse::create([
            'talent_id' => $talent->id, 'parser_version' => '1.0.0',
            'source_hash' => str_repeat('b', 64),
            'payload' => ['talent_id' => $talent->id, 'total_experience_months' => 36],
            'parsed_at' => now(),
        ]);

        AiMatch::create([
            'project_id' => $project->id, 'talent_id' => $talent->id, 'score' => 72,
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
            'project_id' => $project->id, 'talent_id' => $talent->id,
            'status' => InterviewStatus::SCHEDULED, 'channel' => 'phone',
            'scheduled_at' => now(),
        ]);

        return $interview->attempts()->create([
            'attempt_number' => 1,
            'status' => InterviewAttemptStatus::PENDING,
            'channel' => 'phone',
        ]);
    }

    private function fakeWholeService(int $ringingPolls = 1): void
    {
        $polls = 0;

        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response([
                'project_id' => 1, 'talent_id' => 1,
                'greeting' => 'Hello Tanaka, this is the recruitment assistant.',
                'closing' => 'Thank you for your time.',
                'system_prompt' => 'You are a recruitment assistant.',
                'max_call_duration_seconds' => 360, 'language' => 'english',
                'warnings' => [], 'budget' => ['total_seconds' => 300, 'max_questions' => 2],
                'questions' => [
                    ['order' => 1, 'text' => 'Do you have AWS experience?',
                     'intent' => 'probe_missing_skill', 'subject' => 'AWS', 'expected_seconds' => 48],
                    ['order' => 2, 'text' => 'When can you start?',
                     'intent' => 'confirm_availability', 'subject' => null, 'expected_seconds' => 48],
                ],
            ]),

            self::AI.'/v1/interview/call' => Http::response([
                'call_config_id' => 'cfg-1', 'twilio_sid' => 'CA999',
            ]),

            self::AI.'/v1/interview/call/*' => function () use (&$polls, $ringingPolls) {
                $polls++;

                if ($polls <= $ringingPolls) {
                    return Http::response([
                        'lifecycle' => 'in_progress', 'status' => 'in-progress',
                        'completed' => false, 'transcript' => [],
                    ]);
                }

                return Http::response([
                    'lifecycle' => 'completed', 'status' => 'completed', 'completed' => true,
                    'duration_seconds' => 231,
                    'recording_url' => 'https://api.twilio.com/rec.mp3',
                    'transcript' => [
                        ['speaker' => 'BOT', 'text' => 'Do you have AWS experience?'],
                        ['speaker' => 'HUMAN', 'text' => 'Yes, I ran EKS in production for two years.'],
                        ['speaker' => 'BOT', 'text' => 'When can you start?'],
                        ['speaker' => 'HUMAN', 'text' => 'I could start from the first of next month.'],
                    ],
                ]);
            },

            self::AI.'/v1/interview/evaluate' => Http::response([
                'project_id' => 1, 'talent_id' => 1,
                'technical_fit' => 78.0, 'jd_fit' => 71.0, 'communication' => 80.0,
                // Deliberately wrong. The `interview_evaluations` migration
                // says the final score is "calculated by the backend, not
                // trusted from AI/user input" — this is what proves it.
                // 78*.40 + 71*.35 + 80*.25 = 76.05, not this.
                'overall_score' => 99.9,
                'summary' => 'Credible backend candidate with hands-on AWS.',
                'strengths' => ['Production Kubernetes'],
                'gaps' => ['No evidence of team leadership'],
                'evidence' => [[
                    'criterion' => 'technical_fit', 'question_order' => 1,
                    'observation' => 'Ran production EKS.',
                    'quote' => 'I ran EKS in production for two years',
                ]],
                'recommendation' => 'recommended',
                'questions_asked' => 2, 'questions_answered' => 2, 'coverage' => 1.0,
                'provider' => 'ses-ai-service', 'model' => 'llama-3.3-70b',
                'prompt_version' => '1.0.0', 'warnings' => [],
            ]),
        ]);
    }

    /**
     * Scheduler tick → dial → poll chain → transcript → evaluation row.
     */
    public function test_a_scheduled_interview_runs_all_the_way_to_an_evaluation(): void
    {
        // Force the real provider; the container binds the mock under testing
        // so that other tests do not depend on a language model.
        $this->app->bind(
            InterviewEvaluationProvider::class,
            SesAiInterviewEvaluationProvider::class
        );

        $this->fakeWholeService(ringingPolls: 2);
        $attempt = $this->scheduledInterview();

        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        $attempt->refresh();

        // Dialled.
        $this->assertSame('CA999', $attempt->call_sid);
        $this->assertCount(2, $attempt->questions);

        // Polled to completion, artifacts stored (task 13).
        $this->assertSame(InterviewAttemptStatus::COMPLETED, $attempt->status);
        $this->assertSame(231, $attempt->duration_seconds);
        $this->assertSame('https://api.twilio.com/rec.mp3', $attempt->recording_url);
        $this->assertCount(4, $attempt->transcript);
        $this->assertSame(3, $attempt->poll_count);

        // Evaluated against the JD (tasks 14/15).
        $evaluation = InterviewEvaluation::where('interview_attempt_id', $attempt->id)->first();
        $this->assertNotNull($evaluation, 'no evaluation row was written');
        $this->assertEquals(78.0, (float) $evaluation->technical_fit);
        $this->assertSame('recommended', $evaluation->recommendation);
        // Recomputed by the backend from the three criteria, never trusted
        // from the provider — which sent 99.9 for this very reason.
        $this->assertEquals(76.05, (float) $evaluation->overall_score);
        // Coverage is carried through so a score can be read in context.
        // assertEquals, not assertSame: json_encode drops the zero fraction of
        // 1.0, so a whole-number coverage arrives as an int.
        $this->assertEquals(1, $evaluation->metadata['coverage']);
        $this->assertSame(2, $evaluation->metadata['questions_answered']);

        $this->assertSame(InterviewStatus::COMPLETED, $attempt->interview->status);
    }

    public function test_the_evaluation_request_carries_the_questions_with_their_intents(): void
    {
        $this->app->bind(
            InterviewEvaluationProvider::class,
            SesAiInterviewEvaluationProvider::class
        );

        $this->fakeWholeService(ringingPolls: 0);
        $this->scheduledInterview();

        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/v1/interview/evaluate')) {
                return true;
            }

            $questions = $request['questions'];

            return count($questions) === 2
                && $questions[0]['intent'] === 'probe_missing_skill'
                && $questions[0]['subject'] === 'AWS'
                && $questions[0]['order'] === 1
                && count($request['transcript']) === 4
                && $request['duration_seconds'] === 231;
        });
    }

    /**
     * The whole chain, for a candidate who never picks up.
     */
    public function test_an_unanswered_interview_ends_with_a_retry_scheduled(): void
    {
        Http::fake([
            self::AI.'/v1/interview/plan' => Http::response([
                'project_id' => 1, 'talent_id' => 1, 'greeting' => 'Hello.',
                'closing' => 'Bye.', 'system_prompt' => 'p',
                'max_call_duration_seconds' => 360, 'language' => 'english',
                'warnings' => [], 'budget' => [],
                'questions' => [['order' => 1, 'text' => 'Q?', 'intent' => 'confirm_availability',
                                 'subject' => null, 'expected_seconds' => 48]],
            ]),
            self::AI.'/v1/interview/call' => Http::response(['twilio_sid' => 'CA999']),
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'no_answer', 'status' => 'no-answer',
                'completed' => false, 'transcript' => [],
            ]),
        ]);

        $attempt = $this->scheduledInterview();

        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        $interview = $attempt->interview->fresh();

        $this->assertSame(InterviewAttemptStatus::NO_ANSWER, $attempt->fresh()->status);
        $this->assertSame(InterviewStatus::SCHEDULED, $interview->status);
        $this->assertCount(2, $interview->attempts, 'a retry attempt should exist');
        $this->assertTrue($interview->scheduled_at->isFuture());

        // And nothing was evaluated — there is nothing to evaluate.
        $this->assertSame(0, InterviewEvaluation::count());
    }

    /**
     * The retry, once its time comes round, is picked up by the same tick.
     */
    public function test_the_scheduled_retry_is_dialled_when_its_slot_arrives(): void
    {
        $this->fakeWholeService(ringingPolls: 0);
        $attempt = $this->scheduledInterview();
        $interview = $attempt->interview;

        // Simulate a retry that was scheduled an hour ago and is now due.
        $attempt->update(['status' => InterviewAttemptStatus::NO_ANSWER]);
        $retry = $interview->attempts()->create([
            'attempt_number' => 2,
            'status' => InterviewAttemptStatus::PENDING,
            'channel' => 'phone',
        ]);
        $interview->update([
            'status' => InterviewStatus::SCHEDULED,
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('interviews:dispatch-due')->assertSuccessful();

        $retry->refresh();
        $this->assertSame('CA999', $retry->call_sid);
        $this->assertSame(InterviewAttemptStatus::COMPLETED, $retry->status);
    }

    public function test_a_replanned_attempt_does_not_duplicate_its_questions(): void
    {
        // The start job can legitimately run twice for one attempt: plan
        // succeeds, the dial fails transiently, the job retries. The second
        // run must not append a second set of questions — `sequence` is unique
        // per attempt and the insert would collide.
        $this->fakeWholeService(ringingPolls: 0);
        $attempt = $this->scheduledInterview();
        $attempt->update(['status' => InterviewAttemptStatus::STARTING]);

        $orchestrator = app(\App\Services\InterviewOrchestrator::class);
        $orchestrator->start($attempt);

        $attempt->refresh();
        $attempt->update(['status' => InterviewAttemptStatus::STARTING]);

        $orchestrator->start($attempt);

        $this->assertCount(2, $attempt->fresh()->questions);
    }

    public function test_a_partially_asked_interview_is_not_replanned_over(): void
    {
        // If some questions have already been asked, re-planning would either
        // collide on `sequence` or silently rewrite what a candidate was
        // actually asked. Neither is acceptable, so the attempt fails loudly.
        $this->fakeWholeService(ringingPolls: 0);
        $attempt = $this->scheduledInterview();
        $attempt->update(['status' => InterviewAttemptStatus::STARTING]);

        $orchestrator = app(\App\Services\InterviewOrchestrator::class);
        $orchestrator->start($attempt);

        $attempt->refresh();
        $attempt->questions()->first()->update(['asked_at' => now()]);
        $attempt->update(['status' => InterviewAttemptStatus::STARTING]);

        $result = $orchestrator->start($attempt);

        $this->assertSame(InterviewAttemptStatus::FAILED, $result->status);
        $this->assertCount(2, $attempt->fresh()->questions);
    }

    /**
     * A match payload in the other shape the scorer can produce.
     */
    public function test_match_skills_are_read_whether_they_are_strings_or_objects(): void
    {
        $service = app(InterviewAiService::class);
        $method = new \ReflectionMethod($service, 'skillNames');

        $objects = $method->invoke($service, [
            'missing_required_skills' => [
                ['raw' => 'AWS', 'canonical' => 'aws'],
                ['canonical' => 'kafka'],
                ['name' => 'Go'],
            ],
        ], 'missing_required_skills');

        $this->assertSame(['AWS', 'kafka', 'Go'], $objects);

        $strings = $method->invoke($service, [
            'missing_required_skills' => ['AWS', ' AWS ', '', 'Kafka'],
        ], 'missing_required_skills');

        $this->assertSame(['AWS', 'Kafka'], $strings);

        // A scorer that stopped emitting the key must not crash the interview.
        $this->assertSame([], $method->invoke($service, [], 'missing_required_skills'));
        $this->assertSame([], $method->invoke($service, ['missing_required_skills' => null], 'missing_required_skills'));
    }
}
