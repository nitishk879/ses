<?php

namespace App\Services;

use App\Enums\RequirementKind;
use App\Models\AiJdParse;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Support\ProjectLanguages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Normalizer;

/** Keep a project's requirement list in step with its parsed JD — without ever losing what the recruiter decided. */
class ProjectRequirementService
{
    /**
     * Rebuild a project's requirement list from its current JD parse.
     *
     * @return array{created: int, refreshed: int, stale: int}
     */
    public function syncFromParse(Project $project, ?AiJdParse $parse = null): array
    {
        $parse ??= AiJdParse::firstWhere('project_id', $project->id);

        if (! $parse) {
            return ['created' => 0, 'refreshed' => 0, 'stale' => 0];
        }

        $extracted = $this->extract($project, $parse->payload ?? []);

        $created = $refreshed = 0;

        DB::transaction(function () use ($project, $extracted, &$created, &$refreshed) {
            $existing = ProjectRequirement::where('project_id', $project->id)
                ->get()
                ->keyBy('requirement_key');

            foreach ($extracted as $candidate) {
                $row = $existing->get($candidate['requirement_key']);

                if (! $row) {
                    ProjectRequirement::create($candidate + [
                        'project_id' => $project->id,
                        // A brand-new requirement is not mandatory until a
                        // person says so. Defaulting to "must" would silently
                        // tighten the gate every time a JD gained a sentence.
                        'is_mandatory' => false,
                        'source' => 'ai',
                        'in_latest_parse' => true,
                    ]);
                    $created++;

                    continue;
                }

                // The AI-owned columns, and only those.
                $row->fill([
                    'kind' => $candidate['kind'],
                    'label' => $candidate['label'],
                    'min_months' => $candidate['min_months'],
                    'level' => $candidate['level'],
                    'evidence' => $candidate['evidence'],
                    'position' => $candidate['position'],
                    'in_latest_parse' => true,
                ])->save();
                $refreshed++;
            }

            // Everything the parse no longer mentions. Manual rows are exempt:
            // the recruiter added them precisely because the JD does not say
            // them, so "not in the parse" is their normal state.
            $keys = array_column($extracted, 'requirement_key');

            ProjectRequirement::where('project_id', $project->id)
                ->where('source', 'ai')
                ->when($keys !== [], fn ($q) => $q->whereNotIn('requirement_key', $keys))
                ->update(['in_latest_parse' => false]);
        });

        $stale = ProjectRequirement::where('project_id', $project->id)
            ->where('in_latest_parse', false)
            ->count();

        Log::info('ai.requirements.synced', [
            'project_id' => $project->id,
            'created' => $created,
            'refreshed' => $refreshed,
            'stale' => $stale,
        ]);

        return ['created' => $created, 'refreshed' => $refreshed, 'stale' => $stale];
    }

    /**
     * The must-haves to send with a scoring request.
     *
     * @return array<int, array<string, mixed>>
     */
    public function mandatoryPayload(Project $project): array
    {
        return ProjectRequirement::where('project_id', $project->id)
            ->gating()
            ->ordered()
            ->get()
            ->reject(fn (ProjectRequirement $r) => $r->isIncomplete())
            ->map(fn (ProjectRequirement $r) => $r->toMandatoryPayload())
            ->values()
            ->all();
    }

