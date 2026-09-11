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
        'call_sid',
        'call_config_id',
        'recording_url',
        'transcript',
        'call_outcome',
        'poll_count',
        'last_polled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InterviewAttemptStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'last_polled_at' => 'datetime',
            'metadata' => 'array',
            'transcript' => 'array',
        ];
    }

    /**
     * Only the candidate's turns, in order.
     *
     * The bot's turns are the questions we wrote; scoring them would be
     * scoring our own script.
     *
     * @return array<int, array<string, mixed>>
     */
    public function candidateTurns(): array
    {
        return array_values(array_filter(
            $this->transcript ?? [],
            fn (array $turn) => strtoupper($turn['speaker'] ?? '') === 'HUMAN'
        ));
    }

    /**
     * Whether this attempt produced something worth evaluating.
     *
     * A call can be `completed` in Twilio's sense and contain nothing at all.
     */
    public function hasScreeningTranscript(): bool
    {
        return $this->candidateTurns() !== [];
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
