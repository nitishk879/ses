<?php

namespace App\Jobs;

use App\Models\InterviewEvaluation;
use App\Models\InterviewEvaluationDigest;
use App\Notifications\InterviewEvaluationsCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Shut an evaluation window, count what is in it, and email the recruiter. */
class CloseEvaluationDigest implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public function __construct(public int $digestId)
    {
    }

    public function handle(): void
    {
        // The flip is the barrier, and it happens before anything is counted.
        $closed = DB::table('interview_evaluation_digests')
            ->where('id', $this->digestId)
            ->where('status', InterviewEvaluationDigest::STATUS_OPEN)
            ->update([
                'status' => InterviewEvaluationDigest::STATUS_SENT,
                'open_key' => null,
                'closed_at' => now(),
                'updated_at' => now(),
            ]);

        if ($closed === 0) {
            Log::info('interview.digest.already_closed', ['digest_id' => $this->digestId]);

            return;
        }

        $digest = InterviewEvaluationDigest::with(['project', 'user'])->find($this->digestId);

        if (! $digest) {
            return;
        }

        $evaluations = InterviewEvaluation::query()
            ->where('digest_id', $digest->id)
            ->get();

        $digest->forceFill([
            'evaluated' => $evaluations->count(),
            // A recruiter's first question is "how many are worth taking
            // forward", so the headline count is paired with this one rather
            // than leaving them to open the dashboard to find out.
            'recommended' => $evaluations
                ->filter(fn (InterviewEvaluation $e) => $e->isRecommended())
                ->count(),
        ])->save();

        if ($evaluations->isEmpty()) {
            // The window opened and everything in it was later detached or
            // deleted. Nothing to say, so nothing is sent.
            $digest->forceFill(['status' => InterviewEvaluationDigest::STATUS_EMPTY])->save();

            Log::info('interview.digest.closed_empty', ['digest_id' => $digest->id]);

            return;
        }

        if (! $digest->user || blank($digest->user->email)) {
            $digest->forceFill(['status' => InterviewEvaluationDigest::STATUS_EMPTY])->save();

            Log::info('interview.digest.no_recipient', ['digest_id' => $digest->id]);

            return;
        }

        $digest->forceFill(['notified_at' => now()])->save();

        $digest->user->notify(new InterviewEvaluationsCompleted($digest));

        Log::info('interview.digest.notified', [
            'digest_id' => $digest->id,
            'project_id' => $digest->project_id,
            'evaluated' => $digest->evaluated,
            'recommended' => $digest->recommended,
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('interview.digest.close_failed', [
            'digest_id' => $this->digestId,
            'error' => $e->getMessage(),
        ]);
    }
}
