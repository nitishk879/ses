<?php

namespace App\Services;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewQuestionTypeEnum;
use App\Enums\InterviewStatus;
use App\Exceptions\Interview\InterviewAiBusy;
use App\Exceptions\Interview\InterviewAiUnavailable;
use App\Exceptions\Interview\InterviewCallingNotConfigured;
use App\Models\AiJdParse;
use App\Models\AiMatch;
use App\Models\AiResumeParse;
use App\Models\Interview;
use App\Models\InterviewAttempt;
use App\Models\InterviewQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * The missing wiring between SES and the AI service.
 *
 * Everything either side needed already existed — Laravel had the schema and
 * the lifecycle, the Python service had planning, dialling and scoring — and
 * nothing connected them, so tasks 9 through 12 had storage but no behaviour.
 * This is that connection, and it is one class on purpose: the sequence
 * plan → persist → dial → poll → persist has to be readable in one place, and
 * the ordering constraints inside it are not obvious enough to be spread
 * across four controllers.
 *
 * Two rules run through it:
 *
 * **Nothing is dialled that has not first been written down.** Questions are
 * persisted before the call is placed, so a crash mid-call leaves a record of
 * what the candidate was asked rather than an attempt nobody can reconstruct.
 *
 * **A call that did not happen is never recorded as one that went badly.** No
 * answer, a failed dial and an empty transcript are distinct outcomes with
 * distinct next steps, and only one of them is a judgement about a candidate.
 */
class InterviewOrchestrator
{
    public function __construct(
        private readonly InterviewAiService $ai,
        private readonly InterviewAttemptLifeCycleService $lifecycle,
    ) {
    }

    /**
     * Plan an interview, store its questions, and dial the candidate.
     *
     * Returns the attempt with `call_sid` set. The call is live when this
     * returns; its outcome arrives later, via {@see poll()}.
     */
    public function start(InterviewAttempt $attempt): InterviewAttempt
    {
        $attempt->loadMissing([
            'interview.project',
            'interview.talent.user',
        ]);

        $interview = $attempt->interview;
        $project = $interview->project;
        $talent = $interview->talent;

        if (! $project || ! $talent) {
            return $this->failAttempt($attempt, 'Interview is missing its project or talent.');
        }

        // Checked before anything else: an un-dialable number is the most
        // common reason an interview cannot happen, it costs nothing to
        // detect, and detecting it here means no LLM call is spent planning an
        // interview that can never be placed.
        $phone = $talent->interviewPhone();

        if ($phone === null) {
            return $this->failAttempt(
                $attempt,
                __('interview.phone_not_dialable', ['talent' => $talent->id])
            );
        }

        /*
         * A re-run over an interview that already started asking is refused.
         *
         * `start()` can legitimately run twice for one attempt — plan
         * succeeds, the dial fails transiently, the job retries — and
         * re-planning is correct as long as nothing has been asked yet.
         * Once a question carries `asked_at` the options are both wrong:
         * re-planning collides on the (attempt, sequence) unique index, and
         * working around that collision would rewrite the record of what a
         * candidate was actually asked. A new attempt is the right shape for
         * a second try.
         */
        if ($attempt->questions()->whereNotNull('asked_at')->exists()) {
            return $this->failAttempt($attempt, __('interview.already_in_progress'));
        }

        $parses = $this->requireParses($attempt, $project->id, $talent->id);

        if ($parses === null) {
            return $attempt->fresh();
        }

        [$parsedJd, $parsedResume, $match] = $parses;

        try {
            $plan = $this->ai->plan($project, $talent, $parsedJd, $parsedResume, $match);
        } catch (InterviewAiUnavailable $e) {
            // Transient. Left PENDING so the job's own retry picks it up
            // rather than burning one of the candidate's call attempts.
            throw $e;
        } catch (RuntimeException $e) {
            return $this->failAttempt($attempt, 'Could not plan the interview: '.$e->getMessage());
        }

        // Persisted before the dial. A call placed against questions that were
        // never written down produces a transcript nobody can interpret.
        $this->storePlan($attempt, $plan);

        try {
            $handle = $this->ai->call(
                $plan,
                $phone,
                [
                    'ses_interview_id' => (string) $interview->id,
                    'ses_attempt_id' => (string) $attempt->id,
                    'ses_project_id' => (string) $project->id,
                    'ses_talent_id' => (string) $talent->id,
                ],
                // Read now rather than at plan time, so a bot reassigned or
                // reworded on the dashboard applies to this call.
                $project->interview_agent_id,
            );
        } catch (InterviewCallingNotConfigured $e) {
            // Not retryable: this resolves on a deploy, not on a timer. Fail
            // loudly with the service's own message, which names the missing
            // settings.
            return $this->failAttempt($attempt, 'Interview calling is not configured: '.$e->getMessage());
        } catch (InterviewAiBusy|InterviewAiUnavailable $e) {
            // Retryable, and the plan is already saved so the retry will not
            // re-ask the model for it.
            throw $e;
        } catch (RuntimeException $e) {
            return $this->failAttempt($attempt, 'Could not place the call: '.$e->getMessage());
        }

        $attempt->forceFill([
            'call_sid' => $handle['twilio_sid'] ?? null,
            'call_config_id' => $handle['call_config_id'] ?? null,
            'channel' => $attempt->channel ?: 'phone',
        ])->save();

        // STARTING -> IN_PROGRESS. The candidate's phone is ringing, so the
        // clock starts here rather than when the job was queued.
        $attempt = $this->lifecycle->beginInterview($attempt);

        $interview->update([
            'status' => InterviewStatus::IN_PROGRESS,
            'started_at' => $interview->started_at ?? now(),
            'channel' => $interview->channel ?: 'phone',
            'provider_reference' => $handle['twilio_sid'] ?? $interview->provider_reference,
        ]);

        // The number is never logged, only that one was found.
        Log::info('interview.call_placed', [
            'interview_id' => $interview->id,
            'attempt_id' => $attempt->id,
            'call_sid' => $handle['twilio_sid'] ?? null,
            'to' => $phone->masked(),
        ]);

        return $attempt->fresh();
    }

