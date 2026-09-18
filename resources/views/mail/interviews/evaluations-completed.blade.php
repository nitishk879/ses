{{-- The summary a recruiter gets when a batch of AI interviews has been scored. --}}
<x-mail::message>
# {{ __('interview.evaluation_digest.mail.heading', ['count' => $digest->evaluated]) }}

@if(filled($recruiterName ?? null))
{{ __('interview.evaluation_digest.mail.greeting', ['name' => $recruiterName]) }}
@endif

{{ __('interview.evaluation_digest.mail.intro', [
    'count' => $digest->evaluated,
    'project' => $project?->title ?? '',
]) }}

{{ __('interview.evaluation_digest.mail.recommended', ['count' => $digest->recommended]) }}

@if($preview->isNotEmpty())
<x-mail::panel>
**{{ __('interview.evaluation_digest.mail.preview_heading') }}**

@foreach($preview as $evaluation)
@php($name = $evaluation->attempt?->interview?->talent?->user?->name)
{{ $loop->iteration }}. **{{ $name ?: __('interview.evaluation_digest.mail.unnamed', ['id' => $evaluation->id]) }}** — {{ (int) round($evaluation->overall_score) }}/100{{ $evaluation->recommendation ? ' · ' . __('interview.evaluation_digest.recommendation.' . $evaluation->recommendation) : '' }}
@endforeach

@if($remaining > 0)
{{ __('interview.evaluation_digest.mail.and_more', ['count' => $remaining]) }}
@endif
</x-mail::panel>
@endif

<x-mail::button :url="$url">
{{ __('interview.evaluation_digest.mail.cta') }}
</x-mail::button>

{{ __('interview.evaluation_digest.mail.footer') }}
</x-mail::message>
