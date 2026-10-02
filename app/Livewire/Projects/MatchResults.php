<?php

namespace App\Livewire\Projects;

use App\Jobs\ParseProjectJd;
use App\Jobs\ParseTalentResume;
use App\Jobs\ScoreProjectMatches;
use App\Models\AiMatch;
use App\Models\AiMatchRun;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\Talent;
use App\Services\InterviewAiService;
use App\Services\InterviewInvitationService;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
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

    /** The DenAI bot picked in the box, which is not yet the one that is saved. */
    public string $interviewAgentId = '';

    public int $perPage = 20;

    /**
     * The DenAI bot directory, shared by every project — it is one account's
     * list of bots, not one project's, so the key carries no project id.
     */
    private const BOT_DIRECTORY_CACHE_KEY = 'interview.bot_directory';

    private const BOT_DIRECTORY_TTL = 300;

    /** Short, so a recovered service is picked up in seconds. */
    private const BOT_DIRECTORY_FAILURE_TTL = 20;

    public function mount(Project $project): void
    {
        $this->assertVisible($project);
        $this->project = $project;

        $this->threshold = (int) config('services.interview.invitation.min_match_score', 70);
        $this->interviewAgentId = (string) ($project->interview_agent_id ?? '');
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

    /*
     * There is no action here for editing a requirement's value.
     *
     * The years of experience used to be typed on this screen and stored only
     * on the requirement row, so the matching screen could show a requirement
     * the project itself did not have. Values now live on the project form
     * (`projects.min_experience_months` for experience) and this screen only
     * decides which of them are must-haves.
     */

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

        // Minus the rows whose own checkbox is disabled. Otherwise "select
        // page" ticks candidates the recruiter cannot tick one at a time, and
        // they render as checked-but-disabled while Send passes over them.
        $ids = array_values(array_diff(
            $ids,
            app(InterviewInvitationService::class)->skippableTalentIds($this->project, $ids),
        ));

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

    // ── bot ──────────────────────────────────────────────────────────────── #

    /**
     * The bots this account has on the DenAI dashboard.
     *
     * Empty means the directory was unreachable, which the view says out loud
     * rather than rendering as "no bots exist" — that would send a recruiter
     * off to create one that is already there.
     *
     * Cached across requests, not merely memoized within one. A plain
     * #[Computed] lasts a single request, and on a Livewire page every tick of
     * a checkbox and every keystroke in the search box *is* a request — so the
     * directory would be fetched over HTTP dozens of times while a recruiter
     * builds a shortlist, and a slow DenAI would be felt on every click. Bots
     * are authored by hand and change on the order of weeks.
     *
     * Written by hand rather than with `#[Computed(persist: true)]` for one
     * reason: that caches whatever comes back, and what comes back from an
     * unreachable service is `[]`. A single timeout would then pin the picker
     * to its free-text fallback for the full window, long after the service
     * recovered. A failure is cached too — otherwise an outage restores the
     * per-keystroke HTTP call — but for seconds rather than minutes.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function bots(): array
    {
        $cached = Cache::get(self::BOT_DIRECTORY_CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        $bots = app(InterviewAiService::class)->agents();

        Cache::put(
            self::BOT_DIRECTORY_CACHE_KEY,
            $bots,
            $bots === [] ? self::BOT_DIRECTORY_FAILURE_TTL : self::BOT_DIRECTORY_TTL,
        );

        return $bots;
    }

    /** A box showing something other than what the project has saved. */
    #[Computed]
    public function botUnsaved(): bool
    {
        return trim($this->interviewAgentId) !== (string) ($this->project->interview_agent_id ?? '');
    }

    /**
     * Record which DenAI bot conducts this project's calls.
     *
     * On this screen rather than on the interviews dashboard because it is a
     * precondition of the button beside it: {@see inviteSelected()} refuses
     * without a bot, and the recruiter who hits that refusal is standing here.
     * Asking them to leave, find a second panel, choose the same project again
     * and come back is three chances to invite from the wrong one.
     *
     * The bot itself — its wording, its voice, its name — is authored on the
     * DenAI dashboard and stays there. This only records *which* one, because a
     * second place to write prompts would drift from the first within a week
     * and nobody would know which one the candidate heard.
     */
    public function saveBot(): void
    {
        $this->assertVisible();

        $agentId = trim($this->interviewAgentId);

        // Loose on purpose: a dashboard ObjectId today, whatever the dashboard
        // stores tomorrow. Tight enough to reject a pasted URL.
        if ($agentId !== '' && ! preg_match('/^[A-Za-z0-9_-]{1,64}$/', $agentId)) {
            $this->dispatch('notify', type: 'danger', message: __('interview.dashboard.bot_invalid'));

            return;
        }

        // Accepted even when the directory could not be reached. A recruiter
        // who already knows the id should not be blocked by a network path
        // between two services, and the id is validated against the directory
        // at dial time anyway.
        $this->project->update(['interview_agent_id' => $agentId ?: null]);
        $this->interviewAgentId = $agentId;

        unset($this->botUnsaved);

        Log::info('interview.bot_assigned', [
            'project_id' => $this->project->id,
            'agent_id' => $this->project->interview_agent_id,
            'by' => auth()->id(),
        ]);

        $this->dispatch('notify', type: 'success', message: $agentId !== ''
            ? __('interview.dashboard.bot_assigned')
            : __('interview.dashboard.bot_cleared'));
    }

    // ── invite ───────────────────────────────────────────────────────────── #

    /** Invite everyone ticked, offering the three chosen times. */
    public function inviteSelected(InterviewInvitationService $invitations): void
    {
        $this->assertVisible();

        if (blank($this->project->interview_agent_id)) {
            // A product rule, not a technical one: the AI service would happily
            // run the interview on questions SES generates, but the questions
            // that matter are the ones written in the bot's prompt, so an
            // interview without a bot asks the wrong things.
            //
            // Enforced here and not only by disabling the button, because the
            // button is a hint and this is a rule.
            $this->dispatch('notify', type: 'warning', message: __('interview.dashboard.bot_required'));

            return;
        }

        if ($this->botUnsaved()) {
            // The box says one bot and the project has another. Sending now
            // would call the candidate with whichever one was saved, while the
            // screen promised the one on display.
            $this->dispatch('notify', type: 'warning', message: __('interview.dashboard.bot_unsaved'));

            return;
        }

        /*
         * Empty is the normal case, and it means "open the calendar".
         *
         * This used to refuse unless all three boxes were filled, which made
         * every invitation a decision the recruiter had to make on the
         * candidate's behalf — three times, in the candidate's timezone, that
         * a stranger's week has to fit around. Leaving them blank now sends a
         * fortnight of half-hours the candidate picks from themselves.
         *
         * Filling them still wins, because a recruiter sometimes knows what no
         * calendar does: that the client wants this person seen today, that
         * Monday is a holiday.
         */
        $slotTimes = array_values(array_filter($this->slotTimes, 'filled'));

        $ids = $this->selectedIds();

        if ($ids === []) {
            $this->dispatch('notify', type: 'warning', message: __('interview.match_run.nothing_selected'));

            return;
        }

        /*
         * Who, of the ticked candidates, would receive no email anyway.
         *
         * invite() is idempotent: a live invitation comes back untouched,
         * without throwing and without sending. Counting its return as a send —
         * which this loop used to do — reported "Invited 5" when two emails
         * went out, and a recruiter reading that stops chasing the other three.
         *
         * Taken out of the batch before the loop rather than detected inside
         * it, so the count and the message describe the same thing.
         */
        $skipped = $invitations->skippableTalentIds($this->project, $ids);
        $ids = array_values(array_diff($ids, $skipped));

        if ($ids === []) {
            $this->clearSelection();

            $this->dispatch('notify', type: 'info', message: __(
                'interview.match_run.all_already_invited',
                ['count' => count($skipped)],
            ));

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
                    // `null`, not `[]`: the service reads "no times given" as
                    // the calendar, and an empty array must not be mistaken
                    // for a recruiter who meant to pin times and failed.
                    $slotTimes ?: null,
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

        // Built from parts rather than picked from two fixed sentences: a batch
        // can both skip somebody and fail to reach somebody else, and a message
        // that can only say one of those hides the other.
        $parts = [__('interview.dashboard.invited', ['count' => $sent])];

        if ($skipped !== []) {
            $parts[] = __('interview.match_run.skipped_already_invited', ['count' => count($skipped)]);
        }

        if ($failures !== []) {
            $parts[] = __('interview.match_run.some_unreachable', [
                'failures' => implode('; ', array_slice($failures, 0, 3)),
            ]);
        }

        $this->dispatch(
            'notify',
            type: $failures === [] ? 'success' : 'warning',
            message: implode(' ', $parts),
        );
    }

    // ── render ───────────────────────────────────────────────────────────── #

    public function results()
    {
        return $this->baseQuery()->paginate($this->perPage);
    }

    public function render(): View
    {
        $results = $this->results();

        return view('livewire.projects.match-results', [
            'results' => $results,
            /*
             * Rows that Send would pass over, badged so the recruiter sees it
             * before they tick rather than in the message afterwards.
             *
             * Read through the same rule inviteSelected() skips on, not "has an
             * interview row at all". The two disagree on a candidate whose
             * invitation expired or was cancelled — that one carries a row but
             * *will* be emailed again, and badging it "invited" would be the
             * screen contradicting the button.
             *
             * Scoped to the page, so this is one query over at most 20 ids.
             */
            'invitedIds' => app(InterviewInvitationService::class)->skippableTalentIds(
                $this->project,
                $results->pluck('talent_id')->map(fn ($id) => (int) $id)->all(),
            ),
        ]);
    }
}