    /**
     * Read a placed call's outcome and record it.
     *
     * Returns true when the call has reached a terminal state and there is
     * nothing left to poll.
     */
    public function poll(InterviewAttempt $attempt): bool
    {
        if (blank($attempt->call_sid)) {
            $this->failAttempt($attempt, 'No call SID to poll; the dial never completed.');

            return true;
        }

        $result = $this->ai->callResult($attempt->call_sid);
        $lifecycle = $result['lifecycle'] ?? 'pending';

        $attempt->forceFill([
            'poll_count' => (int) $attempt->poll_count + 1,
            'last_polled_at' => now(),
        ])->save();

        if (in_array($lifecycle, ['pending', 'in_progress'], true)) {
            return false;
        }

        // Store the artifacts before touching the lifecycle, so a failure in
        // the state machine cannot lose a transcript we already have.
        $attempt->forceFill([
            'recording_url' => $result['recording_url'] ?? null,
            'transcript' => $result['transcript'] ?? [],
            'call_outcome' => $lifecycle,
        ])->save();

        $duration = isset($result['duration_seconds'])
            ? (int) $result['duration_seconds']
            : null;

        match ($lifecycle) {
            'completed' => $this->recordCompleted($attempt, $duration),
            'no_answer' => $this->recordNoAnswer($attempt),
            'failed' => $this->failAttempt($attempt, __('interview.call_failed')),
            // Ended, but nothing was said. Not a screening, and not the
            // candidate's fault — treated as a retry, not as a bad result.
            'no_transcript' => $this->recordNoAnswer($attempt, __('interview.no_transcript')),
            default => $this->failAttempt($attempt, 'Unknown call outcome: '.$lifecycle),
        };

        return true;
    }

    /**
     * Whether another call to this candidate is warranted.
     *
     * Counts calls placed, not job retries. A candidate notices the former.
     */
    public function shouldRetry(Interview $interview): bool
    {
        $max = (int) config('services.interview.max_attempts', 3);

        return $interview->attempts()->count() < $max;
    }

    /**
     * Queue another attempt for an interview that went unanswered.
     */
    public function scheduleRetry(Interview $interview): ?InterviewAttempt
    {
        if (! $this->shouldRetry($interview)) {
            $interview->update([
                'status' => InterviewStatus::NO_ANSWER,
                'failure_reason' => __('interview.max_attempts_reached'),
            ]);

            return null;
        }

        $delay = (int) config('services.interview.retry_delay_minutes', 60);

        $interview->update([
            'status' => InterviewStatus::SCHEDULED,
            'scheduled_at' => now()->addMinutes($delay),
        ]);

        return DB::transaction(function () use ($interview) {
            $next = ($interview->attempts()->max('attempt_number') ?? 0) + 1;

            return $interview->attempts()->create([
                'attempt_number' => $next,
                'status' => InterviewAttemptStatus::PENDING,
                'channel' => $interview->channel,
            ]);
        });
    }

    // ── internals ───────────────────────────────────────────────────────── #

