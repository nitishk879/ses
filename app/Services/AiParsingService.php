<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Talent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Client for the SES AI parsing service.
 *
 * SES stays the system of record: this class sends what SES already knows and
 * hands the structured result back to the caller to persist. It deliberately
 * does not write to the database itself, so parsing can be retried, previewed
 * or run from a queue without side effects.
 */
class AiParsingService
{
    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $secret = null,
    ) {
    }

    /**
     * Structure a project's job description.
     *
     * The structured columns are sent alongside the free text because the
     * parser treats them as authoritative — the recruiter typed them, so the
     * language model is never allowed to contradict them.
     */
    public function parseProject(Project $project): array
    {
        return $this->post('/v1/parse/jd', $this->buildProjectPayload($project));
    }

    /**
     * The exact request body sent for a project.
     *
     * Exposed so callers can hash it and decide whether a parse is needed at
     * all — see {@see projectInputHash()}.
     *
     * @return array<string, mixed>
     */
    public function buildProjectPayload(Project $project): array
    {
        $project->loadMissing(['subCategories:id', 'locations:id']);

        return [
            'project_id' => (int) $project->id,
            'title' => (string) $project->title,
            'project_description' => (string) ($project->project_description ?? ''),
            'personnel_requirement' => (string) ($project->personnel_requirement ?? ''),
            'sub_category_ids' => $project->subCategories->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all(),
            'location_ids' => $project->locations->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all(),
            'minimum_price' => $project->minimum_price !== null ? (int) $project->minimum_price : null,
            'maximum_price' => $project->maximum_price !== null ? (int) $project->maximum_price : null,
            'experience' => $project->getRawOriginal('experience'),
            'contract_classification' => $project->getRawOriginal('contract_classification'),
            'remote_operation_possible' => (bool) $project->remote_operation_possible,
        ];
    }

    /**
     * Fingerprint of everything that would be sent for this project.
     *
     * This is what makes re-parsing cheap: if the fingerprint is unchanged
     * the language model is never called at all. Comparing the *service's*
     * returned hash instead would be too late — the call would already have
     * cost its five seconds of GPU.
     *
     * `project_id` is excluded so the fingerprint describes the content, and
     * ids are sorted in the payload so a reordered pivot is not mistaken for
     * an edit.
     */
    public function projectInputHash(Project $project): string
    {
        $payload = $this->buildProjectPayload($project);
        unset($payload['project_id']);

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Fingerprint of a candidate's resume file.
     *
     * Hashes the bytes rather than the path: a re-upload under the same
     * filename must count as a change, and a rename without an edit must not.
     */
    public function resumeInputHash(Talent $talent): ?string
    {
        if (blank($talent->resume)) {
            return null;
        }

        $disk = Storage::disk(config('services.ai_parser.resume_disk', 'local'));

        if (! $disk->exists($talent->resume)) {
            return null;
        }

        return hash('sha256', $disk->get($talent->resume));
    }

    /**
     * Structure a talent's resume.
     *
     * The file is streamed from disk and base64-encoded here rather than
     * giving the parser access to SES storage — one service, one credential.
     */
    public function parseResume(Talent $talent): array
    {
        if (blank($talent->resume)) {
            throw new RuntimeException("Talent {$talent->id} has no resume on file.");
        }

        $disk = Storage::disk(config('services.ai_parser.resume_disk', 'local'));

        if (! $disk->exists($talent->resume)) {
            throw new RuntimeException("Resume file missing for talent {$talent->id}.");
        }

        return $this->post('/v1/parse/resume', [
            'talent_id' => (int) $talent->id,
            'file_base64' => base64_encode($disk->get($talent->resume)),
            'filename' => basename($talent->resume),
        ]);
    }

    /**
     * Score a parsed resume against a parsed JD.
     *
     * Rate and preferred locations are sent from SES rather than read out of
     * the CV: the recruiter entered them on the talent form, so they are
     * authoritative in exactly the way the project's own columns are.
     *
     * This call makes no LLM request on the service side — it is arithmetic
     * over the two parses, which is what makes scoring a whole candidate pool
     * affordable.
     *
     * @param  array<string, mixed>  $parsedJd      payload from ai_jd_parses
     * @param  array<string, mixed>  $parsedResume  payload from ai_resume_parses
     * @return array<string, mixed>
     */
    public function match(array $parsedJd, array $parsedResume, Talent $talent): array
    {
        $talent->loadMissing(['locations:id', 'subCategories:id']);

        return $this->post('/v1/match', [
            'jd' => $parsedJd,
            'resume' => $parsedResume,
            'talent' => [
                'talent_id' => (int) $talent->id,
                'min_monthly_price' => $talent->min_monthly_price !== null
                    ? (int) $talent->min_monthly_price : null,
                'max_monthly_price' => $talent->max_monthly_price !== null
                    ? (int) $talent->max_monthly_price : null,
                'location_ids' => $talent->locations->pluck('id')
                    ->map(fn ($id) => (int) $id)->all(),
                'sub_category_ids' => $talent->subCategories->pluck('id')
                    ->map(fn ($id) => (int) $id)->all(),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload): array
    {
        $baseUrl = $this->baseUrl ?: config('services.ai_parser.url');
        $secret = $this->secret ?: config('services.ai_parser.secret');

        if (blank($baseUrl) || blank($secret)) {
            throw new RuntimeException('AI parser URL/secret are not configured.');
        }

        try {
            $response = Http::withHeaders([
                'X-Internal-Secret' => $secret,
                // Lets one parse be traced from this log line into the
                // Python service's own logs.
                'X-Request-Id' => (string) str()->uuid(),
            ])
                ->timeout((int) config('services.ai_parser.timeout', 120))
                // Retry only the transient failures; a 4xx is our bug and
                // retrying it just burns the request budget.
                ->retry(2, 500, fn ($exception) => $exception instanceof ConnectionException)
                ->acceptJson()
                ->post(rtrim($baseUrl, '/').$path, $payload)
                ->throw();
        } catch (ConnectionException $e) {
            Log::error('ai_parser.unreachable', ['path' => $path, 'error' => $e->getMessage()]);

            throw new RuntimeException('AI parsing service is unreachable.', previous: $e);
        } catch (RequestException $e) {
            // Never log the payload: it contains resume content.
            Log::error('ai_parser.rejected', [
                'path' => $path,
                'status' => $e->response->status(),
                'detail' => $e->response->json('detail'),
            ]);

            throw new RuntimeException(
                'AI parsing failed: '.($e->response->json('detail') ?? 'unknown error'),
                previous: $e
            );
        }

        return $response->json();
    }
}
