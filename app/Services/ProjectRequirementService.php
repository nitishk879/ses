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

/**
 * The matching screen's requirement list: what this project actually asks for.
 *
 * Every row comes from something the project states — a field on the project
 * form, or a phrase in its description — and nothing else. A row whose source
 * goes away goes away with it, unless the recruiter had made it a must-have,
 * in which case it stays visible, greyed and not enforced, so a must-have
 * never disappears without a word.
 */
class ProjectRequirementService
{
    /**
     * The marker the JD parser stamps on a skill it copied from the SES form
     * rather than read out of prose.
     *
     * Duplicated from `app/parsing/jd_parser.py` in the AI service, which is
     * where it is written. A shared constant across two languages and two
     * deployments would be a bigger lie than this comment: what matters is
     * that both ends agree, and the only thing that keeps them agreeing is
     * somebody reading this line. Nothing breaks if it drifts — a form-derived
     * skill is simply labelled as coming from the text — which is precisely
     * why it needs saying.
     */
    private const FORM_EVIDENCE = 'Selected in the SES project form';

    /**
     * Rebuild a project's requirement list from its form and its parsed text.
     *
     * Runs on every project save (form fields only — the stored parse is
     * reused for the text) and after every JD parse. A project that has never
     * been parsed still gets its form-derived rows, so must-haves can be set
     * before the first matching run rather than only after it.
     *
     * @return array{created: int, refreshed: int, stale: int, removed: int}
     */
    public function syncFromParse(Project $project, ?AiJdParse $parse = null): array
    {
        $parse ??= AiJdParse::firstWhere('project_id', $project->id);

        $extracted = $this->extract($project, $parse?->payload ?? []);

        $created = $refreshed = $removed = 0;

        DB::transaction(function () use ($project, $extracted, &$created, &$refreshed, &$removed) {
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

                // Everything but the recruiter's must-have switch.
                $row->fill([
                    'kind' => $candidate['kind'],
                    'label' => $candidate['label'],
                    'min_months' => $candidate['min_months'],
                    'level' => $candidate['level'],
                    'evidence' => $candidate['evidence'],
                    'origin' => $candidate['origin'],
                    'position' => $candidate['position'],
                    'in_latest_parse' => true,
                ])->save();
                $refreshed++;
            }

            /*
             * Everything the project no longer states.
             *
             * Not a must-have: deleted. It carried no decision, and a list
             * that keeps showing a category the recruiter just unticked is a
             * list that does not describe the project. If the field comes
             * back, the row comes back exactly as it was — off.
             *
             * A must-have: kept but marked stale, which takes it out of the
             * gate (see scopeGating) while leaving it on screen. Deleting it
             * would drop a hiring rule the recruiter set without telling them.
             *
             * Manual rows are exempt from both: "not stated by the project" is
             * why somebody added them by hand.
             */
            $keys = array_column($extracted, 'requirement_key');

            $gone = ProjectRequirement::where('project_id', $project->id)
                ->where('source', 'ai')
                ->when($keys !== [], fn ($q) => $q->whereNotIn('requirement_key', $keys));

            $removed = (clone $gone)->where('is_mandatory', false)->delete();

            (clone $gone)->where('is_mandatory', true)->update(['in_latest_parse' => false]);
        });

        $stale = ProjectRequirement::where('project_id', $project->id)
            ->where('in_latest_parse', false)
            ->count();

        Log::info('ai.requirements.synced', [
            'project_id' => $project->id,
            'created' => $created,
            'refreshed' => $refreshed,
            'removed' => $removed,
            'stale' => $stale,
            'parsed' => $parse !== null,
        ]);

        return ['created' => $created, 'refreshed' => $refreshed, 'stale' => $stale, 'removed' => $removed];
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

