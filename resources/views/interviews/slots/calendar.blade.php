{{--
    The candidate's own calendar: a fortnight of days, each with the half-hours
    still free in it.

    This replaces a list of three times a recruiter typed. Three times is a
    yes/no question asked three times, and the answer is usually no — which
    cost a reply, a second email, another wait, and often the candidate. A
    window lets them answer once.

    **No JavaScript, deliberately.** The day tabs and the time grid run on
    radio buttons and sibling selectors. This page is the single point where an
    invitation either becomes a booking or is lost, and it is opened inside
    mail clients' embedded browsers on devices nobody has a list of. A date
    picker that needs a script to render is one that is sometimes a blank
    rectangle, and there is no second email to recover from that.

    Times already taken are shown struck through rather than omitted. A grid
    that silently skips 14:00 reads as a rendering fault; one that shows it
    crossed out reads as information.
--}}
@extends('interviews.slots.layout', ['title' => __('interview.page.choose_title')])

@push('styles')
    {{-- One pair of rules per day. Written out rather than scripted: the whole
         interaction is "which panel is visible", and that is something CSS has
         been able to say since long before any of the browsers this has to
         work in. --}}
    <style>
        @foreach($days as $day)
            #d-{{ $day['date'] }}:checked ~ .daystrip label[for="d-{{ $day['date'] }}"] {
                border-color: var(--accent);
                box-shadow: inset 0 0 0 1px var(--accent);
                background: #f5f8ff;
            }
            #d-{{ $day['date'] }}:checked ~ .panels #p-{{ $day['date'] }} { display: block; }
        @endforeach
        @media (prefers-color-scheme: dark) {
            @foreach($days as $day)
                #d-{{ $day['date'] }}:checked ~ .daystrip label[for="d-{{ $day['date'] }}"] {
                    background: #232a36;
                }
            @endforeach
        }
    </style>
@endpush

@section('content')
    <h1>{{ __('interview.page.choose_title') }}</h1>

    <p>{{ __('interview.page.choose_intro_calendar', ['project' => $interview->project?->title ?? '']) }}</p>
    <p class="muted">{{ __('interview.page.duration_note', ['minutes' => $minutes]) }}</p>

    @if(session('slot_error'))
        {{-- Somebody else confirmed this window between the page loading and
             the button being pressed, or nothing was picked at all. Expected,
             so it reads as information rather than as an error. --}}
        <div class="notice notice-warn">{{ session('slot_error') }}</div>
    @endif

    <p class="tz">
        {{ __('interview.page.calendar_hours', ['from' => $opensAt, 'to' => $closesAt]) }}
        {{ __('interview.page.times_shown_in', ['timezone' => $timezone]) }}
    </p>

    <form method="POST" action="{{ route('interview-slots.store', ['token' => request()->route('token')]) }}">
        @csrf

        {{-- The tab state. These come first and are siblings of everything
             below, which is what makes `#d-x:checked ~ .panels #p-x` reach the
             right panel. `day_tab` is posted and ignored by the controller;
             the booking is `slot_start` alone. --}}
        @foreach($days as $day)
            <input type="radio" name="day_tab" class="daytab"
                   id="d-{{ $day['date'] }}"
                   aria-label="{{ $day['weekday'] }} {{ $day['label'] }}"
                   @checked($day['date'] === $openDate)>
        @endforeach

        <div class="daystrip">
            @foreach($days as $day)
                <label for="d-{{ $day['date'] }}"
                       class="dayblock {{ $day['available_count'] === 0 ? 'full' : '' }}"
                       title="{{ $day['available_count'] === 0
                            ? __('interview.page.day_full')
                            : __('interview.page.day_free', ['count' => $day['available_count']]) }}">
                    <span class="wd">{{ $day['weekday'] }}</span>
                    <span class="dn">{{ $day['day'] }}</span>
                    <span class="mo">{{ $day['month'] }}</span>
                </label>
            @endforeach
        </div>

        <div class="panels">
            @foreach($days as $day)
                <div class="panel" id="p-{{ $day['date'] }}">
                    @if($day['available_count'] === 0)
                        <p class="dayempty">{{ __('interview.page.day_full_long') }}</p>
                    @else
                        <div class="times">
                            @foreach($day['cells'] as $cell)
                                @if($cell['available'])
                                    <label class="time">
                                        <input type="radio" name="slot_start" value="{{ $cell['value'] }}">
                                        <span>{{ $cell['label'] }}</span>
                                    </label>
                                @else
                                    {{-- Not a disabled input: a disabled control
                                         is still reachable in some readers and
                                         invites a tap that does nothing. --}}
                                    <span class="time gone" aria-hidden="true">{{ $cell['label'] }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn">{{ __('interview.page.confirm_button') }}</button>
    </form>

    <p class="meta">
        {{ __('interview.page.calendar_expires', [
            'date' => \App\Support\InterviewTime::full($until, $timezone),
        ]) }}
        {{ __('interview.page.recorded_notice') }}
    </p>
@endsection
