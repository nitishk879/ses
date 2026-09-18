<?php

namespace App\Services;

use App\Jobs\CloseEvaluationDigest;
use App\Models\InterviewEvaluation;
use App\Models\InterviewEvaluationDigest;
use App\Models\Project;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

/** Collect completed evaluations into one "N candidates evaluated" email. */
class InterviewEvaluationDigestService
{
    /** Attach a freshly completed evaluation to a digest. */
    public function record(InterviewEvaluation $evaluation): ?InterviewEvaluationDigest
    {
        if ($evaluation->digest_id !== null) {
            return $evaluation->digest;
        }

        $project = $evaluation->attempt?->interview?->project;

        if (! $project) {
            // An evaluation with no project behind it cannot be addressed to
            // anyone. Not an error worth failing the evaluation over.
            Log::info('interview.digest.no_project', ['evaluation_id' => $evaluation->id]);

            return null;
        }

        // Bounded retry, because joining a window races against closing it.
        foreach (range(1, 2) as $attempt) {
            [$digest, $opened] = $this->openDigestFor($project);

            $joined = InterviewEvaluation::query()
                ->where('id', $evaluation->id)
                ->whereNull('digest_id')
                ->update(['digest_id' => $digest->id]);

            if ($joined && $digest->refresh()->isOpen()) {
                $evaluation->digest_id = $digest->id;

                Log::info('interview.digest.recorded', [
                    'digest_id' => $digest->id,
                    'evaluation_id' => $evaluation->id,
                    'project_id' => $project->id,
                ]);

                // The closing job is queued last: after the window exists, and after this evaluation is inside it.
                if ($opened) {
                    CloseEvaluationDigest::dispatch($digest->id)->delay($digest->closes_at);
                }

                return $digest;
            }

            // Closed underneath us. Release the claim and try the next window.
            InterviewEvaluation::query()
                ->where('id', $evaluation->id)
                ->where('digest_id', $digest->id)
                ->update(['digest_id' => null]);

            Log::info('interview.digest.join_closed_underneath', [
                'digest_id' => $digest->id,
                'evaluation_id' => $evaluation->id,
                'attempt' => $attempt,
            ]);
        }

        // Both attempts lost. The evaluation is stored and visible on the
        // dashboard; only its line in a digest email is missing, which is not
        // worth failing the evaluation over.
        Log::warning('interview.digest.join_abandoned', [
            'evaluation_id' => $evaluation->id,
            'project_id' => $project->id,
        ]);

        return null;
    }

    /**
     * The project's live window, opening one if there is none.
     *
     * @return array{0: InterviewEvaluationDigest, 1: bool} the window, and
     */
    private function openDigestFor(Project $project): array
    {
        if ($existing = $this->liveDigestFor($project)) {
            return [$existing, false];
        }

        $minutes = max(1, (int) config('services.interview.evaluation_digest_minutes', 20));

        try {
            $digest = InterviewEvaluationDigest::create([
                'project_id' => $project->id,
                // Whoever owns the posting is who hears about it, matching the
                // CV-matching summary.
                'user_id' => $project->user_id,
                'status' => InterviewEvaluationDigest::STATUS_OPEN,
                'closes_at' => now()->addMinutes($minutes),
                'open_key' => $project->id,
            ]);
        } catch (QueryException $e) {
            // Lost the race. The winner's row is the answer.
            $winner = $this->liveDigestFor($project);

            if (! $winner) {
                throw $e; // a real failure, not contention
            }

            Log::info('interview.digest.join_race_lost', [
                'project_id' => $project->id,
                'digest_id' => $winner->id,
            ]);

            return [$winner, false];
        }

        Log::info('interview.digest.opened', [
            'digest_id' => $digest->id,
            'project_id' => $project->id,
            'closes_at' => $digest->closes_at->toIso8601String(),
        ]);

        // The window is closed by a delayed job rather than by a scheduler sweep.
        return [$digest, true];
    }

    /** This project's open digest, if it has one. */
    private function liveDigestFor(Project $project): ?InterviewEvaluationDigest
    {
        return InterviewEvaluationDigest::query()
            ->where('project_id', $project->id)
            ->where('status', InterviewEvaluationDigest::STATUS_OPEN)
            ->first();
    }
}
