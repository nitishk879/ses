<?php

namespace App\Models;

use App\Enums\InterviewQuestionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InterviewQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_attempt_id',
        'sequence',
        'type',
        'skill_area',
        'question_text',
        'source',
        'asked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => InterviewQuestionTypeEnum::class,
            'asked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function interviewAttempt(): BelongsTo
    {
        return $this->belongsTo(InterviewAttempt::class);
    }

    public function answer(): HasOne
    {
        return $this->hasOne(InterviewAnswer::class);
    }
}
