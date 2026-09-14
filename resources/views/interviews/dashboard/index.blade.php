@extends('layouts.app')

@section('title', __('interview.dashboard.title'))

@section('content')
<div class="container-fluid container-lg my-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">{{ __('interview.dashboard.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('interview.dashboard.subtitle') }}</p>
        </div>

        @if($projects->isNotEmpty())
            {{-- The two operations that used to be artisan commands. --}}
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary" type="button"
                        data-bs-toggle="collapse" data-bs-target="#runActions">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                    {{ __('interview.dashboard.actions') }}
                </button>
            </div>
        @endif
    </div>

    @if($projects->isNotEmpty())
        <div class="collapse mb-3" id="runActions">
            <div class="card card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <h2 class="h6">{{ __('interview.dashboard.run_matching') }}</h2>
                        <p class="text-muted small">{{ __('interview.dashboard.run_matching_help') }}</p>
                        <form method="POST" action="{{ route('interview-dashboard.match', ['project' => 0]) }}"
                              class="row g-2 align-items-end" id="matchForm">
                            @csrf
                            <div class="col-sm-8">
                                <label class="form-label small mb-1" for="matchProjectSelect">
                                    {{ __('interview.dashboard.project') }}
                                </label>
                                <select class="form-select" id="matchProjectSelect" required
                                        onchange="document.getElementById('matchForm').action='{{ url('interviews/dashboard/match') }}/'+this.value">
                                    <option value="">{{ __('interview.dashboard.choose_project') }}</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <button class="btn btn-primary w-100" type="submit">
                                    {{ __('interview.dashboard.run') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Which bot conducts this project's calls. The bot itself is
                         authored on the DenAI dashboard; this only records the
                         choice, so there is exactly one place prompts are written. --}}
                    <div class="col-12 border-top pt-3">
                        <h2 class="h6">{{ __('interview.dashboard.interview_bot') }}</h2>
                        <p class="text-muted small">{{ __('interview.dashboard.interview_bot_help') }}</p>
                        <form method="POST" action="{{ url('interviews/dashboard/bot') }}/0"
                              class="row g-2 align-items-end" id="botForm">
                            @csrf
                            <div class="col-sm-4">
                                <label class="form-label small mb-1" for="botProjectSelect">
                                    {{ __('interview.dashboard.project') }}
                                </label>
                                <select class="form-select" id="botProjectSelect" required
                                        onchange="document.getElementById('botForm').action='{{ url('interviews/dashboard/bot') }}/'+this.value">
                                    <option value="">{{ __('interview.dashboard.choose_project') }}</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}"
                                                @selected(($filters['project'] ?? null) == $p->id)>{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label small mb-1" for="interviewAgentId">
                                    {{ __('interview.dashboard.bot') }}
                                </label>
                                @if(! empty($bots))
                                    <select class="form-select" id="interviewAgentId" name="interview_agent_id">
                                        <option value="">{{ __('interview.dashboard.no_bot') }}</option>
                                        @foreach($bots as $bot)
                                            <option value="{{ $bot['agent_id'] }}">
                                                {{ $bot['name'] }}@if(! empty($bot['language'])) — {{ $bot['language'] }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    {{-- An unreachable directory is said out loud rather than
                                         rendered as "no bots exist", which would send a
                                         recruiter off to create one that already exists. --}}
                                    <input type="text" class="form-control" id="interviewAgentId"
                                           name="interview_agent_id" maxlength="64"
                                           placeholder="{{ __('interview.dashboard.bot_id_placeholder') }}">
                                    <div class="form-text text-warning">
                                        {{ __('interview.dashboard.bot_list_unavailable') }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-sm-3">
                                <button class="btn btn-outline-primary w-100" type="submit">
                                    {{ __('interview.dashboard.save_bot') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-6 border-start-lg">
                        <h2 class="h6">{{ __('interview.dashboard.invite_shortlist') }}</h2>
                        <p class="text-muted small">{{ __('interview.dashboard.invite_help') }}</p>
                        <form method="POST" action="{{ url('interviews/dashboard/invite') }}/0"
                              class="row g-2 align-items-end" id="inviteForm"
                              onsubmit="return confirm('{{ __('interview.dashboard.invite_confirm') }}')">
                            @csrf
                            <div class="col-sm-6">
                                <label class="form-label small mb-1" for="inviteProjectSelect">
                                    {{ __('interview.dashboard.project') }}
                                </label>
                                <select class="form-select" id="inviteProjectSelect" required
                                        onchange="document.getElementById('inviteForm').action='{{ url('interviews/dashboard/invite') }}/'+this.value">
                                    <option value="">{{ __('interview.dashboard.choose_project') }}</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label small mb-1" for="threshold">
                                    {{ __('interview.dashboard.threshold') }}
                                </label>
                                <input type="number" class="form-control" id="threshold" name="threshold"
                                       min="0" max="100"
                                       value="{{ config('services.interview.invitation.min_match_score', 70) }}">
                            </div>
                            <div class="col-sm-3">
                                <button class="btn btn-success w-100" type="submit">
                                    {{ __('interview.dashboard.send') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Counts, so the first thing a recruiter sees is what needs them. --}}
    <div class="row g-2 mb-3">
        @foreach([
            'total' => 'secondary',
            'awaiting_reply' => 'info',
            'scheduled' => 'primary',
            'completed' => 'success',
            'needs_attention' => 'warning',
        ] as $key => $variant)
            <div class="col-6 col-md">
                <div class="card h-100 border-{{ $variant }}">
                    <div class="card-body py-2 px-3">
                        <div class="fs-4 fw-semibold">{{ $summary[$key] }}</div>
                        <div class="small text-muted">{{ __("interview.dashboard.stat_{$key}") }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label small mb-1" for="q">{{ __('interview.dashboard.search') }}</label>
            <input type="search" class="form-control" id="q" name="q"
                   value="{{ $filters['q'] ?? '' }}"
                   placeholder="{{ __('interview.dashboard.search_placeholder') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1" for="statusFilter">{{ __('interview.dashboard.status') }}</label>
            <select class="form-select" id="statusFilter" name="status">
                <option value="">{{ __('interview.dashboard.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ \App\Enums\InterviewStatus::toName($status) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1" for="projectFilter">{{ __('interview.dashboard.project') }}</label>
            <select class="form-select" id="projectFilter" name="project">
                <option value="">{{ __('interview.dashboard.all_projects') }}</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" @selected((int) ($filters['project'] ?? 0) === $p->id)>
                        {{ $p->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-outline-primary flex-fill" type="submit">
                {{ __('interview.dashboard.filter') }}
            </button>
            <a class="btn btn-link" href="{{ route('interview-dashboard.index') }}">
                {{ __('interview.dashboard.clear') }}
            </a>
        </div>
    </form>

    @if($interviews->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-1">{{ __('interview.dashboard.empty') }}</p>
                <p class="small text-muted mb-0">{{ __('interview.dashboard.empty_help') }}</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('interview.dashboard.candidate') }}</th>
                            <th>{{ __('interview.dashboard.project') }}</th>
                            <th class="text-center">{{ __('interview.dashboard.match') }}</th>
                            <th>{{ __('interview.dashboard.status') }}</th>
                            <th>{{ __('interview.dashboard.scheduled') }}</th>
                            <th class="text-center">{{ __('interview.dashboard.result') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($interviews as $interview)
                        @php
                            $evaluation = $interview->latestAttempt?->evaluation;
                            $tz = $interview->timezone ?: config('services.interview.invitation.timezone', 'Asia/Tokyo');
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $interview->talent?->user?->name ?: '—' }}</div>
                                <div class="small text-muted">{{ $interview->talent?->user?->email }}</div>
                            </td>
                            <td class="small">{{ $interview->project?->title ?: '—' }}</td>
                            <td class="text-center">
                                @if($interview->match_score !== null)
                                    <span class="badge bg-light text-dark border">{{ $interview->match_score }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $interview->status->badgeVariant() }}">
                                    {{ \App\Enums\InterviewStatus::toName($interview->status) }}
                                </span>
                            </td>
                            <td class="small">
                                @if($interview->scheduled_at)
                                    {{ $interview->scheduled_at->setTimezone($tz)->translatedFormat('D, j M Y H:i') }}
                                    <span class="text-muted">({{ $tz }})</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($evaluation && $evaluation->overall_score !== null)
                                    <span class="fw-semibold">{{ (int) round($evaluation->overall_score) }}</span>
                                    <div class="small text-muted">{{ $evaluation->recommendation }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('interview-dashboard.show', $interview) }}">
                                    {{ __('interview.dashboard.view') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $interviews->links() }}</div>
    @endif
</div>
@endsection
