<?php

namespace App\Models;

use App\Enums\InterviewAttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InterviewAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'attempt_number',
        'status',
        'channel',
        'started_at',
        'ended_at',
        'duration_seconds',
        'failure_reason',
        'provider_reference',
        'metadata',
        'sequence',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'status' => InterviewAttemptStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(InterviewQuestion::class)
            ->orderBy('sequence');
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(InterviewEvaluation::class);
    }
}
