{{-- CV matching, start to finish, on one page. --}}
<div>
    {{-- ── 1. Requirements ──────────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-semibold">{{ __('interview.requirement.heading') }}</span>
                <p class="text-muted small mb-0">{{ __('interview.requirement.help') }}</p>
            </div>
            <span class="badge text-bg-dark">
                {{ __('interview.requirement.mandatory_count', [
                    'count' => $this->requirements->where('is_mandatory', true)->where('in_latest_parse', true)->count(),
                ]) }}
            </span>
        </div>

        <div class="card-body">
            @forelse($this->requirements as $requirement)
                @php
                    // A stale row is one the JD no longer states. Kept, greyed,
                    // and excluded from the gate — because a must-have that
                    // vanished without a word is the same failure in a slower
                    // form.
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
                                {{ $requirement->label }}
                            </span>
                        </label>
                    </div>

                    @if($requirement->kind === \App\Enums\RequirementKind::LANGUAGE && filled($requirement->level))
                        <span class="badge text-bg-secondary">{{ $requirement->level }}</span>
                    @endif

                    {{-- he parser frequently reads "experienced engineer" with no number attached. --}}
                    @if($requirement->kind === \App\Enums\RequirementKind::EXPERIENCE)
                        <div class="input-group input-group-sm" style="width: 11rem;">
                            <input type="number" class="form-control" min="0" max="40"
                                   value="{{ (int) floor(($requirement->min_months ?? 0) / 12) }}"
                                   aria-label="{{ __('interview.requirement.years') }}"
                                   wire:change="setExperienceYears({{ $requirement->id }}, $event.target.value)">
                            <span class="input-group-text">{{ __('interview.requirement.years') }}</span>
                        </div>
                    @endif

                    @if($incomplete && $requirement->is_mandatory)
                        <span class="badge text-bg-warning">{{ __('interview.requirement.needs_number') }}</span>
                    @endif

                    @if($stale)
                        <span class="badge text-bg-secondary">{{ __('interview.requirement.stale') }}</span>
                    @endif

                    @if(filled($requirement->evidence) && ! $stale)
                        <small class="text-muted text-truncate d-none d-md-inline" style="max-width: 24rem;"
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

    {{-- ── 3. Results ───────────────────────────────────────────────────── --}}
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

                    <div class="d-flex flex-wrap gap-2">
                        @foreach([0, 1, 2] as $i)
                            <div>
                                <label class="form-label small mb-1">
                                    {{ __('interview.match_run.slot_n', ['n' => $i + 1]) }}
                                </label>
                                <input type="datetime-local" class="form-control form-control-sm"
                                       wire:model="slotTimes.{{ $i }}">
                            </div>
                        @endforeach
                    </div>

                    <button class="btn btn-success"
                            wire:click="inviteSelected"
                            wire:loading.attr="disabled"
                            wire:confirm="{{ __('interview.dashboard.invite_confirm') }}">
                        <i class="fa-solid fa-paper-plane me-1"></i>
                        {{ __('interview.match_run.invite_selected') }}
                    </button>
                </div>

                <p class="text-muted small mb-0 mt-2">
                    {{ __('interview.dashboard.slot_times_help', [
                        'zone' => config('services.interview.invitation.timezone', 'Asia/Tokyo'),
                    ]) }}
                </p>
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
                                <input type="checkbox" class="form-check-input"
                                       value="{{ $match->talent_id }}"
                                       wire:model.live="selected"
                                       @checked($selectAllMatching)
                                       @disabled($selectAllMatching)
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
