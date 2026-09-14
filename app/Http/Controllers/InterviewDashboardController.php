<?php

namespace App\Http\Controllers;

use App\Enums\InterviewStatus;
use App\Jobs\ParseProjectJd;
use App\Jobs\ParseTalentResume;
use App\Models\Interview;
use App\Models\Project;
use App\Models\Talent;
use App\Services\InterviewInvitationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Task 16: the screens a recruiter actually works from.
 *
 * Everything this feature does was already reachable — through artisan
 * commands and a set of JSON endpoints — which is to say it was reachable by
 * an engineer with an SSH key and by nobody else. These pages put the same
 * operations in front of the person whose job it is to run them.
 *
 * Deliberately separate from {@see InterviewController}: that one serves JSON
 * to whatever consumes the API, and mixing HTML rendering into it would make
 * two contracts out of one class. Nothing here changes those endpoints.
 *
 * **Scoping is the load-bearing part.** An interview record carries a
 * candidate's transcript and an assessment of them. One employer seeing
 * another's is not an inconvenience, it is a data breach, so every query in
 * this class goes through {@see scopeToViewer()} rather than trusting the id
 * in the URL.
 */
class InterviewDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'max:40'],
            'project' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $interviews = $this->scopeToViewer(
            Interview::query()->with([
                'project:id,title',
                'talent.user:id,firstname,lastname,email',
                // One attempt per interview, via a correlated subquery rather
                // than a limited eager load — see Interview::latestAttempt().
                'latestAttempt.evaluation',
            ])
        );

        if (! empty($filters['status'])) {
            $interviews->where('status', $filters['status']);
        }

        if (! empty($filters['project'])) {
            $interviews->where('project_id', $filters['project']);
        }

        if (! empty($filters['q'])) {
            // Search the candidate, which is what a recruiter has in mind —
            // they remember a person, not an interview id.
            $term = '%'.$filters['q'].'%';
            $interviews->whereHas('talent.user', fn (Builder $q) => $q
                ->where('firstname', 'like', $term)
                ->orWhere('lastname', 'like', $term)
                ->orWhere('email', 'like', $term));
        }

        return view('interviews.dashboard.index', [
            'interviews' => $interviews
                ->orderByRaw('COALESCE(scheduled_at, created_at) DESC')
                ->paginate(15)
                ->withQueryString(),
            'projects' => $this->viewableProjects(),
            'statuses' => InterviewStatus::cases(),
            'filters' => $filters,
            'summary' => $this->summary(),
        ]);
    }

    public function show(Interview $interview): View
    {
        $this->authorizeInterview($interview);

        $interview->load([
            'project',
            'talent.user',
            'slots',
            'attempts' => fn ($q) => $q->orderByDesc('attempt_number'),
            'attempts.questions.answer',
            'attempts.evaluation',
        ]);

        return view('interviews.dashboard.show', [
            'interview' => $interview,
            // The attempt a reader means when they say "the interview": the
            // most recent one that actually produced a transcript, falling
            // back to the most recent of any kind.
            'attempt' => $interview->attempts->firstWhere(
                fn ($a) => $a->hasScreeningTranscript()
            ) ?? $interview->attempts->first(),
            'timezone' => $interview->timezone
                ?: (string) config('services.interview.invitation.timezone', 'Asia/Tokyo'),
        ]);
    }

    /**
     * Queue a re-parse and re-score of one project's candidate pool.
     *
     * Replaces `php artisan ses:ai-match`. Queued rather than run inline: this
     * is one language-model call per unparsed CV, and a recruiter should not
     * be staring at a spinner for two minutes to find out whether it worked.
     */
    public function runMatching(Project $project): RedirectResponse
    {
        $this->authorizeProject($project);

        // One hash-guarded job per candidate.
        //
        // Deliberately not filtered. The two obvious filters are both wrong:
        //
        // * filtering on `resume` skipped every candidate without a CV file,
        //   which is most of them — and they are parseable from the profile
        //   they filled in ({@see AiParsingService::resumeSource()}). This is
        //   why the score column was mostly empty;
        // * filtering on "has no parse row yet" skips a candidate whose stored
        //   parse came from a CV that has since been deleted or replaced. Their
        //   score then stays frozen at an answer derived from a document that
        //   no longer exists, and no amount of pressing this button fixes it.
        //
        // Dispatching for everyone is safe because {@see ParseTalentResume}
        // compares a content hash before calling the model: an unchanged
        // candidate costs one cheap hash and returns. The expensive thing is
        // the language model, and that is guarded where it belongs rather than
        // by a query here that cannot see whether the source changed.
        $queued = 0;

        Talent::query()->select('id')->chunkById(500, function ($talents) use (&$queued) {
            foreach ($talents as $talent) {
                ParseTalentResume::dispatch($talent->id);
                $queued++;
            }
        });

        // Parsing the JD chains into scoring on completion, so this is the
        // only other job needed.
        ParseProjectJd::dispatch($project->id);

        Log::info('interview.matching_queued', [
            'project_id' => $project->id,
            'resumes_queued' => $queued,
            'by' => auth()->id(),
        ]);

        return back()->with([
            'message' => __('interview.dashboard.matching_queued', ['count' => $queued]),
            'type' => 'info',
        ]);
    }

    /**
     * Invite everyone on this project's shortlist.
     *
     * Replaces `php artisan interviews:invite`. Runs inline rather than
     * queued, because a recruiter pressing "Invite" is entitled to be told
     * immediately how many people it reached — and the slow part (the email)
     * is queued inside the notification anyway.
     */
    public function invite(Request $request, Project $project, InterviewInvitationService $invitations): RedirectResponse
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'threshold' => ['nullable', 'integer', 'min:0', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $threshold = $validated['threshold']
            ?? (int) config('services.interview.invitation.min_match_score', 70);

        $shortlist = $invitations->shortlistFor($project, $threshold)
            ->take($validated['limit'] ?? 25);

        if ($shortlist->isEmpty()) {
            return back()->with([
                'message' => __('interview.dashboard.no_shortlist', ['threshold' => $threshold]),
                'type' => 'warning',
            ]);
        }

        $sent = 0;
        $failures = [];

        foreach ($shortlist as $match) {
            $talent = Talent::with('user')->find($match->talent_id);

            if (! $talent) {
                continue;
            }

            try {
                $invitations->invite($project, $talent, (int) $match->score);
                $sent++;
            } catch (RuntimeException $e) {
                // One unreachable candidate must not abandon the shortlist;
                // the recruiter is told which ones need attention.
                $failures[] = ($talent->user?->name ?: "talent #{$talent->id}").' — '.$e->getMessage();
            } catch (Throwable $e) {
                $failures[] = ($talent->user?->name ?: "talent #{$talent->id}").' — '.$e->getMessage();
                Log::error('interview.invite_failed', [
                    'project_id' => $project->id,
                    'talent_id' => $talent->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with([
            'message' => $failures === []
                ? __('interview.dashboard.invited', ['count' => $sent])
                : __('interview.dashboard.invited_with_failures', [
                    'count' => $sent,
                    'failures' => implode('; ', array_slice($failures, 0, 3)),
                ]),
            'type' => $failures === [] ? 'success' : 'warning',
        ]);
    }

    // ── scoping ──────────────────────────────────────────────────────────── #

    /**
     * Narrow a query to the interviews this viewer is entitled to see.
     *
     * Admins see everything. Everyone else sees only interviews for their own
     * company's projects — an interview holds a named person's transcript and
     * an assessment of them, so the default has to be closed.
     */
    private function scopeToViewer(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user?->hasRole('admin')) {
            return $query;
        }

        $companyId = $user?->company?->id;

        if (! $companyId) {
            // No company means no projects, which means no interviews. Return
            // a query that matches nothing rather than one that matches all.
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('project', fn (Builder $q) => $q->where('company_id', $companyId));
    }

    private function authorizeInterview(Interview $interview): void
    {
        $user = auth()->user();

        if ($user?->hasRole('admin')) {
            return;
        }

        abort_unless(
            $interview->project?->company_id
                && $interview->project->company_id === $user?->company?->id,
            403
        );
    }

    private function authorizeProject(Project $project): void
    {
        $user = auth()->user();

        if ($user?->hasRole('admin')) {
            return;
        }

        abort_unless($project->company_id === $user?->company?->id, 403);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Project>
     */
    private function viewableProjects()
    {
        $user = auth()->user();

        $query = Project::query()->orderByDesc('created_at');

        if (! $user?->hasRole('admin')) {
            $companyId = $user?->company?->id;

            if (! $companyId) {
                return collect();
            }

            $query->where('company_id', $companyId);
        }

        return $query->get(['id', 'title', 'company_id']);
    }

    /**
     * Counts for the strip at the top of the list.
     *
     * One grouped query rather than one per status: the page already paginates
     * the interviews themselves, and a dashboard that issues a dozen COUNTs to
     * draw its header is a dashboard that gets slower as the product succeeds.
     *
     * @return array<string, int>
     */
    private function summary(): array
    {
        $counts = $this->scopeToViewer(Interview::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $awaiting = (int) ($counts[InterviewStatus::SLOT_SELECTION->value] ?? 0)
            + (int) ($counts[InterviewStatus::INVITED->value] ?? 0);

        return [
            'total' => (int) $counts->sum(),
            'awaiting_reply' => $awaiting,
            'scheduled' => (int) ($counts[InterviewStatus::SCHEDULED->value] ?? 0),
            'completed' => (int) ($counts[InterviewStatus::COMPLETED->value] ?? 0)
                + (int) ($counts[InterviewStatus::EVALUATED->value] ?? 0),
            'needs_attention' => (int) ($counts[InterviewStatus::NO_ANSWER->value] ?? 0)
                + (int) ($counts[InterviewStatus::FAILED->value] ?? 0)
                + (int) ($counts[InterviewStatus::RESCHEDULE_REQUIRED->value] ?? 0),
        ];
    }
}
