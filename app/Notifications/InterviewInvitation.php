<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The one email the candidate receives: you are shortlisted, here are three
 * times, pick one.
 *
 * Queued. Sending happens over SMTP to a third party, and a slow or failing
 * mail host must not hold up the request — or the transaction — that invited
 * the candidate.
 */
class InterviewInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public function __construct(
        public readonly Interview $interview,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Mail only. A database notification would put a link to a
        // token-gated page in a bell dropdown the candidate may never open,
        // and the task sheet is explicit that this arrives by email.
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->interview->loadMissing(['project', 'slots' => fn ($q) => $q->orderBy('position')]);

        $timezone = $this->interview->timezone
            ?: (string) config('services.interview.invitation.timezone', 'Asia/Tokyo');

        $minutes = max(1, (int) round(
            ((int) config('services.interview.duration_seconds', 300)) / 60
        ));

        return (new MailMessage)
            ->subject(__('interview.mail.subject', [
                'project' => $this->interview->project?->title ?? '',
            ]))
            ->markdown('mail.interviews.invitation', [
                'interview' => $this->interview,
                // Addressed by name. An unaddressed "You have been
                // shortlisted" reads as a mailshot, and a candidate who
                // decides it is one never opens the slot picker.
                'candidateName' => trim((string) ($notifiable->name ?? '')),
                'project' => $this->interview->project,
                'slots' => $this->interview->slots,
                'timezone' => $timezone,
                'minutes' => $minutes,
                'expiresAt' => $this->interview->invitation_expires_at,
                'url' => $this->interview->invitationUrl(),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'interview_id' => $this->interview->id,
            'project_id' => $this->interview->project_id,
        ];
    }
}
