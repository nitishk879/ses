@extends('interviews.slots.layout', ['title' => __('interview.page.choose_title')])

@section('content')
    <h1>{{ __('interview.page.choose_title') }}</h1>

    <p>{{ __('interview.page.choose_intro', ['project' => $interview->project?->title ?? '']) }}</p>
    <p class="muted">{{ __('interview.page.duration_note', ['minutes' => $minutes]) }}</p>

    @if(session('slot_error'))
        {{-- Somebody else confirmed this window between the page loading and
             the button being pressed. Expected, so it reads as information
             rather than as an error. --}}
        <div class="notice notice-warn">{{ session('slot_error') }}</div>
    @endif

    <p class="tz">{{ __('interview.page.times_shown_in', ['timezone' => $timezone]) }}</p>

    <form method="POST" action="{{ route('interview-slots.store', ['token' => request()->route('token')]) }}">
        @csrf

        <ul class="slots">
            @foreach($slots as $slot)
                <li class="slot">
                    <label>
                        <input type="radio" name="slot_id" value="{{ $slot->id }}"
                               @checked($loop->first) required>
                        <span class="when">{{ $slot->presentIn($timezone) }}</span>
                    </label>
                </li>
            @endforeach
        </ul>

        <button type="submit" class="btn">{{ __('interview.page.confirm_button') }}</button>
    </form>

    <p class="meta">{{ __('interview.page.recorded_notice') }}</p>
@endsection
