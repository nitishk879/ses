{{-- The summary a recruiter gets when a matching run finishes. --}}
<x-mail::message>
# {{ __('interview.match_run.mail.heading', ['count' => $run->matched]) }}

@if(filled($recruiterName ?? null))
{{ __('interview.match_run.mail.greeting', ['name' => $recruiterName]) }}
@endif

{{ __('interview.match_run.mail.intro', [
    'count' => $run->matched,
    'project' => $project?->title ?? '',
    'scored' => $run->scored,
]) }}

@if($run->needs_review > 0)
{{ __('interview.match_run.mail.needs_review', ['count' => $run->needs_review]) }}
@endif

@if($preview->isNotEmpty())
<x-mail::panel>
**{{ __('interview.match_run.mail.preview_heading') }}**

@foreach($preview as $match)
{{ $loop->iteration }}. **{{ $match->talent?->user?->name ?: __('interview.match_run.mail.unnamed', ['id' => $match->talent_id]) }}** — {{ $match->score }}%
@endforeach

@if($remaining > 0)
{{ __('interview.match_run.mail.and_more', ['count' => $remaining]) }}
@endif
</x-mail::panel>
@endif

<x-mail::button :url="$url">
{{ __('interview.match_run.mail.cta') }}
</x-mail::button>

@if($run->parse_failures > 0)
{{-- Said plainly rather than left out. A pool with unreadable CVs in it is a
     smaller pool than the recruiter thinks, and they can only fix what they
     are told about. --}}
{{ __('interview.match_run.mail.parse_failures', ['count' => $run->parse_failures]) }}
@endif

{{ __('interview.match_run.mail.footer') }}
</x-mail::message>
