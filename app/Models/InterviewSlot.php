<?php

namespace App\Models;

use App\Enums\InterviewSlotStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One time option presented to a candidate.
 *
 * Times are UTC in the column and rendered in the interview's timezone. Nothing
 * here converts on write — a slot that "means 10:00 in Tokyo" is stored as the
 * instant that is, because the instant is what we dial at.
 */
class InterviewSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'starts_at',
        'ends_at',
        'status',
        'position',
        'selected_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InterviewSlotStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'selected_at' => 'datetime',
        ];
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    public function scopeOffered(Builder $query): Builder
    {
        return $query->where('status', InterviewSlotStatus::OFFERED);
    }

    public function scopeSelected(Builder $query): Builder
    {
        return $query->where('status', InterviewSlotStatus::SELECTED);
    }

    /**
     * Still choosable: offered, and far enough in the future to be worth
     * dialling. A slot whose start has passed is not an option, whatever its
     * status column says.
     */
    public function isChoosable(): bool
    {
        return $this->status === InterviewSlotStatus::OFFERED
            && $this->starts_at->isFuture();
    }

    /**
     * Rendered for a human, in the timezone the invitation was issued in.
     *
     * The zone abbreviation is included on purpose: a bare "10:00" in an email
     * read on a phone in another country is an ambiguity the candidate has no
     * way to resolve, and a missed interview is the result.
     */
    public function presentIn(string $timezone): string
    {
        return $this->starts_at
            ->copy()
            ->setTimezone($timezone)
            ->translatedFormat('D, j M Y — H:i (T)');
    }

    public function startsAtIn(string $timezone): CarbonInterface
    {
        return $this->starts_at->copy()->setTimezone($timezone);
    }
}