    /**
     * Write the planned questions, replacing anything a previous try left.
     *
     * @param  array<string, mixed>  $plan
     */
    private function storePlan(InterviewAttempt $attempt, array $plan): void
    {
        DB::transaction(function () use ($attempt, $plan) {
            // A retried start must not append a second set of questions to the
            // same attempt; `sequence` is unique per attempt and would collide.
            $attempt->questions()->whereNull('asked_at')->delete();

            foreach ($plan['questions'] ?? [] as $question) {
                InterviewQuestion::create([
                    'interview_attempt_id' => $attempt->id,
                    'sequence' => (int) $question['order'],
                    'type' => $this->questionType($question['intent'] ?? ''),
                    'skill_area' => $question['subject'] ?? null,
                    'question_text' => (string) $question['text'],
                    'source' => 'ai',
                    'metadata' => [
                        'intent' => $question['intent'] ?? null,
                        'expected_seconds' => $question['expected_seconds'] ?? null,
                    ],
                ]);
            }

            $attempt->forceFill([
                'metadata' => array_merge($attempt->metadata ?? [], [
                    'plan' => [
                        'greeting' => $plan['greeting'] ?? null,
                        'closing' => $plan['closing'] ?? null,
                        'budget' => $plan['budget'] ?? null,
                        'language' => $plan['language'] ?? null,
                        'max_call_duration_seconds' => $plan['max_call_duration_seconds'] ?? null,
                        'warnings' => $plan['warnings'] ?? [],
                    ],
                ]),
            ])->save();
        });

        foreach ($plan['warnings'] ?? [] as $warning) {
            // Worth a log line each: these are how "the questions fell back to
            // templates" or "a question was blocked" becomes visible.
            Log::warning('interview.plan_warning', [
                'attempt_id' => $attempt->id,
                'warning' => $warning,
            ]);
        }
    }

    /**
     * Map the AI service's question intent onto the SES question type.
     *
     * The two vocabularies were designed independently; translating once here
     * beats storing a foreign enum in our column.
     */
    private function questionType(string $intent): InterviewQuestionTypeEnum
    {
        return match ($intent) {
            'probe_missing_skill', 'verify_claimed_skill' => InterviewQuestionTypeEnum::JD_SPECIFIC,
            'clarify_experience' => InterviewQuestionTypeEnum::CANDIDATE_SPECIFIC,
            default => InterviewQuestionTypeEnum::CORE,
        };
    }

    /**
     * The stored parses and match this interview needs, or null after failing
     * the attempt with a reason.
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: array<string, mixed>}|null
     */
    private function requireParses(InterviewAttempt $attempt, int $projectId, int $talentId): ?array
    {
        $jd = AiJdParse::firstWhere('project_id', $projectId);

        if (! $jd) {
            $this->failAttempt($attempt, __('interview.jd_not_parsed'));

            return null;
        }

        $resume = AiResumeParse::firstWhere('talent_id', $talentId);

        if (! $resume) {
            $this->failAttempt($attempt, __('interview.resume_not_parsed'));

            return null;
        }

        // The match is optional: without it the interview still runs, it just
        // asks generic questions instead of ones aimed at this candidate's
        // gaps. A missing score is a worse interview, not a reason to refuse
        // one the candidate is expecting.
        $match = AiMatch::where('project_id', $projectId)
            ->where('talent_id', $talentId)
            ->first();

        return [
            $jd->payload ?? [],
            $resume->payload ?? [],
            $match?->payload ?? [],
        ];
    }

    private function recordCompleted(InterviewAttempt $attempt, ?int $duration): void
    {
        // The state machine refuses a transition out of order, and a call can
        // be polled to completion from STARTING if the begin step was missed.
        if ($attempt->status !== InterviewAttemptStatus::IN_PROGRESS) {
            $attempt = $this->lifecycle->beginInterview($attempt->fresh());
        }

        $attempt = $this->lifecycle->complete($attempt, $duration);

        $attempt->interview->update([
            'status' => InterviewStatus::COMPLETED,
            'ended_at' => now(),
            'duration_seconds' => $attempt->duration_seconds,
        ]);
    }

    private function recordNoAnswer(InterviewAttempt $attempt, ?string $reason = null): void
    {
        $this->lifecycle->noAnswer($attempt, $reason ?? __('interview.no_answer'));

        $interview = $attempt->interview;

        if ($this->shouldRetry($interview)) {
            $this->scheduleRetry($interview);

            return;
        }

        $interview->update([
            'status' => InterviewStatus::NO_ANSWER,
            'failure_reason' => __('interview.max_attempts_reached'),
        ]);
    }

    private function failAttempt(InterviewAttempt $attempt, string $reason): InterviewAttempt
    {
        Log::warning('interview.attempt_failed', [
            'attempt_id' => $attempt->id,
            'reason' => $reason,
        ]);

        $terminal = [
            InterviewAttemptStatus::COMPLETED,
            InterviewAttemptStatus::NO_ANSWER,
            InterviewAttemptStatus::FAILED,
            InterviewAttemptStatus::CANCELLED,
        ];

        if (! in_array($attempt->status, $terminal, true)) {
            $this->lifecycle->fail($attempt, $reason);
        }

        $attempt->interview?->update([
            'status' => InterviewStatus::FAILED,
            'failure_reason' => $reason,
        ]);

        return $attempt->fresh();
    }
}
