<x-mail::message>
# Project Saved: {{ $project->title ?? '' }}

Hi! Project {{$project->title }} has been saved for later and hope you are going to bid it later and updated things.

<x-mail::button :url="route('project.show', $project)">
Check Project
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
