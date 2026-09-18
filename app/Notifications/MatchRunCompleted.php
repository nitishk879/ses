<?php

namespace App\Notifications;

use App\Models\AiMatch;
use App\Models\AiMatchRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** "30 candidates matched this JD" — the email that ends a matching run. */
class MatchRunCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    /** How many candidates the email itself names. */
    private const PREVIEW = 5;

    public function __construct(public readonly AiMatchRun $run)
    {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->run->loadMissing('project');

        $preview = AiMatch::query()
            ->with('talent.user:id,firstname,lastname')
            ->where('project_id', $this->run->project_id)
            ->where('score', '>=', $this->run->threshold)
            ->where('meets_mandatory', true)
            ->ranked()
            ->limit(self::PREVIEW)
            ->get();

        return (new MailMessage)
            ->subject(__('interview.match_run.mail.subject', [
                'count' => $this->run->matched,
                'project' => $this->run->project?->title ?? '',
            ]))
            ->markdown('mail.matches.completed', [
                'run' => $this->run,
                'project' => $this->run->project,
                'recruiterName' => trim((string) ($notifiable->name ?? '')),
                'preview' => $preview,
                // Everything beyond the names actually listed, so the email can
                // say "and 25 more" rather than implying five is the answer.
                'remaining' => max(0, $this->run->matched - $preview->count()),
                'url' => route('project-matches.index', $this->run->project_id),
            ]);
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'run_id' => $this->run->id,
            'project_id' => $this->run->project_id,
            'matched' => $this->run->matched,
        ];
    }
}
