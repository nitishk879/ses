<?php

namespace App\Services;

use App\Exceptions\Interview\InterviewAiBusy;
use App\Exceptions\Interview\InterviewAiUnavailable;
use App\Exceptions\Interview\InterviewCallingNotConfigured;
use App\Models\Project;
use App\Models\Talent;
use App\Support\PhoneNumber;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Client for the interview half of ses-ai-service.
 *
 * Four calls, mirroring the four stages the Python service keeps separate:
 * plan the questions, place the call, read the outcome, score the transcript.
 * The separation matters here too — planning is free and reviewable, dialling
 * is not, and evaluation must be re-runnable against a stored transcript
 * without phoning anybody again.
 *
 * Like {@see AiParsingService}, this writes nothing. The caller persists.
 */
class InterviewAiService
{
    /**
     * The AI service answers 501 when its DENAI and TWILIO settings are
     * absent. Distinguished from a 503 so a deployment gap is never reported
     * as an outage — the fix for one is a secret, for the other a restart.
     */
    public const STATUS_NOT_CONFIGURED = 501;

    /** All interview call slots are busy. Back off and try again. */
    public const STATUS_BUSY = 429;

    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $secret = null,
    ) {
    }

    /**
     * Whether the AI service is able to place calls at all right now.
     *
     * Read from its own /health rather than inferred from our config: the
     * settings that decide this live in the Python service's environment, and
     * the whole failure we are guarding against was a deployment where ours
     * looked complete and theirs was not.
     *
     * @return array{configured: bool, missing: array<string>}
     */
    public function callingStatus(): array
    {
        try {
            $body = Http::timeout(10)
                ->acceptJson()
                ->get(rtrim($this->resolveBaseUrl(), '/').'/health')
                ->throw()
                ->json('interview_calling') ?? [];
        } catch (ConnectionException|RequestException $e) {
            return ['configured' => false, 'missing' => ['ai service unreachable']];
        }

        return [
            'configured' => (bool) ($body['configured'] ?? false),
            'missing' => $body['missing_settings'] ?? [],
        ];
    }

    /**
     * Build the questions and the agent script for one candidate.
     *
     * Takes the stored JD and resume parses plus the match result, because the
     * interview is shaped by the *gaps*: the questions worth five minutes are
     * the ones about requirements the CV did not evidence.
     *
     * @param  array<string, mixed>  $parsedJd
     * @param  array<string, mixed>  $parsedResume
     * @param  array<string, mixed>  $match  payload from ai_matches
     * @return array<string, mixed>
     */
    public function plan(
        Project $project,
        Talent $talent,
        array $parsedJd,
        array $parsedResume,
        array $match = [],
    ): array {
        return $this->post('/v1/interview/plan', [
            'project_id' => (int) $project->id,
            'talent_id' => (int) $talent->id,
            'jd' => $parsedJd,
            'resume' => $parsedResume,
            'matched_required_skills' => $this->skillNames($match, 'matched_required_skills'),
            'missing_required_skills' => $this->skillNames($match, 'missing_required_skills'),
            'total_seconds' => (int) config('services.interview.duration_seconds', 300),
            'language' => (string) config('services.interview.language', 'japanese'),
            'candidate_name' => $talent->user?->name,
        ]);
    }

    /**
     * Dial the candidate and run the planned interview.
     *
     * @param  array<string, mixed>  $plan  exactly what plan() returned
     * @param  array<string, string>  $metadata
     * @return array<string, mixed>
     */
    public function call(
        array $plan,
        PhoneNumber $to,
        array $metadata = [],
        ?string $agentId = null,
    ): array {
        $from = config('services.interview.from_number');

        if (blank($from)) {
            throw new RuntimeException(
                'INTERVIEW_FROM_NUMBER is not set; no outbound number to call from.'
            );
        }

        return $this->post('/v1/interview/call', [
            'plan' => $plan,
            'to_phone' => $to->e164,
            'from_phone' => (string) $from,
            'metadata' => $metadata,
            // Which dashboard bot conducts this call. Its prompt becomes the
            // interview's persona, and its id is what attributes the finished
            // conversation to it on the DenAI dashboard. Null is valid: the
            // interview runs on the generated script alone.
            'agent_id' => $agentId,
        ]);
    }

    /**
     * The interview bots a recruiter can choose between.
     *
     * Read live from the DenAI dashboard rather than mirrored into this
     * database. A local copy would need syncing, and a stale copy offering a
     * bot that no longer exists is worse than no list at all.
     *
     * Returns an empty array when the directory is unreachable — the UI says
     * so and still accepts an id typed by hand, because a recruiter who
     * already knows which bot they want should not be blocked by a network
     * path that has nothing to do with them.
     *
     * @return array<int, array<string, mixed>>
     */
    public function agents(): array
    {
        try {
            return $this->get('/v1/interview/agents');
        } catch (Throwable $e) {
            Log::warning('interview.agents_unavailable', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Read a placed call's status and transcript.
     *
     * A call that has not finished is a normal 200 with `lifecycle` of
     * `pending` or `in_progress` — branch on that, never on `status`, which is
     * Twilio's raw string and reads "completed" for calls nobody spoke on.
     *
     * @return array<string, mixed>
     */
    public function callResult(string $callSid): array
    {
        return $this->get('/v1/interview/call/'.urlencode($callSid));
    }

    /**
     * Score a finished interview against the job description.
     *
     * @param  array<string, mixed>  $parsedJd
     * @param  array<int, array<string, mixed>>  $questions
     * @param  array<int, array<string, mixed>>  $transcript
     * @return array<string, mixed>
     */
    public function evaluate(
        Project $project,
        Talent $talent,
        array $parsedJd,
        array $questions,
        array $transcript,
        ?int $durationSeconds = null,
    ): array {
        return $this->post('/v1/interview/evaluate', [
            'project_id' => (int) $project->id,
            'talent_id' => (int) $talent->id,
            'jd' => $parsedJd,
            'questions' => array_values($questions),
            'transcript' => array_values($transcript),
            'duration_seconds' => $durationSeconds,
            'language' => (string) config('services.interview.language', 'japanese'),
        ]);
    }

    /**
     * Skill names out of a stored match payload.
     *
     * The scorer reports these either as plain strings or as objects carrying
     * the canonical mapping, depending on the dimension. Accepting both keeps
     * a scorer change from silently producing an interview with no questions.
     *
     * @param  array<string, mixed>  $match
     * @return array<int, string>
     */
    private function skillNames(array $match, string $key): array
    {
        $raw = $match[$key] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->map(fn ($entry) => is_array($entry)
                ? ($entry['raw'] ?? $entry['canonical'] ?? $entry['name'] ?? null)
                : $entry)
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->map(fn (string $name) => trim($name))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload): array
    {
        return $this->send($path, fn ($request, string $url) => $request->post($url, $payload));
    }

    /** @return array<string, mixed> */
    private function get(string $path): array
    {
        return $this->send($path, fn ($request, string $url) => $request->get($url));
    }

    /**
     * @param  callable(\Illuminate\Http\Client\PendingRequest, string): Response  $dispatch
     * @return array<string, mixed>
     */
    private function send(string $path, callable $dispatch): array
    {
        $baseUrl = $this->resolveBaseUrl();
        $secret = $this->secret ?: config('services.ai_parser.secret');

        if (blank($secret)) {
            throw new RuntimeException('AI_PARSER_SECRET is not configured.');
        }

        $request = Http::withHeaders([
            'X-Internal-Secret' => $secret,
            'X-Request-Id' => (string) str()->uuid(),
        ])
            ->timeout((int) config('services.ai_parser.timeout', 120))
            // Only transient failures. A 4xx is our bug, and a 429 means the
            // service is deliberately shedding load — hammering it is the one
            // thing guaranteed not to help.
            ->retry(2, 500, fn ($exception) => $exception instanceof ConnectionException)
            ->acceptJson();

        try {
            /** @var Response $response */
            $response = $dispatch($request, rtrim($baseUrl, '/').$path)->throw();
        } catch (ConnectionException $e) {
            Log::error('interview_ai.unreachable', ['path' => $path, 'error' => $e->getMessage()]);

            throw new InterviewAiUnavailable('AI interview service is unreachable.', previous: $e);
        } catch (RequestException $e) {
            // Never log the payload: it contains resume-derived content and,
            // on the call path, a candidate's phone number.
            $status = $e->response->status();
            $detail = $e->response->json('detail') ?? 'unknown error';

            Log::error('interview_ai.rejected', ['path' => $path, 'status' => $status]);

            throw match ($status) {
                self::STATUS_NOT_CONFIGURED => new InterviewCallingNotConfigured(
                    is_string($detail) ? $detail : 'interview calling is not configured',
                    previous: $e
                ),
                self::STATUS_BUSY => new InterviewAiBusy(
                    is_string($detail) ? $detail : 'all interview call slots are busy',
                    (int) ($e->response->header('Retry-After') ?: 120),
                    previous: $e
                ),
                default => new RuntimeException(
                    'AI interview request failed: '.(is_string($detail) ? $detail : json_encode($detail)),
                    previous: $e
                ),
            };
        }

        return $response->json() ?? [];
    }

    private function resolveBaseUrl(): string
    {
        $baseUrl = $this->baseUrl ?: config('services.ai_parser.url');

        if (blank($baseUrl)) {
            throw new RuntimeException('AI_PARSER_URL is not configured.');
        }

        return $baseUrl;
    }
}
