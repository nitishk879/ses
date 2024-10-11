@extends('layouts.app')

@section('content')
<div class="container" id="dashboard">
    <div class="row justify-content-center">
        <x-sidebar />
        <div class="col-md-9 col-xl-8">
            <div class="d-flex w-100 justify-content-between my-3">
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
            @foreach(\App\Models\Project::all() as $project)
                <div class="job-list">
                    <a href="" class="add-to-favourite">
                        <i class="fa-solid fa-star"></i>
                    </a>
                    <div class="job-content">
                        <div class="row justify-content-between">
                            <div class="col-md-6 d-flex gap-2">
                                <span class="job-status">{{ __("common/home.new") }}</span>
                                <span class="job-location"><i class="fa-solid fa-location-dot"></i> {{ __("common/home.japan_tokyo") }}</span>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="" class="add-to-wishlist"><i class="fa-solid fa-heart"></i> 54</a>
                            </div>
                            <div class="col-md-12 text-end">
                                <h4 class="job-budget"><i class="fa-solid fa-yen-sign"></i> {{ $project->minimum_price }}-{{ $project->maximum_price }}/{{ __("common/home.month") }}</h4>
                            </div>
                            <div class="col-md-12">
                                <h3 class="job-title">{{ $project->title }}</h3>
                            </div>
                            <div class="col-md-12 text-end">
                                <h6 class="job-paid-type">{{ __("common/home.fixed_price") }}</h6>
                            </div>
                            <div class="col-md-12 d-flex justify-content-between">
                                <div class="row">
                                    <div class="col-md-6 mb-2 job-listing-date"><i class="fa-solid fa-calendar-days"></i>
                                        {{ __("common/home.registered_on") }}: {{ $project->created_at->format('M d, Y') }}</div>
                                    <div class="col-md-6 mb-2 job-updated"><i class="fa-solid fa-rotate"></i> {{ __("common/home.updated_on") }}: {{ $project->updated_at->format('M d, Y') }}</div>
                                    <div class="col job-duration"><i class="fa-regular fa-hourglass-half"></i> {{ __("common/home.duration") }}: {{ $project->deadline->format('M d, Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-12 text-end">
                                <a href="{{ route("project.show", $project) ?? __("/project/{$project->slug}") }}" class="btn job-btn-summary">{{ __("common/home.summary") }}</a>
                            </div>
                            <div class="col-md-10">
                                <p class="job-overview">
                                    {!! \Illuminate\Support\Str::limit($project->project_description, 200) !!}
                                </p>
                            </div>
                            <div class="col-md-12 job-pills">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center">
                                        <div class="job-pill">{{ __('common/home.apply') }}: <span>{{ today()->format('d/m/y') }}</span></div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <div class="job-pill">{{ __("common/home.contract_price") }}: <span>{{ __("common/home.fixed") }}</span></div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <div class="job-pill">{{ __("common/home.no_of_interviews") }}: <span>{{ __('5') }}</span></div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <div class="job-pill">{{ __("common/home.eligibility") }}: <span>{{ __('B.Tech') }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
