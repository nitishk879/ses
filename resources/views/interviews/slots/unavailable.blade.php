@extends('interviews.slots.layout', ['title' => __('interview.page.unavailable_title')])

@section('content')
    <h1>{{ __('interview.page.unavailable_title') }}</h1>

    <div class="notice notice-warn">{{ $reason }}</div>

    {{-- No dead end: a candidate who reaches this page has already shown they
         want to take part, so they are told exactly what to do next rather
         than being left with a message and no route forward. --}}
    <p>{{ __('interview.page.unavailable_next') }}</p>
@endsection
