<?php

namespace App\Models;

use App\Enums\InterviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'invitation_token',
        'invitation_sent_at',
        'invitation_expires_at',
        'slot_selected_at',
        'match_score',
        // `attempt_number` is deliberately absent: it belongs to
        // `interview_attempts` and there is no such column on `interviews`.
        // Listing it here made any create()/update() that happened to carry the
        // key fail with an "Unknown column" SQL error at runtime.
    ];

    /**
     * The invitation token never leaves the server except inside the email.
     *
     * Hidden so it cannot be leaked by a `toJson()` on any of the interview
     * API endpoints — one of which a recruiter can call, and none of which
     * should hand out a credential that books somebody else's interview.
     *
     * @var array<int, string>
     */
    protected $hidden = ['invitation_token'];

    protected function casts(): array
    {
        return [
            'status' => InterviewStatus::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'invitation_sent_at' => 'datetime',
            'invitation_expires_at' => 'datetime',
            'slot_selected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function slots(): HasMany
    {
        return $this->hasMany(InterviewSlot::class)->orderBy('starts_at');
    }

    public function selectedSlot(): HasOne
    {
        return $this->hasOne(InterviewSlot::class)
            ->where('status', \App\Enums\InterviewSlotStatus::SELECTED);
    }

    /**
     * The link posted to the candidate.
     *
     * Built from the named route so it follows APP_URL — which is why APP_URL
     * being wrong is not cosmetic here: a `http://localhost` base produces a
     * dead link in a real person's inbox.
     */
    public function invitationUrl(): ?string
    {
        return filled($this->invitation_token)
            ? route('interview-slots.show', ['token' => $this->invitation_token])
            : null;
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

    /**
     * The most recent call attempt, as a single relation.
     *
     * Exists so a list view can eager-load one attempt per interview. The
     * obvious alternative — `with(['attempts' => fn ($q) => $q->limit(1)])` —
     * is a trap: Laravel 11 can apply that per parent, but only by emitting a
     * window function, which ties a page to what the database engine happens
     * to support. `latestOfMany()` is a plain correlated subquery and works
     * everywhere, which for a screen a recruiter opens all day is the right
     * trade.
     */
    public function latestAttempt(): HasOne
    {
        return $this->hasOne(InterviewAttempt::class)->latestOfMany('attempt_number');
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
