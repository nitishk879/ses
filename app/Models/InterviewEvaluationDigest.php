<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A window of completed interview evaluations, and the one email about them. */
class InterviewEvaluationDigest extends Model
{
    /** Accepting evaluations; the closing job is queued. */
    public const STATUS_OPEN = 'open';

    /** Counted and emailed. */
    public const STATUS_SENT = 'sent';

    /** Closed with nothing in it, or with nobody to write to. */
    public const STATUS_EMPTY = 'empty';

    protected $fillable = [
        'project_id',
        'user_id',
        'status',
        'closes_at',
        'evaluated',
        'recommended',
        'notified_at',
        'closed_at',
        'open_key',
    ];

    protected function casts(): array
    {
        return [
            'closes_at' => 'datetime',
            'notified_at' => 'datetime',
            'closed_at' => 'datetime',
            'evaluated' => 'integer',
            'recommended' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** The recruiter who receives the summary. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The evaluations assigned to this window. */
    public function evaluations(): HasMany
    {
        return $this->hasMany(InterviewEvaluation::class, 'digest_id');
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
