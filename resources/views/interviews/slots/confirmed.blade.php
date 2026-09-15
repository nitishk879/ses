@extends('interviews.slots.layout', ['title' => __('interview.page.confirmed_title')])

@section('content')
    <h1>{{ __('interview.page.confirmed_title') }}</h1>

    <div class="notice notice-ok">
        {{ \App\Support\InterviewTime::full($interview->scheduled_at, $timezone) }}
    </div>

    <p>{{ __('interview.page.confirmed_intro', ['project' => $interview->project?->title ?? '']) }}</p>

    <p class="muted">{{ __('interview.page.confirmed_what_happens') }}</p>

    <p class="meta">{{ __('interview.page.recorded_notice') }}</p>
@endsection
