<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One "Analyze / Match Candidates" run. */
class AiMatchRun extends Model
{
    /** Queued, not yet picked up by a worker. */
    public const STATUS_QUEUED = 'queued';

    /** Parsing and scoring in flight. */
    public const STATUS_RUNNING = 'running';

    /** Every candidate scored; counters written; email sent. */
    public const STATUS_COMPLETED = 'completed';

    /** The run stopped before scoring finished. */
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'project_id',
        'user_id',
        'status',
        'batch_id',
        'candidates_total',
        'scored',
        'parse_failures',
        'matched',
        'needs_review',
        'threshold',
        'failure_reason',
        'started_at',
        'completed_at',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'candidates_total' => 'integer',
            'scored' => 'integer',
            'parse_failures' => 'integer',
            'matched' => 'integer',
            'needs_review' => 'integer',
            'threshold' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** The recruiter who started it, and who receives the summary. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }

    /** Candidates worth a recruiter's attention: matched plus needs-review. */
    public function actionable(): int
    {
        return $this->matched + $this->needs_review;
    }
}
