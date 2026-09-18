<?php

namespace App\Models;

use App\Enums\InterviewEvaluationStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_attempt_id',
        'digest_id',
        'status',
        'technical_fit',
        'jd_fit',
        'communication',
        'overall_score',
        'summary',
        'strengths',
        'gaps',
        'evidence',
        'recommendation',
        'provider',
        'model',
        'prompt_version',
        'evaluated_at',
        'failure_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => InterviewEvaluationStatusEnum::class,

            'technical_fit' => 'decimal:2',
            'jd_fit' => 'decimal:2',
            'communication' => 'decimal:2',
            'overall_score' => 'decimal:2',

            'strengths' => 'array',
            'gaps' => 'array',
            'evidence' => 'array',
            'metadata' => 'array',

            'evaluated_at' => 'datetime',
        ];
    }

    public function interviewAttempt(): BelongsTo
    {
        return $this->belongsTo(
            InterviewAttempt::class,
            'interview_attempt_id'
        );
    }

    /** Alias for the relation above, for call sites that read better short. */
    public function attempt(): BelongsTo
    {
        return $this->interviewAttempt();
    }

    /** The digest email this evaluation was counted in, if any. */
    public function digest(): BelongsTo
    {
        return $this->belongsTo(InterviewEvaluationDigest::class, 'digest_id');
    }

    /** Whether this candidate is one the recruiter should take forward. */
    public function isRecommended(): bool
    {
        return $this->recommendation === 'recommended';
    }
}
