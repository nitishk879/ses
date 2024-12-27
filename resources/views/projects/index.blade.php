@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="container-fluid container-lg pb-4" id="dashboard">
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
                @php($company = Auth::user()->company)
                @if($company && $company->projects->count() >=1)
                    @foreach($company->projects as $project)
                        <div class="job-list mt-4">
                            @can('update', $project)
                                <a href="{{ route("project.edit", $project) }}" class="add-to-favourite">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                            @endcan
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
                                        <h4 class="job-budget"><i class="fa-solid fa-yen-sign"></i> {{ $project->salary_range }}/{{ __("common/home.month") }}</h4>
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
                            @can('delete', $project)
                                <form method="POST" action="{{ route("project.destroy", $project) }}" x-data>
                                    @csrf @method('DELETE')
                                    <a class="remove-from-favourite" href="{{ route("project.destroy", $project) }}" x-on:click.prevent="$root.submit();"><i class="fa-solid fa-trash"></i></a>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                @else
                    <livewire:projects.search-result />
                @endif
            </div>
        </div>
    </div>
@endsection

@section('select2', true)

@push('scripts')
    <script>
        // Small using Bootstrap 5 classes
        $( '#multiple-select-field' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
        } );
    </script>
    @vite(['resources/js/main.js'])
@endpush

