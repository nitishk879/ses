<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A stored JD-to-candidate match score. */
class AiMatch extends Model
{
    protected $table = 'ai_matches';

    protected $fillable = [
        'project_id',
        'talent_id',
        'score',
        // Both of these are written through updateOrCreate, which is mass
        // assignment — a column missing from this list is dropped in silence
        // and the row keeps its database default. For `meets_mandatory` that
        // default is `true`, so an omission here does not read as a bug: it
        // reads as every candidate clearing every must-have.
        'meets_mandatory',
        'unverified_mandatory',
        'payload',
        'scorer_version',
        'jd_source_hash',
        'resume_source_hash',
        'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'score' => 'integer',
            'meets_mandatory' => 'boolean',
            'unverified_mandatory' => 'integer',
            'scored_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function talent(): BelongsTo
    {
        return $this->belongsTo(Talent::class);
    }

    /** Highest scoring candidates first — the shortlist order. */
    public function scopeRanked(Builder $query): Builder
    {
        return $query->orderByDesc('score')->orderBy('talent_id');
    }

    /** True when either side has been re-parsed since this score was computed. */
    public function isStale(?string $jdHash, ?string $resumeHash): bool
    {
        return $this->jd_source_hash !== $jdHash
            || $this->resume_source_hash !== $resumeHash;
    }

    /** @return array<int, string> Human-readable reasons, strongest first. */
    public function reasons(): array
    {
        return $this->payload['reasons'] ?? [];
    }

    /** @return array<int, string> Hard misses a recruiter must see. */
    public function blockers(): array
    {
        return $this->payload['blockers'] ?? [];
    }

    /** @return array<int, string> Things the resume never stated. */
    public function unverified(): array
    {
        return $this->payload['unverified'] ?? [];
    }
}