    /**
     * Requirements pulled out of a parse payload, ready to upsert.
     *
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function extract(Project $project, array $payload): array
    {
        $rows = [];
        $position = 0;

        // ── Skills ────────────────────────────────────────────────────────
        //
        // Required and preferred both become rows. That is the point of the
        // feature: a recruiter is allowed to decide that something the JD
        // merely prefers is non-negotiable for *this* client, which is exactly
        // the example in the brief. Marking it mandatory is what promotes it.
        foreach (['required_skills', 'preferred_skills'] as $bucket) {
            foreach ($payload[$bucket] ?? [] as $skill) {
                $label = trim((string) ($skill['raw'] ?? ''));

                if ($label === '') {
                    continue;
                }

                $key = $this->skillKey($skill);

                // A skill listed as both required and preferred, or twice
                // under different spellings that normalize together, is one
                // requirement — not two rows the recruiter has to tick twice.
                if (isset($rows[$key])) {
                    continue;
                }

                $rows[$key] = [
                    'kind' => RequirementKind::SKILL,
                    'requirement_key' => $key,
                    'label' => $label,
                    'min_months' => null,
                    'level' => null,
                    'evidence' => $skill['evidence'] ?? null,
                    'position' => $position++,
                ];
            }
        }

        // ── Experience ────────────────────────────────────────────────────
        $months = $payload['min_experience_months'] ?? null;

        if (is_numeric($months) && (int) $months > 0) {
            $years = intdiv((int) $months, 12);
            $rows['experience'] = [
                'kind' => RequirementKind::EXPERIENCE,
                'requirement_key' => 'experience',
                'label' => __('interview.requirement.experience_label', ['years' => $years]),
                'min_months' => (int) $months,
                'level' => null,
                'evidence' => null,
                'position' => $position++,
            ];
        }

        // ── Languages ─────────────────────────────────────────────────────
        //
        // Two sources, and the prose one goes first because it is the richer
        // of the two: a JD that says "JLPT N2 以上" carries a *level*, and the
        // project form's radio does not. Keying both on the language alone
        // means the form can only ever add a language the prose missed, never
        // overwrite a level the prose established.
        foreach ($payload['languages'] ?? [] as $language) {
            $name = trim((string) ($language['language'] ?? ''));

            if ($name === '') {
                continue;
            }

            // Keyed on the language alone, not the level. A JD edited from N2
            // to N1 is the same requirement at a new bar — re-keying it would
            // orphan the recruiter's must-have onto a row nothing reads.
            $key = 'language:'.$this->normalize($name);

            if (isset($rows[$key])) {
                continue;
            }

            $rows[$key] = [
                'kind' => RequirementKind::LANGUAGE,
                'requirement_key' => $key,
                'label' => $name,
                'min_months' => null,
                'level' => $language['level'] ?? null,
                'evidence' => $language['evidence'] ?? null,
                'position' => $position++,
            ];
        }

        // The language the recruiter actually picked on the project form.
        foreach (ProjectLanguages::forProject($project) as $name) {
            $key = 'language:'.$this->normalize($name);

            if (isset($rows[$key])) {
                // The prose already established this one, with a level the
                // form cannot express. Leave it alone.
                continue;
            }

            $rows[$key] = [
                'kind' => RequirementKind::LANGUAGE,
                'requirement_key' => $key,
                'label' => $name,
                'min_months' => null,
                // No level: the form asks which language, not how well. The
                // scorer reads a null level as "speaking it at all is the
                // bar", which is exactly what the form asked.
                'level' => null,
                'evidence' => null,
                'position' => $position++,
            ];
        }

        // ── Project-level conditions ──────────────────────────────────────
        //
        // Not from the JD text: these are SES columns the recruiter already
        // filled in. They are offered as tickable requirements because "must
        // be in commuting distance" and "must be within budget" are real
        // must-haves, and the scorer can answer both from data SES owns.
        if (! $project->remote_operation_possible && $project->locations()->exists()) {
            $rows['location'] = [
                'kind' => RequirementKind::LOCATION,
                'requirement_key' => 'location',
                'label' => __('interview.requirement.location_label'),
                'min_months' => null,
                'level' => null,
                'evidence' => null,
                'position' => $position++,
            ];
        }

        if ($project->maximum_price !== null) {
            $rows['budget'] = [
                'kind' => RequirementKind::BUDGET,
                'requirement_key' => 'budget',
                'label' => __('interview.requirement.budget_label', [
                    'amount' => number_format((int) $project->maximum_price),
                ]),
                'min_months' => null,
                'level' => null,
                'evidence' => null,
                'position' => $position++,
            ];
        }

        return array_values($rows);
    }

    /**
     * Stable identity for a skill across re-parses.
     *
     * @param  array<string, mixed>  $skill
     */
    private function skillKey(array $skill): string
    {
        if (filled($skill['sub_category_id'] ?? null)) {
            return 'skill:id:'.(int) $skill['sub_category_id'];
        }

        return 'skill:'.$this->normalize((string) ($skill['raw'] ?? ''));
    }

    /** Fold a surface form down to a comparison key. */
    private function normalize(string $value): string
    {
        if (class_exists(Normalizer::class)) {
            $value = Normalizer::normalize($value, Normalizer::FORM_KC) ?: $value;
        }

        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}
