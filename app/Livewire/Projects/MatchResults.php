<?php

namespace App\Livewire\Projects;

use App\Enums\RequirementKind;
use App\Jobs\ParseProjectJd;
use App\Jobs\ParseTalentResume;
use App\Jobs\ScoreProjectMatches;
use App\Models\AiMatch;
use App\Models\AiMatchRun;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\Talent;
use App\Services\InterviewInvitationService;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Throwable;

/** The one screen for CV matching: mark the must-haves, run the match, pick who to invite. */
class MatchResults extends Component
{
    use WithPagination;

    public Project $project;

    /** matched | review | all — which slice of the pool is on screen. */
    #[Url]
    public string $filter = 'matched';

    /** The score floor, or null when the recruiter has emptied the box. */
    #[Url]
    public ?int $threshold = 70;

    #[Url]
    public string $search = '';

    /**
     * Talent ids ticked on screen.
     *
     * @var array<int, int>
     */
    public array $selected = [];

    /** "Select all N matching", as distinct from "select the 15 on this page". */
    public bool $selectAllMatching = false;

    /** The three times offered to everyone in this batch. */
    public array $slotTimes = ['', '', ''];

    public int $perPage = 20;

    public function mount(Project $project): void
    {
        $this->assertVisible($project);
        $this->project = $project;

        $this->threshold = (int) config('services.interview.invitation.min_match_score', 70);
    }

    // ── guards ───────────────────────────────────────────────────────────── #

    /** Company scoping, applied on mount and again on every write. */
    private function assertVisible(?Project $project = null): void
    {
        $project ??= $this->project;

        abort_unless($project->isMatchableBy(auth()->user()), 403);
    }

    // ── requirements ─────────────────────────────────────────────────────── #

    /** @return \Illuminate\Support\Collection<int, ProjectRequirement> */
    #[Computed]
    public function requirements()
    {
        return ProjectRequirement::where('project_id', $this->project->id)
            ->ordered()
            ->get();
    }

    /** Flip one requirement between must-have and nice-to-have. */
    public function toggleMandatory(int $requirementId): void
    {
        $this->assertVisible();

        $requirement = $this->ownedRequirement($requirementId);

        $requirement->is_mandatory = ! $requirement->is_mandatory;
        $requirement->save();

        // Ticking a requirement changes who qualifies, so a selection made
        // against the old gate no longer means what the recruiter intended.
        $this->clearSelection();

        Log::info('ai.requirement.toggled', [
            'project_id' => $this->project->id,
            'requirement_id' => $requirement->id,
            'is_mandatory' => $requirement->is_mandatory,
            'by' => auth()->id(),
        ]);

        unset($this->requirements);
    }

    /** Set the year threshold on an experience requirement. */
    public function setExperienceYears(int $requirementId, int $years): void
    {
        $this->assertVisible();

        $requirement = $this->ownedRequirement($requirementId);

        abort_unless($requirement->kind === RequirementKind::EXPERIENCE, 422);

        $years = max(0, min(40, $years));
        $requirement->min_months = $years * 12;
        $requirement->label = __('interview.requirement.experience_label', ['years' => $years]);
        $requirement->save();

        $this->clearSelection();

        unset($this->requirements);
    }

    /** A requirement belonging to *this* project. */
    private function ownedRequirement(int $requirementId): ProjectRequirement
    {
        return ProjectRequirement::where('project_id', $this->project->id)
            ->where('id', $requirementId)
            ->firstOrFail();
    }

    // ── the run ──────────────────────────────────────────────────────────── #

    /** The run whose state the page is reporting. */
    #[Computed]
    public function latestRun(): ?AiMatchRun
    {
        return AiMatchRun::where('project_id', $this->project->id)
            ->latestFirst()
            ->first();
    }

    /** Whether the stored scores predate the current set of must-haves. */
    #[Computed]
    public function gateIsStale(): bool
    {
        $run = $this->latestRun();

        if (! $run || ! $run->completed_at) {
            return false;
        }

        return ProjectRequirement::where('project_id', $this->project->id)
            ->where('updated_at', '>', $run->completed_at)
            ->exists();
    }

