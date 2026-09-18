@extends('layouts.app')

@section('title', __('interview.match_run.title'))

@section('content')
<div class="container-fluid container-lg my-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">{{ __('interview.match_run.title') }}</h1>
            <p class="text-muted small mb-0">{{ $project->title }}</p>
        </div>

        <a href="{{ route('interview-dashboard.index', ['project' => $project->id]) }}"
           class="btn btn-outline-secondary btn-sm">
            {{ __('interview.match_run.to_interviews') }}
        </a>
    </div>

    {{-- Livewire dispatches a `notify` event for every action on this page
         rather than using session flash, because none of them reload the
         page and a flash would only appear on the next full request. --}}
    <div id="match-notices"></div>

    @livewire('projects.match-results', ['project' => $project])
</div>
@endsection

@push('scripts')
<script>
    // Bootstrap alerts driven by the component's `notify` events. Kept here
    // rather than inside the component so a re-render never wipes a message
    // the recruiter has not read yet.
    document.addEventListener('livewire:init', () => {
        Livewire.on('notify', (event) => {
            const { type = 'info', message = '' } = event[0] ?? event ?? {};
            if (!message) return;

            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.setAttribute('role', 'alert');
            alert.textContent = message;

            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'btn-close';
            close.setAttribute('data-bs-dismiss', 'alert');
            alert.appendChild(close);

            document.getElementById('match-notices').prepend(alert);
        });
    });
</script>
@endpush
