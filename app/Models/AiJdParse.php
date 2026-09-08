<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The structured form of a project's job description.
 */
class AiJdParse extends Model
{
    protected $table = 'ai_jd_parses';

    protected $fillable = [
        'project_id',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Skills the parser could not map onto an SES sub-category.
     *
     * Worth surfacing to a recruiter: the SES taxonomy holds job domains
     * ("Backend", "Web system"), not technologies, so "Java" and "AWS" land
     * here as a matter of course. A term appearing repeatedly is a candidate
     * for the alias table.
     */
    public function unmappedSkills(): array
    {
        return $this->payload['unmapped_skills'] ?? [];
    }
}
