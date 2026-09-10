<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_question_id',
        'answer_text',
        'transcript',
        'audio_path',
        'audio_duration_seconds',
        'started_at',
        'ended_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function interviewQuestion(): BelongsTo
    {
        return $this->belongsTo(InterviewQuestion::class);
    }
}
