<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Talent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
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
     * What this candidate can actually be parsed from.
     *
     * A CV file is the better source and is always preferred. But a missing
     * file is the common case, not the exceptional one: SES rows seeded or
     * imported without an upload carry a filename that was never written to
     * disk, and a candidate can complete their profile without attaching
     * anything at all. Treating that as "unparseable" is what left most of the
     * pool with no score — and an empty score column reads to a recruiter as
     * "this candidate is unranked", which is a different and much worse claim
     * than "nobody has uploaded their CV yet".
     *
     * So the profile the candidate filled in is the second source. It is
     * thinner than a CV and the result says so ({@see kind}), but it is real
     * data the recruiter entered, and scoring it is strictly better than
     * refusing to score at all.
     *
     * The profile hash is domain-separated with a prefix so it can never
     * collide with a file's. The file hash is deliberately left as a bare
     * hash of the bytes — prefixing it too would be tidier, but it would also
     * invalidate every stored parse and spend a language-model call per
     * already-parsed candidate to arrive at the identical answer.
     *
     * @return array{kind: 'file'|'profile', hash: string, text: ?string, path: ?string, contents: ?string}|null
     */
    public function resumeSource(Talent $talent): ?array
    {
        if (filled($talent->resume) && ($located = $this->locateResume($talent)) !== null) {
            return [
                'kind' => 'file',
                'hash' => hash('sha256', $located['contents']),
                'text' => null,
                'path' => $located['path'],
                'contents' => $located['contents'],
            ];
        }

        $text = $this->buildProfileText($talent);

        if ($text === '') {
            return null;
        }

        return [
            'kind' => 'profile',
            'hash' => hash('sha256', 'profile:'.$text),
            'text' => $text,
            'path' => null,
            'contents' => null,
        ];
    }

    /**
     * Fingerprint of whatever this candidate would be parsed from.
     *
     * Hashes content rather than the path: a re-upload under the same filename
     * must count as a change, a rename without an edit must not, and an edited
     * profile must re-parse.
     */
    public function resumeInputHash(Talent $talent): ?string
    {
        return $this->resumeSource($talent)['hash'] ?? null;
    }

    /**
     * Structure a talent's resume.
     *
     * The file is streamed from disk and base64-encoded here rather than
     * giving the parser access to SES storage — one service, one credential.
     *
     * @param  array|null  $source  a pre-resolved {@see resumeSource()}, so a
     *                              caller that already hashed the input does
     *                              not read the same file off disk twice.
     */
    public function parseResume(Talent $talent, ?array $source = null): array
    {
        $source ??= $this->resumeSource($talent);

        if ($source === null) {
            throw new RuntimeException(
                "Nothing to parse for talent {$talent->id}: no readable CV"
                .(filled($talent->resume) ? " (stored as \"{$talent->resume}\")" : '')
                .' and no profile text on the record.'
            );
        }

        // The service takes exactly one of `text` or `file_base64` and rejects
        // a request carrying both, so this is an either/or by contract.
        return $this->post('/v1/parse/resume', $source['kind'] === 'file'
            ? [
                'talent_id' => (int) $talent->id,
                'file_base64' => base64_encode($source['contents']),
                'filename' => basename((string) $source['path']),
            ]
            : [
                'talent_id' => (int) $talent->id,
                'text' => $source['text'],
            ]);
    }

    /**
     * Structure a resume that has been uploaded but not yet saved.
     *
     * Used to pre-fill the talent form from the file the recruiter just
     * picked, which happens before there is a `talent` row to attach anything
     * to — hence the file rather than a model, and hence `talent_id: 0`. The
     * service requires the field but only echoes it back; nothing is stored on
     * either side, so this call leaves no trace of a candidate who may never
     * be created.
     *
     * @return array<string, mixed>
     */
    public function parseUploadedResume(UploadedFile $file): array
    {
        $contents = $file->get();

        if ($contents === false || $contents === '') {
            throw new RuntimeException('The uploaded file is empty.');
        }

        return $this->post('/v1/parse/resume', [
            'talent_id' => 0,
            'file_base64' => base64_encode($contents),
            'filename' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * A candidate's profile rendered as the CV they never uploaded.
     *
     * Plain prose with section headings rather than JSON, because this is fed
     * to the same extraction prompt a real CV goes through — handing it a
     * shape it was not trained on would make the two sources score
     * differently for reasons that have nothing to do with the candidate.
     *
     * Only free-text and human-meaningful fields go in. Taxonomy ids are left
     * out on purpose: they mean nothing to a language model, and {@see match()}
     * already sends them as structured, authoritative input.
     */
    public function buildProfileText(Talent $talent): string
    {
        $talent->loadMissing('user');

        $sections = [];

        if ($name = trim((string) $talent->user?->name)) {
            $sections[] = $name;
        }

        $add = function (string $heading, ?string $body) use (&$sections): void {
            $body = trim((string) $body);
            if ($body !== '') {
                $sections[] = $heading."\n".$body;
            }
        };

        $add('Experience', $talent->experience_pr);
        $add('Summary', $talent->cover_letter);
        $add('Qualifications', $talent->qualifications);
        $add('Preferences', $talent->other_desire_conditions);

        if ($talent->min_monthly_price !== null || $talent->max_monthly_price !== null) {
            $sections[] = 'Expected monthly rate'."\n".
                number_format((int) ($talent->min_monthly_price ?? 0)).' - '.
                number_format((int) ($talent->max_monthly_price ?? 0)).' JPY';
        }

        return trim(implode("\n\n", $sections));
    }

    /**
     * Find a talent's resume wherever SES actually put it.
     *
     * `talent.resume` is not one shape, because two upload paths write it
     * differently and neither is going to be rewritten under us:
     *
     * * {@see \App\Http\Controllers\TalentController} and
     *   `TalentRegistrationController` call `storeAs('public/talents/', $name)`
     *   but save only `$name` — so the row says `Taro-Tanaka.pdf` while the
     *   bytes are at `storage/app/public/talents/Taro-Tanaka.pdf`;
     * * {@see \App\Http\Traits\HasTalentDocumentTrait} saves the full
     *   `talents/Taro-Tanaka.pdf` relative to whichever disk it used;
     * * seeded rows are plain filenames sitting at the root of the local disk.
     *
     * Resolving all three here is what makes a resume uploaded through the UI
     * parseable at all. Before this, only seeded rows could be read, and a
     * genuine upload failed with "Resume file missing" — the one path a person
     * testing the feature would actually take.
     *
     * @return array{disk: string, path: string, contents: string}|null
     */
    public function locateResume(Talent $talent): ?array
    {
        $stored = trim((string) $talent->resume);

        if ($stored === '') {
            return null;
        }

        $configured = (string) config('services.ai_parser.resume_disk', 'local');
        $name = basename($stored);

        // Ordered most- to least-specific: the value as stored comes first on
        // each disk, so a correctly-recorded path never loses to a guess.
        $candidates = [
            [$configured, $stored],
            [$configured, 'talents/'.$name],
            [$configured, 'public/talents/'.$name],
            ['public', $stored],
            ['public', 'talents/'.$name],
        ];

        foreach ($candidates as [$diskName, $path]) {
            try {
                $disk = Storage::disk($diskName);
            } catch (\InvalidArgumentException) {
                // A disk that is not configured in this environment.
                continue;
            }

            if (! $disk->exists($path)) {
                continue;
            }

            $contents = $disk->get($path);

            if ($contents === null || $contents === '') {
                // Present but empty — keep looking rather than sending the
                // parser zero bytes and getting back an empty resume.
                continue;
            }

            return ['disk' => $diskName, 'path' => $path, 'contents' => $contents];
        }

        Log::warning('ai_parser.resume_not_found', [
            'talent_id' => $talent->id,
            'stored' => $stored,
        ]);

        return null;
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
     * Must-haves ride along per request: SES owns them and they change
     * without a re-parse.
     *
     * @param  array<string, mixed>  $parsedJd      payload from ai_jd_parses
     * @param  array<string, mixed>  $parsedResume  payload from ai_resume_parses
     * @param  array<int, array<string, mixed>>  $mandatory  from
     *         {@see \App\Services\ProjectRequirementService::mandatoryPayload()}
     * @return array<string, mixed>
     */
    public function match(
        array $parsedJd,
        array $parsedResume,
        Talent $talent,
        array $mandatory = [],
    ): array {
        $talent->loadMissing(['locations:id', 'subCategories:id']);

        return $this->post('/v1/match', [
            'jd' => $parsedJd,
            'resume' => $parsedResume,
            'mandatory' => $mandatory,
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
