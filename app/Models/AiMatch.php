<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stored JD-to-candidate match score.
 *
 * The score is a plain integer column rather than a value dug out of the JSON
 * so the database can sort and index it — that is what makes "top 5 for this
 * project" a single indexed query instead of a scan.
 */
class AiMatch extends Model
{
    protected $table = 'ai_matches';

    protected $fillable = [
        'project_id',
        'talent_id',
        'score',
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

    /**
     * True when either side has been re-parsed since this score was computed.
     *
     * A score derived from an older parse is not wrong so much as unexplained:
     * its reasons quote evidence that may no longer exist in the document.
     */
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
