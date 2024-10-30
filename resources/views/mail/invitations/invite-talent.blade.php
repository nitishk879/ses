<x-mail::message>
# {{ $project->title ?? __("projects/invitation.subject", ["project" => $project->title]) }}

<x-mail::panel>
    @foreach($project->talents as $talent)
        {!! $talent->pivot->remarks ?? '' !!}
        @if ($loop->first)
            @break
        @endif
    @endforeach
</x-mail::panel>

<x-mail::button :url="route('project.show', $project)">
Project
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
