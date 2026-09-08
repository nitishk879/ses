<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The structured form of a candidate's resume.
 *
 * Note this row holds extracted personal data. It is deliberately a separate
 * table so it can be purged for a single candidate without touching their
 * profile, and so retention can be applied to it on its own.
 */
class AiResumeParse extends Model
{
    protected $table = 'ai_resume_parses';

    protected $fillable = [
        'talent_id',
        'parser_version',
        'source_hash',
        'payload',
        'parsed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'parsed_at' => 'datetime',
        ];
    }

    public function talent(): BelongsTo
    {
        return $this->belongsTo(Talent::class);
    }

    /** @return array<int, string> Skill names as written on the CV. */
    public function skillNames(): array
    {
        return array_values(array_filter(array_map(
            fn (array $skill) => $skill['raw'] ?? null,
            $this->payload['skills'] ?? []
        )));
    }

    public function totalExperienceMonths(): ?int
    {
        return $this->payload['total_experience_months'] ?? null;
    }
}
