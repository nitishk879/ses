{{-- CV matching, start to finish, on one page. --}}
<div>
    {{-- ── 1. Requirements ──────────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-semibold">{{ __('interview.requirement.heading') }}</span>
                <p class="text-muted small mb-0">{{ __('interview.requirement.help') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Values are edited on the project form; this screen only
                     decides which of them are must-haves. --}}
                @can('update', $project)
                    <a href="{{ route('project.edit', $project) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-pen me-1"></i>{{ __('interview.requirement.edit_in_project') }}
                    </a>
                @endcan
                <span class="badge text-bg-dark">
                    {{ __('interview.requirement.mandatory_count', [
                        'count' => $this->requirements->where('is_mandatory', true)->where('in_latest_parse', true)->count(),
                    ]) }}
                </span>
            </div>
        </div>

        <div class="card-body">
            @forelse($this->requirements as $requirement)
                @php
                    // A stale row is a must-have whose source the project no
                    // longer states. Kept, greyed and not enforced, so a hiring
                    // rule never disappears without a word. Non-mandatory rows
                    // in that state are deleted on sync and never reach here.
                    $stale = ! $requirement->in_latest_parse;
                    $incomplete = $requirement->isIncomplete();
                @endphp

                <div class="d-flex flex-wrap align-items-center gap-2 py-2 border-bottom {{ $stale ? 'opacity-50' : '' }}">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox"
                               id="req-{{ $requirement->id }}"
                               @checked($requirement->is_mandatory)
                               @disabled($stale)
                               wire:click="toggleMandatory({{ $requirement->id }})"
                               wire:loading.attr="disabled">
                        <label class="form-check-label" for="req-{{ $requirement->id }}">
                            <span class="badge text-bg-light border me-1">{{ $requirement->kind->label() }}</span>
                            <span class="{{ $requirement->is_mandatory && ! $stale ? 'fw-semibold' : '' }}">
                                {{ $requirement->displayLabel() }}
                            </span>
                        </label>
                    </div>

                    @if($requirement->kind === \App\Enums\RequirementKind::LANGUAGE && filled($requirement->level))
                        <span class="badge text-bg-secondary">{{ $requirement->level }}</span>
                    @endif

                    @if($incomplete && $requirement->is_mandatory)
                        <span class="badge text-bg-warning">{{ __('interview.requirement.needs_number') }}</span>
                    @endif

                    @if($stale)
                        <span class="badge text-bg-secondary">{{ __('interview.requirement.stale') }}</span>
                    @endif

                    {{-- Where this row came from. Three different things are
                         listed under one heading — pivots the recruiter
                         ticked, columns they filled in, and phrases pulled out
                         of the description — and a list whose provenance
                         cannot be read is a list whose switches do not get
                         used. --}}
                    <span class="badge rounded-pill text-bg-light border text-muted fw-normal">
                        {{ $requirement->originLabel() }}
                    </span>

                    {{-- A quote only when it is one. On a form-derived row the
                         "evidence" is the parser's sentinel, which the pill
                         beside it already says more plainly. --}}
                    @if(filled($requirement->evidence) && ! $stale && $requirement->origin === 'jd_text')
                        <small class="text-muted text-truncate d-none d-md-inline" style="max-width: 20rem;"
                               title="{{ $requirement->evidence }}">
                            “{{ $requirement->evidence }}”
                        </small>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">{{ __('interview.requirement.none') }}</p>
            @endforelse
        </div>
    </div>

    {{-- ── 2. Run ───────────────────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-end gap-3">
            <div>
                <label class="form-label small mb-1" for="threshold">{{ __('interview.dashboard.threshold') }}</label>
                <input type="number" class="form-control form-control-sm" id="threshold"
                       min="0" max="100" style="width: 6rem;" wire:model.live.debounce.500ms="threshold">
            </div>

            <button class="btn btn-primary" wire:click="analyze" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="analyze">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                    {{ __('interview.match_run.analyze') }}
                </span>
                <span wire:loading wire:target="analyze">{{ __('interview.match_run.starting') }}</span>
            </button>

            <div class="flex-grow-1">
                @if($this->latestRun)
                    @php $run = $this->latestRun; @endphp

                    @if(! $run->isFinished())
                        <div class="alert alert-info py-2 mb-0" wire:poll.10s>
                            <i class="fa-solid fa-spinner fa-spin me-1"></i>
                            {{ __('interview.match_run.in_progress', ['count' => $run->candidates_total]) }}
                        </div>
                    @elseif($run->status === \App\Models\AiMatchRun::STATUS_FAILED)
                        <div class="alert alert-danger py-2 mb-0">
                            {{ __('interview.match_run.failed', ['reason' => $run->failure_reason]) }}
                        </div>
                    @else
                        <p class="text-muted small mb-0">
                            {{ __('interview.match_run.last_run', [
                                'when' => $run->completed_at?->diffForHumans(),
                                'scored' => $run->scored,
                            ]) }}
                            @if($run->parse_failures > 0)
                                <span class="text-warning">
                                    {{ __('interview.match_run.parse_failures', ['count' => $run->parse_failures]) }}
                                </span>
                            @endif
                        </p>
                    @endif
                @else
                    <p class="text-muted small mb-0">{{ __('interview.match_run.never_run') }}</p>
                @endif
            </div>
        </div>

        {{-- he scores on screen were reached under a gate that has since changed. --}}
        @if($this->gateIsStale)
            <div class="card-footer bg-warning-subtle">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                {{ __('interview.match_run.gate_stale') }}
            </div>
        @endif
    </div>

    {{-- ── 3. Which bot conducts the calls ──────────────────────────────────
         Beside the button that needs it. Invite refuses without a bot, and
         this is where that refusal is read — the picker used to live on the
         interviews dashboard, which meant leaving this screen, choosing the
         same project again in a second selector, and coming back. --}}
    <div class="card mb-3" id="interview-bot">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <span class="fw-semibold">{{ __('interview.dashboard.interview_bot') }}</span>
                    <p class="text-muted small mb-0">{{ __('interview.dashboard.interview_bot_help') }}</p>
                </div>
                @if(filled($project->interview_agent_id))
                    <span class="badge text-bg-success">
                        <i class="fa-solid fa-check me-1"></i>{{ __('interview.match_run.bot_set') }}
                    </span>
                @else
                    <span class="badge text-bg-warning">{{ __('interview.match_run.bot_missing') }}</span>
                @endif
            </div>

            <div class="d-flex flex-wrap align-items-end gap-2">
                <div class="flex-grow-1" style="min-width: 16rem;">
                    <label class="form-label small mb-1" for="interviewAgentId">
                        {{ __('interview.dashboard.bot') }}
                    </label>
                    @if($this->bots !== [])
                        <select class="form-select form-select-sm" id="interviewAgentId"
                                wire:model.live="interviewAgentId">
                            <option value="">{{ __('interview.dashboard.no_bot') }}</option>
                            @foreach($this->bots as $bot)
                                <option value="{{ $bot['agent_id'] }}">
                                    {{ $bot['name'] }}@if(! empty($bot['language'])) — {{ $bot['language'] }}@endif
                                </option>
                            @endforeach
                        </select>
                    @else
                        {{-- An unreachable directory is said out loud rather than
                             rendered as "no bots exist", which would send a
                             recruiter off to create one that already exists. --}}
                        <input type="text" class="form-control form-control-sm" id="interviewAgentId"
                               maxlength="64" wire:model.live.debounce.500ms="interviewAgentId"
                               placeholder="{{ __('interview.dashboard.bot_id_placeholder') }}">
                    @endif
                </div>

                <button class="btn btn-outline-primary btn-sm" wire:click="saveBot" wire:loading.attr="disabled">
                    {{ __('interview.dashboard.save_bot') }}
                </button>
            </div>

            @if($this->bots === [])
                <div class="form-text text-warning">{{ __('interview.dashboard.bot_list_unavailable') }}</div>
            @endif

            @if($this->botUnsaved)
                <div class="form-text text-warning">{{ __('interview.dashboard.bot_unsaved') }}</div>
            @elseif(blank($project->interview_agent_id))
                {{-- With a bot assigned, the bot's prompt IS the interview: SES
                     supplies only the recorded-call opening, the candidate's
                     facts and the closing. The AI service can fall back to
                     generated questions; this product does not want that. --}}
                <div class="form-text text-warning">{{ __('interview.dashboard.no_bot_warning') }}</div>
            @endif
        </div>
    </div>

    {{-- ── 4. Results ───────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <ul class="nav nav-pills">
                @foreach(['matched', 'review', 'all'] as $tab)
                    <li class="nav-item">
                        <button class="nav-link {{ $filter === $tab ? 'active' : '' }}"
                                wire:click="$set('filter', '{{ $tab }}')">
                            {{ __("interview.match_run.tab.{$tab}") }}
                            <span class="badge text-bg-light text-dark ms-1">{{ $this->tallies[$tab] }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>

            <input type="search" class="form-control form-control-sm" style="max-width: 16rem;"
                   placeholder="{{ __('interview.match_run.search') }}"
                   wire:model.live.debounce.400ms="search">
        </div>

        @if($filter === 'review')
            <div class="alert alert-warning rounded-0 mb-0 py-2 small">
                {{ __('interview.match_run.review_help') }}
            </div>
        @endif

        {{-- The bulk action bar. Appears only with a selection, the way an
             email client's does, so the default view is the list rather than a
             row of disabled buttons. --}}
        @if($this->selectionCount > 0)
            @php
                // A bot is the only hard precondition left. Times used to be a
                // second one; they are now an override, and an unfilled
                // override is the normal case rather than a missing step.
                $botReady = filled($project->interview_agent_id) && ! $this->botUnsaved;
                $pinnedCount = count(array_filter($slotTimes, 'filled'));
                $zone = config('services.interview.invitation.timezone', 'Asia/Tokyo');
            @endphp
            <div class="card-body border-bottom bg-light">
                <div class="d-flex flex-wrap align-items-end gap-3">
                    <div>
                        <span class="fw-semibold">
                            {{ __('interview.match_run.selected', ['count' => $this->selectionCount]) }}
                        </span>
                        <button class="btn btn-link btn-sm p-0 ms-2" wire:click="clearSelection">
                            {{ __('interview.match_run.clear_selection') }}
                        </button>
                    </div>

                    <button class="btn btn-success"
                            wire:click="inviteSelected"
                            wire:loading.attr="disabled"
                            @disabled(! $botReady)
                            wire:confirm="{{ __('interview.dashboard.invite_confirm') }}">
                        <i class="fa-solid fa-paper-plane me-1"></i>
                        {{ __('interview.match_run.invite_selected') }}
                    </button>
                </div>

                <p class="text-muted small mb-0 mt-2">
                    <i class="fa-regular fa-calendar me-1"></i>
                    {{ __('interview.dashboard.calendar_default', [
                        'days' => config('services.interview.invitation.horizon_days', 14),
                        'from' => sprintf('%02d:00', (int) config('services.interview.invitation.business_start_hour', 8)),
                        'to' => sprintf('%02d:00', (int) config('services.interview.invitation.business_end_hour', 20)),
                        'zone' => $zone,
                    ]) }}
                </p>

                {{-- Collapsed, because it is the exception. Opened by default
                     when something is already typed in it, so a half-filled
                     override is never hidden behind a summary that reads as
                     "nothing to see here". --}}
                <details class="mt-2" @if($pinnedCount > 0) open @endif>
                    <summary class="small text-primary" style="cursor: pointer;">
                        {{ __('interview.dashboard.pin_times') }}
                        @if($pinnedCount > 0)
                            <span class="badge text-bg-primary ms-1">{{ $pinnedCount }}</span>
                        @endif
                    </summary>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach([0, 1, 2] as $i)
                            <div>
                                <label class="form-label small mb-1">
                                    {{ __('interview.match_run.slot_n', ['n' => $i + 1]) }}
                                </label>
                                {{-- `.live`: the hint below counts what is
                                     filled, and a value the server has not seen
                                     yet would make it contradict the boxes. --}}
                                <input type="datetime-local" class="form-control form-control-sm"
                                       min="{{ now($zone)->format('Y-m-d\TH:i') }}"
                                       wire:model.live="slotTimes.{{ $i }}">
                            </div>
                        @endforeach
                    </div>

                    <p class="text-muted small mb-0 mt-2">
                        {{ __('interview.dashboard.slot_times_help', ['zone' => $zone]) }}
                    </p>
                </details>

                {{-- The one thing that still blocks Send, linked to where it
                     gets fixed. --}}
                @if(! $botReady)
                    <p class="small text-warning mb-0 mt-1">
                        <a href="#interview-bot" class="link-warning">
                            {{ blank($project->interview_agent_id)
                                ? __('interview.dashboard.bot_required')
                                : __('interview.dashboard.bot_unsaved') }}
                        </a>
                    </p>
                @endif
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 2.5rem;">
                            <input type="checkbox" class="form-check-input"
                                   aria-label="{{ __('interview.match_run.select_page') }}"
                                   wire:change="togglePage($event.target.checked)">
                        </th>
                        <th>{{ __('interview.match_run.candidate') }}</th>
                        <th style="width: 9rem;">{{ __('interview.match_run.score') }}</th>
                        <th>{{ __('interview.match_run.must_haves') }}</th>
                        <th class="d-none d-lg-table-cell">{{ __('interview.match_run.why') }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- "Select all N" lives in its own row, shown only once the
                         page is fully ticked — exactly where an email client
                         puts it, and never as the default action. --}}
                    @if($this->selectionCount > 0 && ! $selectAllMatching && $results->total() > $results->count())
                        <tr class="table-light">
                            <td colspan="5" class="text-center small py-2">
                                <button class="btn btn-link btn-sm p-0"
                                        wire:click="$set('selectAllMatching', true)">
                                    {{ __('interview.match_run.select_all', ['count' => $results->total()]) }}
                                </button>
                            </td>
                        </tr>
                    @endif

                    @forelse($results as $match)
                        @php
                            $mandatory = $match->payload['mandatory_results'] ?? [];
                            $invited = in_array((int) $match->talent_id, $invitedIds, true);
                        @endphp

                        <tr wire:key="match-{{ $match->id }}">
                            <td>
                                {{-- An already-invited row is not tickable. Send
                                     passes over it anyway, so offering the tick
                                     would only inflate the count the recruiter
                                     reads before pressing it. --}}
                                <input type="checkbox" class="form-check-input"
                                       value="{{ $match->talent_id }}"
                                       wire:model.live="selected"
                                       @checked($selectAllMatching && ! $invited)
                                       @disabled($selectAllMatching || $invited)
                                       title="{{ $invited ? __('interview.match_run.already_invited_hint') : '' }}"
                                       aria-label="{{ $match->talent?->user?->name }}">
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $match->talent?->user?->name ?: __('interview.match_run.unnamed', ['id' => $match->talent_id]) }}
                                </div>
                                <small class="text-muted">{{ $match->talent?->user?->email }}</small>
                                @if($invited)
                                    <span class="badge text-bg-info ms-1">{{ __('interview.match_run.already_invited') }}</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: .5rem;" role="progressbar"
                                         aria-valuenow="{{ $match->score }}" aria-valuemin="0" aria-valuemax="100">
                                        {{-- The normalized floor, not the raw input: an emptied
                                             box is null, and `>= null` would paint every bar green. --}}
                                        <div class="progress-bar {{ $match->score >= $this->scoreFloor ? 'bg-success' : 'bg-secondary' }}"
                                             style="width: {{ $match->score }}%"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $match->score }}%</span>
                                </div>
                            </td>

                            <td>
                                @forelse($mandatory as $outcome)
                                    @php
                                        // Three states, three colours. A grey
                                        // "?" is the one that matters: it means
                                        // the documents did not say, which is
                                        // neither a pass nor a rejection.
                                        [$class, $icon] = match($outcome['status'] ?? '') {
                                            'met' => ['text-bg-success', 'fa-check'],
                                            'not_met' => ['text-bg-danger', 'fa-xmark'],
                                            default => ['text-bg-secondary', 'fa-question'],
                                        };
                                    @endphp
                                    <span class="badge {{ $class }} mb-1"
                                          title="{{ $outcome['detail'] ?? '' }}">
                                        <i class="fa-solid {{ $icon }} me-1"></i>{{ $outcome['label'] ?? '' }}
                                    </span>
                                @empty
                                    <span class="text-muted small">{{ __('interview.match_run.no_must_haves') }}</span>
                                @endforelse
                            </td>

                            <td class="d-none d-lg-table-cell">
                                <ul class="list-unstyled small mb-0">
                                    @foreach(array_slice($match->reasons(), 0, 2) as $reason)
                                        <li class="text-muted">{{ $reason }}</li>
                                    @endforeach
                                    @foreach(array_slice($match->blockers(), 0, 1) as $blocker)
                                        <li class="text-danger">{{ $blocker }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                {{ $this->tallies['all'] === 0
                                    ? __('interview.match_run.empty_never_run')
                                    : __('interview.match_run.empty_filter') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($results->hasPages())
            <div class="card-footer">{{ $results->links() }}</div>
        @endif
    </div>
</div>
