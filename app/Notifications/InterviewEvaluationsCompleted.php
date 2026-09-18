<?php

namespace App\Notifications;

use App\Models\InterviewEvaluation;
use App\Models\InterviewEvaluationDigest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** "The AI interview evaluation has been completed for N candidates." */
class InterviewEvaluationsCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    /** How many candidates the email itself names. */
    private const PREVIEW = 5;

    public function __construct(public readonly InterviewEvaluationDigest $digest)
    {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->digest->loadMissing('project');

        $preview = InterviewEvaluation::query()
            ->with('attempt.interview.talent.user:id,firstname,lastname')
            ->where('digest_id', $this->digest->id)
            ->orderByDesc('overall_score')
            ->limit(self::PREVIEW)
            ->get();

        return (new MailMessage)
            ->subject(__('interview.evaluation_digest.mail.subject', [
                'count' => $this->digest->evaluated,
                'project' => $this->digest->project?->title ?? '',
            ]))
            ->markdown('mail.interviews.evaluations-completed', [
                'digest' => $this->digest,
                'project' => $this->digest->project,
                'recruiterName' => trim((string) ($notifiable->name ?? '')),
                'preview' => $preview,
                'remaining' => max(0, $this->digest->evaluated - $preview->count()),
                'url' => route('interview-dashboard.index', [
                    'project' => $this->digest->project_id,
                ]),
            ]);
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'digest_id' => $this->digest->id,
            'project_id' => $this->digest->project_id,
            'evaluated' => $this->digest->evaluated,
        ];
    }
}
