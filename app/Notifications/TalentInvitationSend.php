<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TalentInvitationSend extends Notification
{
    use Queueable;

    public Project $project;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->prefers_sms ? ['vonage'] : ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->markdown('mail.invitations.invite-talent', ['project' => $this->project])
            ->subject(__("projects/invitation.subject", ["project" => $this->project->title]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'notification_title' => __("projects/invitation.notification_title"),
            'notification_body' => __("projects/invitation.notification_body", ["project" => $this->project->title]),
        ];
    }
}
