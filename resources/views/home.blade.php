@extends('layouts.app')

@section('content')
<div class="container-fluid container-lg" id="dashboard">
    <div class="row justify-content-center">
        <div class="col-md-4 col-xl-3 offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
            <div class="offcanvas-body">
                <livewire:projects.search-form />
            </div>
        </div>
        <div class="col-md-8 col-xl-9">
            <!-- Top Nav -->
            <div class="my-2">
                <button class="btn btn-primary d-md-none offCanvasBtn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
            </div>
            <!--End Top Nav -->
            <div class="d-md-flex w-100 justify-content-between my-3">
                <h2 class="search-keyword">{{ __('common/home.showing_results_for') }}: <span>"{{ __("common/home.search_keyword") }}"</span></h2>
                <div class="search-sort">
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="inputGroupSelect01">{{ __("common/home.sort_by") }}</label>
                        <select class="form-select" id="inputGroupSelect01">
                            <option selected>Recent Listing</option>
                            <option value="1">Updated on</option>
                            <option value="4">Favorite</option>
                            <option value="2">Registration date</option>
                            <option value="3">Application Deadline</option>
                        </select>
                    </div>
                </div>
            </div>
            @php($projects = \App\Models\Project::paginate(10))
            @foreach($projects as $project)
                <x-ui.card-layout :item="$project">
                    <x-slot:header>
                        <div class="col-md-6 d-flex gap-2">
                            @if($project->created_at >= today()->subMonth())
                                <span class="job-status">{{ __("common/home.new") }}</span>
                            @endif
                            @if($project->deadline <= today()->addMonth())
                                <span class="job-status">{{ __("Urgent") }}</span>
                            @endif
                            <span class="job-location"><i class="fa-solid fa-location-dot"></i> {{ $project->locations->first()->title ?? __("common/home.japan_tokyo") }}</span>
                        </div>
                        <div class="col-md-6 text-end">
                            <h4 class="job-budget"><i class="fa-solid fa-yen-sign"></i> {{ $project->salary_range }}</h4>
                        </div>
                    </x-slot:header>
                    <!--- Card Body content ---->
                    <div class="col-md-8 mb-4 ps-md-3">
                        <h3 class="job-title">{{ $project->title ?? '' }}</h3>
                    </div>
                    <div class="col-md-4 mb-4 text-end">
                        <h6 class="">{{ __('projects/form.remote_operation_possible') }}:
                            <span>{!! $project->remote_operation_possible ? '<i class="fa-regular fa-circle-check text-success"></i>' : '<i class="fa-regular fa-circle-xmark text-danger"></i>' !!}</span>
                        </h6>
                    </div>
                    <div class="col-md-8">
                        <p class="job-overview">
                            {!! \Illuminate\Support\Str::limit($project->project_description, 200) !!}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-grid d-md-block gap-2">
                            <a href="{{ route("project.show", $project) }}" class="btn btn-sm btn-primary">{{ __("common/index.apply_now") }}</a>
                            {{--                    <a href="{{ route("project.save-for-later", $project) }}"--}}
                            {{--                       onclick="event.preventDefault(); document.getElementById('save-for-later').submit();"--}}
                            {{--                       class="btn btn-sm btn-secondary" @if($project->saves->contains($project->id)) disabled @endif>{{ __("common/index.apply_later") }}</a>--}}
                            <form action="{{ route('project.save-for-later', $project) }}" method="post" id="save-for-later" class="d-inline">
                                @csrf
                                <input type="hidden" name="project_id" value="{{ $project->id }}">
                                <button type="submit" class="btn btn-sm btn-secondary" @if($project->saves->contains($project->id)) disabled @endif>{{ __("common/index.apply_later") }}</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-12 job-pills">
                        <div class="row align-items-center">
                            <div class="col-md text-center">
                                <div class="job-pill">{{ __('common/home.apply') }}: <span>{{ today()->format('d/m/y') }}</span></div>
                            </div>
                            <div class="col-md text-center">
                                <div class="job-pill">{{ __("projects/form.remote_operation_possible") }}:
                                    <span>{!! $project->remote_operation_possible ? '<i class="fa-regular fa-circle-check text-success"></i>' : '<i class="fa-regular fa-circle-xmark text-danger"></i>' !!}</span>
                                </div>
                            </div>
                            <div class="col-md text-center">
                                <div class="job-pill">{{ __("common/home.no_of_interviews") }}: <span>{{ \App\Enums\InterviewEnum::toName($project->number_of_interviewers) ?? __('5') }}</span></div>
                            </div>
                            <div class="col-md text-center">
                                <div class="job-pill">{{ __("common/home.eligibility") }}: <span>{{ __('B.Tech') }}</span></div>
                            </div>
                        </div>
                    </div>
                    <!--- Card Body content ---->
                </x-ui.card-layout>
            @endforeach
            {{ $projects->links() }}
        </div>

    </div>
</div>
@endsection