        /*
         * ── Skills: the categories ticked on the form ─────────────────────
         *
         * Read from the pivot as it is *now*, not from the parse. The parser
         * copies the ticked categories into its payload too, but that copy is
         * as old as the last matching run — reading it meant a category the
         * recruiter had since unticked stayed on this list until somebody ran
         * matching again.
         *
         * Keyed `skill:id:N`, the same key the parse copy used, so a must-have
         * set before this change carries over.
         */
        $subCategories = $project->subCategories()
            ->orderBy('sub_categories.id')
            ->get(['sub_categories.id', 'sub_categories.title']);

        foreach ($subCategories as $sub) {
            $label = trim((string) $sub->title);

            if ($label === '') {
                continue;
            }

            $key = 'skill:id:'.(int) $sub->id;

            $rows[$key] = [
                'kind' => RequirementKind::SKILL,
                'requirement_key' => $key,
                'label' => $label,
                'min_months' => null,
                'level' => null,
                'evidence' => null,
                'origin' => 'project_form',
                'position' => $position++,
            ];
        }

        /*
         * ── Skills: phrases from the description ──────────────────────────
         *
         * Required and preferred both become rows. That is the point of the
         * feature: a recruiter is allowed to decide that something the JD
         * merely prefers is non-negotiable for *this* client. Marking it
         * mandatory is what promotes it.
         */
        foreach (['required_skills', 'preferred_skills'] as $bucket) {
            foreach ($payload[$bucket] ?? [] as $skill) {
                $label = trim((string) ($skill['raw'] ?? ''));

                if ($label === '' || $this->isFormCopy($skill)) {
                    // The parse's copy of the form's categories, superseded by
                    // the live pivot read above.
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
                    'origin' => 'jd_text',
                    'position' => $position++,
                ];
            }
        }

        /*
         * ── Experience ────────────────────────────────────────────────────
         *
         * Only when the project states one. The form's "Required experience"
         * wins — a person typed it — and a number the parser found in the
         * description ("3年以上") is used only when the form is empty. Neither:
         * no row, because this list shows what the project asks for and this
         * project does not ask for experience.
         */
        $formMonths = (int) ($project->min_experience_months ?? 0);
        $textMonths = $payload['min_experience_months'] ?? null;
        $textMonths = is_numeric($textMonths) ? max(0, (int) $textMonths) : 0;

        $months = $formMonths > 0 ? $formMonths : $textMonths;

        if ($months > 0) {
            $rows['experience'] = [
                'kind' => RequirementKind::EXPERIENCE,
                'requirement_key' => 'experience',
                'label' => $this->experienceLabel($months),
                'min_months' => $months,
                'level' => null,
                'evidence' => null,
                'origin' => $formMonths > 0 ? 'project_form' : 'jd_text',
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
                'origin' => 'jd_text',
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
                'origin' => 'project_form',
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
                'origin' => 'project_form',
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
                'origin' => 'project_form',
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

    /**
     * Whether a parsed skill is the parser's copy of a ticked category.
     *
     * The parser marks those with a fixed evidence string and `source: form`;
     * either is enough.
     *
     * @param  array<string, mixed>  $skill
     */
    private function isFormCopy(array $skill): bool
    {
        return ($skill['evidence'] ?? null) === self::FORM_EVIDENCE
            || ($skill['source'] ?? null) === 'form';
    }

    /**
     * How a required-experience threshold reads: "At least 2 years 6 months".
     *
     * Months are shown, not rounded away — the form asks for them, and a
     * screen that shows "2 years" for a project that asked for two and a half
     * is a screen the recruiter stops trusting.
     */
    public function experienceLabel(int $months): string
    {
        return __('interview.requirement.experience_label', [
            'duration' => self::duration($months),
        ]);
    }

    /** "2 years 6 months", "1 year", "6 months" — in the current locale. */
    public static function duration(int $months): string
    {
        $parts = [];

        if (intdiv($months, 12) > 0) {
            $parts[] = trans_choice('interview.requirement.duration_years', intdiv($months, 12));
        }

        if ($months % 12 > 0) {
            $parts[] = trans_choice('interview.requirement.duration_months', $months % 12);
        }

        return implode(' ', $parts);
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
