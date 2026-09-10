<?php

namespace App\Models;

use App\Enums\InterviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'talent_id',
        'status',
        'channel',
        'timezone',
        'scheduled_at',
        'started_at',
        'ended_at',
        'duration_seconds',
        'failure_reason',
        'provider_reference',
        'metadata',
        'attempt_number',
    ];

    protected function casts(): array
    {
        return [
            'status' => InterviewStatus::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'metadata' => 'array',
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

    /**
     * Interview can be done in multiple steps.
     *
     * @return HasMany
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(InterviewAttempt::class)
            ->orderBy('attempt_number');
    }

    public function evaluations(): HasManyThrough
    {
        return $this->hasManyThrough(
            InterviewEvaluation::class,
            InterviewAttempt::class,
            'interview_id',
            'interview_attempt_id',
            'id',
            'id'
        );
    }
}
