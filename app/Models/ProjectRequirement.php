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
}
