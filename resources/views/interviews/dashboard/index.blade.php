@extends('layouts.app')

@section('title', __('interview.dashboard.title'))

@section('content')
<div class="container-fluid container-lg my-4">

    {{-- No actions in this header, and no entry point to matching either.
         This page reads interview outcomes. Matching, choosing the bot and
         sending invitations belong to a project, so they are reached from the
         project — not from a button here that depends on a filter being set. --}}
    <div class="mb-3">
        <h1 class="h4 mb-1">{{ __('interview.dashboard.title') }}</h1>
        <p class="text-muted small mb-0">{{ __('interview.dashboard.subtitle') }}</p>
    </div>

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
                            <td class="small">
                                @if($interview->project)
                                    {{-- Straight to where this project's candidates
                                         are matched and invited. --}}
                                    <a href="{{ route('project-matches.index', $interview->project->id) }}">
                                        {{ $interview->project->title }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
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
