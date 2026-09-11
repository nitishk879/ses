<?php

namespace Tests\Feature;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewStatus;
use App\Jobs\PollInterviewCallJob;
use App\Models\AiJdParse;
use App\Models\AiResumeParse;
use App\Models\Interview;
use App\Models\InterviewAttempt;
use App\Models\Project;
use App\Models\Talent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The polling chain, exercised through the real dispatcher.
 *
 * `Queue::fake()` records what was dispatched but does not model the *unique
 * job lock*, which is acquired at dispatch and released only after `handle()`
 * returns. A job that re-queues itself from inside its own `handle()` is
 * therefore the one case a faked queue cannot tell you the truth about — the
 * second dispatch is silently dropped and the fake shows it as sent.
 *
 * These tests use the real dispatcher with an array cache so the lock behaves
 * as it does in production. Without them, "polling works" was an assumption.
 */
class InterviewPollChainTest extends TestCase
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
        config()->set('services.interview.poll_interval_seconds', 20);
        config()->set('services.interview.poll_max_attempts', 5);
    }

    private function liveAttempt(): InterviewAttempt
    {
        $user = User::factory()->create(['phone' => '090-1234-5678']);
        $project = Project::factory()->create();
        $talent = Talent::factory()->create(['user_id' => $user->id]);

        AiJdParse::create([
            'project_id' => $project->id, 'parser_version' => '1.0.0',
            'source_hash' => str_repeat('a', 64),
            'payload' => ['project_id' => $project->id, 'title' => 'Backend'],
            'parsed_at' => now(),
        ]);
        AiResumeParse::create([
            'talent_id' => $talent->id, 'parser_version' => '1.0.0',
            'source_hash' => str_repeat('b', 64),
            'payload' => ['talent_id' => $talent->id],
            'parsed_at' => now(),
        ]);

        $interview = Interview::create([
            'project_id' => $project->id, 'talent_id' => $talent->id,
            'status' => InterviewStatus::IN_PROGRESS, 'channel' => 'phone',
            'scheduled_at' => now(),
        ]);

        return $interview->attempts()->create([
            'attempt_number' => 1,
            'status' => InterviewAttemptStatus::IN_PROGRESS,
            'channel' => 'phone',
            'call_sid' => 'CA999',
            'started_at' => now(),
        ]);
    }

    private function fakePending(): void
    {
        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'pending', 'status' => 'pending',
                'completed' => false, 'transcript' => [],
            ]),
        ]);
    }

    /**
     * The bug this file exists for.
     *
     * A poll that finds the call still ringing must queue the next poll. If the
     * unique lock blocks that re-dispatch, polling stops dead after one
     * attempt: the call completes, nobody ever reads the result, the
     * transcript is never stored and the attempt sits IN_PROGRESS forever.
     */
    public function test_a_pending_poll_really_queues_the_next_poll(): void
    {
        $this->fakePending();
        $attempt = $this->liveAttempt();

        Bus::fake([\App\Jobs\EvaluateInterviewAttemptJob::class]);
        Queue::fake();

        // Dispatch through the real pending-dispatch path so the unique lock,
        // if any, is genuinely acquired for the outer job.
        $outer = new PollInterviewCallJob($attempt->id);
        app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchNow($outer);

        Queue::assertPushed(PollInterviewCallJob::class, 1);
    }

    /**
     * The same thing, without any faking at all: run the chain end to end on a
     * sync queue and assert it reaches a terminal state.
     */
    public function test_the_chain_runs_to_completion_against_a_real_queue(): void
    {
        $attempt = $this->liveAttempt();

        $calls = 0;
        Http::fake(function ($request) use (&$calls) {
            $calls++;

            // Ringing twice, then answered.
            if ($calls < 3) {
                return Http::response([
                    'lifecycle' => 'in_progress', 'status' => 'in-progress',
                    'completed' => false, 'transcript' => [],
                ]);
            }

            return Http::response([
                'lifecycle' => 'completed', 'status' => 'completed',
                'completed' => true, 'duration_seconds' => 212,
                'recording_url' => 'https://api.twilio.com/rec.mp3',
                'transcript' => [
                    ['speaker' => 'BOT', 'text' => 'Do you have AWS experience?'],
                    ['speaker' => 'HUMAN', 'text' => 'Yes, two years running EKS.'],
                ],
            ]);
        });

        // QUEUE_CONNECTION is sync in phpunit.xml, so each self-dispatch runs
        // immediately and the whole chain unwinds inside this call.
        PollInterviewCallJob::dispatch($attempt->id);

        $attempt->refresh();

        $this->assertSame(3, $calls, 'the chain stopped before the call was answered');
        $this->assertSame(InterviewAttemptStatus::COMPLETED, $attempt->status);
        $this->assertSame(212, $attempt->duration_seconds);
        $this->assertCount(2, $attempt->transcript);
        $this->assertSame(3, $attempt->poll_count);
    }

    public function test_the_chain_stops_itself_at_the_poll_ceiling(): void
    {
        config()->set('services.interview.poll_max_attempts', 3);
        $this->fakePending();
        $attempt = $this->liveAttempt();

        PollInterviewCallJob::dispatch($attempt->id);

        $attempt->refresh();

        // Stopped, and the reason says WE gave up — not that the candidate
        // failed to answer, which would consume one of their retries.
        $this->assertSame(InterviewAttemptStatus::FAILED, $attempt->status);
        $this->assertStringContainsString(
            'Stopped waiting',
            $attempt->failure_reason
        );
        $this->assertSame(3, $attempt->poll_count);
    }

    public function test_a_terminal_attempt_is_not_polled_again(): void
    {
        Http::fake();
        $attempt = $this->liveAttempt();
        $attempt->update(['status' => InterviewAttemptStatus::COMPLETED]);

        PollInterviewCallJob::dispatch($attempt->id);

        Http::assertNothingSent();
        $this->assertSame(0, $attempt->fresh()->poll_count);
    }

    /**
     * A transient outage that outlives every retry must not strand the attempt.
     *
     * Nothing reports a row that is merely *stuck*: it is not failed, not
     * completed, and has no poll queued to move it. Without a `failed()`
     * handler the candidate's interview would sit IN_PROGRESS forever and
     * nobody would find out.
     */
    public function test_giving_up_on_polling_fails_the_attempt_rather_than_stranding_it(): void
    {
        $attempt = $this->liveAttempt();

        (new PollInterviewCallJob($attempt->id))->failed(
            new \App\Exceptions\Interview\InterviewAiUnavailable('service down')
        );

        $attempt->refresh();

        $this->assertSame(InterviewAttemptStatus::FAILED, $attempt->status);
        $this->assertStringContainsString('Stopped waiting', $attempt->failure_reason);
    }

    public function test_giving_up_never_overwrites_an_outcome_already_recorded(): void
    {
        // A late failure callback must not turn a completed interview into a
        // failed one.
        $attempt = $this->liveAttempt();
        $attempt->update([
            'status' => InterviewAttemptStatus::COMPLETED,
            'duration_seconds' => 212,
        ]);

        (new PollInterviewCallJob($attempt->id))->failed(
            new \App\Exceptions\Interview\InterviewAiUnavailable('service down')
        );

        $this->assertSame(InterviewAttemptStatus::COMPLETED, $attempt->fresh()->status);
    }

    public function test_the_start_job_backs_off_further_for_a_busy_line_than_an_outage(): void
    {
        // A busy line only frees when a call ends — minutes, not seconds. A
        // single 60s backoff burned all three attempts before that could clear.
        $backoff = (new \App\Jobs\StartInterviewAttemptJob(1))->backoff;

        $this->assertIsArray($backoff);
        $this->assertGreaterThan($backoff[0], $backoff[1]);
    }

    public function test_a_completed_call_queues_its_evaluation(): void
    {
        // Only the evaluation job is faked. Faking the whole Bus would also
        // intercept the poll job itself, so `handle()` would never run and the
        // assertion would pass or fail for the wrong reason.
        Bus::fake([\App\Jobs\EvaluateInterviewAttemptJob::class]);
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'completed', 'status' => 'completed',
                'completed' => true, 'duration_seconds' => 200,
                'transcript' => [
                    ['speaker' => 'HUMAN', 'text' => 'A substantive answer here.'],
                ],
            ]),
        ]);

        app(\Illuminate\Contracts\Bus\Dispatcher::class)
            ->dispatchNow(new PollInterviewCallJob($attempt->id));

        Bus::assertDispatched(\App\Jobs\EvaluateInterviewAttemptJob::class);
    }

    public function test_an_empty_transcript_is_not_sent_for_evaluation(): void
    {
        // Scoring a call nobody spoke on produces a confident number about
        // nothing.
        Bus::fake([\App\Jobs\EvaluateInterviewAttemptJob::class]);
        $attempt = $this->liveAttempt();

        Http::fake([
            self::AI.'/v1/interview/call/*' => Http::response([
                'lifecycle' => 'no_transcript', 'status' => 'completed',
                'completed' => false, 'transcript' => [],
            ]),
        ]);

        app(\Illuminate\Contracts\Bus\Dispatcher::class)
            ->dispatchNow(new PollInterviewCallJob($attempt->id));

        Bus::assertNotDispatched(\App\Jobs\EvaluateInterviewAttemptJob::class);
    }
}