    /** "Analyze / Match Candidates". */
    public function analyze(): void
    {
        $this->assertVisible();

        $running = AiMatchRun::where('project_id', $this->project->id)
            ->whereIn('status', [AiMatchRun::STATUS_QUEUED, AiMatchRun::STATUS_RUNNING])
            ->exists();

        if ($running) {
            // Pressing twice must not double the work or produce two emails.
            $this->dispatch('notify', type: 'info', message: __('interview.match_run.already_running'));

            return;
        }

        $run = AiMatchRun::create([
            'project_id' => $this->project->id,
            'user_id' => auth()->id(),
            'status' => AiMatchRun::STATUS_QUEUED,
            'threshold' => $this->scoreFloor(),
            'candidates_total' => Talent::count(),
            'started_at' => now(),
        ]);

        // Every candidate, not only those with a CV file.
        $jobs = [];

        Talent::query()->select('id')->chunkById(500, function ($talents) use (&$jobs) {
            foreach ($talents as $talent) {
                $jobs[] = new ParseTalentResume($talent->id);
            }
        });

        // The JD goes in the same batch so scoring cannot start against a
        // stale structure of the posting.
        $jobs[] = new ParseProjectJd($this->project->id);

        $projectId = $this->project->id;
        $runId = $run->id;

        $batch = Bus::batch($jobs)
            ->name("ai-match:{$projectId}:run:{$runId}")
            // One unreadable CV must not abandon the pool.
            ->allowFailures()
            ->finally(function (Batch $batch) use ($projectId, $runId) {
                // `finally`, not `then`: with allowFailures() a batch that had
                // any failure never reaches `then`, and the pool still needs
                // scoring and the recruiter still needs telling.
                AiMatchRun::where('id', $runId)->update([
                    'parse_failures' => $batch->failedJobs,
                ]);

                ScoreProjectMatches::dispatch($projectId, false, $runId);
            })
            ->dispatch();

        // Written as two conditional updates rather than a save() on `$run`.
        AiMatchRun::where('id', $run->id)->update(['batch_id' => $batch->id]);

        AiMatchRun::where('id', $run->id)
            ->where('status', AiMatchRun::STATUS_QUEUED)
            ->update(['status' => AiMatchRun::STATUS_RUNNING]);

        $this->clearSelection();
        unset($this->latestRun, $this->gateIsStale);

        Log::info('ai.match_run.started', [
            'run_id' => $run->id,
            'project_id' => $projectId,
            'candidates' => count($jobs) - 1,
            'mandatory' => $this->requirements()->where('is_mandatory', true)->count(),
            'by' => auth()->id(),
        ]);

        $this->dispatch('notify', type: 'info', message: __('interview.match_run.started', [
            'count' => count($jobs) - 1,
        ]));
    }

    // ── results ──────────────────────────────────────────────────────────── #

    /** The ranked pool, filtered to the slice the recruiter is looking at. */
    private function baseQuery(): Builder
    {
        $query = AiMatch::query()
            ->with(['talent.user:id,firstname,lastname,email'])
            ->where('project_id', $this->project->id);

        match ($this->filter) {
            'matched' => $query
                ->where('score', '>=', $this->scoreFloor())
                ->where('meets_mandatory', true),
            'review' => $query
                ->where('score', '>=', $this->scoreFloor())
                ->where('meets_mandatory', false)
                ->where('unverified_mandatory', '>', 0),
            default => $query,
        };

        if (filled($this->search)) {
            $term = '%'.$this->search.'%';
            $query->whereHas('talent.user', fn (Builder $q) => $q
                ->where('firstname', 'like', $term)
                ->orWhere('lastname', 'like', $term)
                ->orWhere('email', 'like', $term));
        }

        return $query->ranked();
    }

    /** Counts for the three filter tabs — one grouped query, not three. */
    #[Computed]
    public function tallies(): array
    {
        $row = AiMatch::query()
            ->where('project_id', $this->project->id)
            ->selectRaw('COUNT(*) as all_scored')
            ->selectRaw(
                'SUM(CASE WHEN score >= ? AND meets_mandatory = 1 THEN 1 ELSE 0 END) as matched',
                [$this->scoreFloor()]
            )
            ->selectRaw(
                'SUM(CASE WHEN score >= ? AND meets_mandatory = 0 '
                .'AND unverified_mandatory > 0 THEN 1 ELSE 0 END) as review',
                [$this->scoreFloor()]
            )
            ->first();

        return [
            'all' => (int) ($row->all_scored ?? 0),
            'matched' => (int) ($row->matched ?? 0),
            'review' => (int) ($row->review ?? 0),
        ];
    }

