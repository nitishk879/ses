{{--
    Invitation + slot offer (tasks 5 and 6).

    Every time is printed with its zone abbreviation. A bare "10:00" read on a
    phone in another country is an ambiguity the candidate cannot resolve, and
    a missed interview is what that costs.

    The times are also listed in the body rather than only behind the button:
    a candidate should be able to tell from the email alone whether any of the
    options work, without clicking anything.
--}}
<x-mail::message>
# {{ __('interview.mail.heading') }}

{{ __('interview.mail.intro', ['project' => $project?->title ?? '']) }}

{{ __('interview.mail.about', ['minutes' => $minutes]) }}

<x-mail::panel>
**{{ __('interview.mail.slots_heading') }}**

@foreach($slots as $slot)
{{ $loop->iteration }}. {{ $slot->presentIn($timezone) }}
@endforeach
</x-mail::panel>

<x-mail::button :url="$url">
{{ __('interview.mail.cta') }}
</x-mail::button>

@if($expiresAt)
{{ __('interview.mail.expires', ['date' => $expiresAt->setTimezone($timezone)->translatedFormat('D, j M Y H:i (T)')]) }}
@endif

{{ __('interview.mail.none_suitable') }}

{{ __('interview.mail.recorded_notice') }}

{{ __('interview.mail.regards') }}<br>
{{ config('app.name') }}

<x-slot:subcopy>
{{ __('interview.mail.button_fallback') }}
[{{ $url }}]({{ $url }})
</x-slot:subcopy>
</x-mail::message>
