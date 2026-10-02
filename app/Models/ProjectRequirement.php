<?php

namespace App\Models;

use App\Enums\RequirementKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One requirement of a project, and whether the recruiter made it a must-have. */
class ProjectRequirement extends Model
{
    protected $fillable = [
        'project_id',
        'kind',
        'requirement_key',
        'label',
        'is_mandatory',
        'min_months',
        'level',
        'source',
        'origin',
        'in_latest_parse',
        'evidence',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'kind' => RequirementKind::class,
            'is_mandatory' => 'boolean',
            'in_latest_parse' => 'boolean',
            'min_months' => 'integer',
            'position' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** The requirements that actually gate this project's candidates. */
    public function scopeGating(Builder $query): Builder
    {
        return $query->where('is_mandatory', true)->where('in_latest_parse', true);
    }

    /** Recruiter-facing ordering: the JD's own order. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    /**
     * This requirement as the AI service's `/v1/match` expects it.
     *
     * @return array<string, mixed>
     */
    public function toMandatoryPayload(): array
    {
        $payload = [
            'key' => (string) $this->id,
            'kind' => $this->kind->value,
            'label' => $this->label,
        ];

        if ($this->kind === RequirementKind::EXPERIENCE) {
            $payload['min_months'] = (int) $this->min_months;
        }

        if ($this->kind === RequirementKind::LANGUAGE && filled($this->level)) {
            $payload['level'] = $this->level;
        }

        return $payload;
    }

    /** True when this row cannot be sent to the scorer as it stands. */
    public function isIncomplete(): bool
    {
        return $this->kind === RequirementKind::EXPERIENCE
            && ($this->min_months === null || $this->min_months <= 0);
    }

    /**
     * Where this requirement came from, said in one short phrase.
     *
     * The card is headed "Requirements from this job description", and for
     * most of the rows under it that heading is wrong. `Language · Japanese`
     * is a radio on the project form. `Budget · within ¥1,200,000` is a column
     * on it. `Skill · RPA` is a sub-category checkbox. Only the ones carrying
     * quoted evidence were read out of the description at all.
     *
     * A recruiter who cannot tell those apart goes looking in the project form
     * for the sentence that produced `Location`, does not find one, and
     * concludes the list is arbitrary — at which point the must-have switches
     * stop being used, because nobody flips a switch they cannot account for.
     */
    public function originLabel(): string
    {
        return __('interview.requirement.origin.'.($this->origin ?: 'jd_text'));
    }

    /**
     * The label in the reader's language.
     *
     * `label` is written in whichever locale the sync ran in — a recruiter
     * saving the form in Japanese, a queue worker in English — so the three
     * kinds whose wording SES owns are rebuilt from their data instead. Skills
     * and languages are names, and read the same either way.
     */
    public function displayLabel(): string
    {
        return match ($this->kind) {
            RequirementKind::EXPERIENCE => (int) $this->min_months > 0
                ? __('interview.requirement.experience_label', [
                    'duration' => \App\Services\ProjectRequirementService::duration((int) $this->min_months),
                ])
                : $this->label,
            RequirementKind::LOCATION => __('interview.requirement.location_label'),
            RequirementKind::BUDGET => $this->project?->maximum_price !== null
                ? __('interview.requirement.budget_label', [
                    'amount' => number_format((int) $this->project->maximum_price),
                ])
                : $this->label,
            default => $this->label,
        };
    }
}