    /**
     * Talent ids this action applies to.
     *
     * @return array<int, int>
     */
    private function selectedIds(): array
    {
        if ($this->selectAllMatching) {
            return $this->baseQuery()->pluck('talent_id')->all();
        }

        return array_values(array_unique(array_map('intval', $this->selected)));
    }

    #[Computed]
    public function selectionCount(): int
    {
        return $this->selectAllMatching
            ? $this->tallies()[$this->filter] ?? $this->tallies()['all']
            : count($this->selected);
    }

    /** Tick or untick every row on the current page. */
    public function togglePage(bool $checked): void
    {
        $ids = $this->results()->pluck('talent_id')->map(fn ($id) => (int) $id)->all();

        $this->selected = $checked
            ? array_values(array_unique([...$this->selected, ...$ids]))
            : array_values(array_diff($this->selected, $ids));

        // Unticking anything means the recruiter no longer means "all N".
        if (! $checked) {
            $this->selectAllMatching = false;
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAllMatching = false;
    }

    /** Changing the slice invalidates a selection made against the old one. */
    public function updatedFilter(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedThreshold(): void
    {
        if ($this->threshold !== null) {
            $this->threshold = max(0, min(100, $this->threshold));
        }

        $this->clearSelection();
        $this->resetPage();
        unset($this->tallies);
    }

    /** The score floor actually applied to every query on this page. */
    #[Computed]
    public function scoreFloor(): int
    {
        return max(0, min(100, $this->threshold ?? 0));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // ── invite ───────────────────────────────────────────────────────────── #

    /** Invite everyone ticked, offering the three chosen times. */
    public function inviteSelected(InterviewInvitationService $invitations): void
    {
        $this->assertVisible();

        if (blank($this->project->interview_agent_id)) {
            // The same rule the interview dashboard enforces: the questions
            // live in the bot's prompt, so an interview without one asks the
            // wrong things.
            $this->dispatch('notify', type: 'warning', message: __('interview.dashboard.bot_required'));

            return;
        }

        $slotTimes = array_values(array_filter($this->slotTimes, 'filled'));

        if (count($slotTimes) !== 3) {
            $this->dispatch('notify', type: 'warning', message: __('interview.dashboard.slot_times_required'));

            return;
        }

        $ids = $this->selectedIds();

        if ($ids === []) {
            $this->dispatch('notify', type: 'warning', message: __('interview.match_run.nothing_selected'));

            return;
        }

        $scores = AiMatch::where('project_id', $this->project->id)
            ->whereIn('talent_id', $ids)
            ->pluck('score', 'talent_id');

        $sent = 0;
        $failures = [];

        foreach ($ids as $talentId) {
            $talent = Talent::with('user')->find($talentId);

            if (! $talent) {
                continue;
            }

            try {
                $invitations->invite(
                    $this->project,
                    $talent,
                    (int) ($scores[$talentId] ?? 0),
                    $slotTimes,
                );
                $sent++;
            } catch (\InvalidArgumentException $e) {
                // A bad time is wrong for the whole batch, not for one person,
                // so it stops here instead of repeating once per candidate.
                $this->dispatch('notify', type: 'danger', message: $e->getMessage());

                return;
            } catch (RuntimeException $e) {
                // No email, or no dialable phone. One unreachable candidate
                // must not abandon the rest.
                $failures[] = ($talent->user?->name ?: "#{$talent->id}").' — '.$e->getMessage();
            } catch (Throwable $e) {
                $failures[] = ($talent->user?->name ?: "#{$talent->id}").' — '.$e->getMessage();
                Log::error('interview.invite_failed', [
                    'project_id' => $this->project->id,
                    'talent_id' => $talentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->clearSelection();

        $this->dispatch(
            'notify',
            type: $failures === [] ? 'success' : 'warning',
            message: $failures === []
                ? __('interview.dashboard.invited', ['count' => $sent])
                : __('interview.dashboard.invited_with_failures', [
                    'count' => $sent,
                    'failures' => implode('; ', array_slice($failures, 0, 3)),
                ]),
        );
    }

    // ── render ───────────────────────────────────────────────────────────── #

    public function results()
    {
        return $this->baseQuery()->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.projects.match-results', [
            'results' => $this->results(),
            // Talents already invited, so a row can say so instead of offering
            // to invite somebody twice.
            'invitedIds' => $this->project->interviews()
                ->pluck('talent_id')
                ->map(fn ($id) => (int) $id)
                ->all(),
        ]);
    }
}
