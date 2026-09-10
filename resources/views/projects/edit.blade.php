@extends('layouts.app')

@section('title', $project->title)

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12 px-4 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb pt-3">
                        <li class="breadcrumb-item"><a href="#">{{ __("projects/show.home") }}</a></li>
                        <li class="breadcrumb-item"><a href="#">{{ __("projects/show.jobs") }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $project->title }}</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-end align-items-center">
                    <a href="">
                        <svg width="43" height="43" viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32.25 37.625L21.4988 30.9062L10.75 37.625V8.0625C10.75 7.70612 10.8916 7.36433 11.1436 7.11233C11.3956 6.86032 11.7374 6.71875 12.0938 6.71875H30.9062C31.2626 6.71875 31.6044 6.86032 31.8564 7.11233C32.1084 7.36433 32.25 7.70612 32.25 8.0625V37.625Z" stroke="#0080FF" stroke-width="1.78125" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="">
                        <svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_2643_31718)">
                                <path d="M25.9766 0H9.02344C6.63027 0 4.33513 0.950681 2.6429 2.6429C0.950681 4.33513 0 6.63027 0 9.02344L0 25.9766C0 28.3697 0.950681 30.6649 2.6429 32.3571C4.33513 34.0493 6.63027 35 9.02344 35H25.9766C28.3697 35 30.6649 34.0493 32.3571 32.3571C34.0493 30.6649 35 28.3697 35 25.9766V9.02344C35 6.63027 34.0493 4.33513 32.3571 2.6429C30.6649 0.950681 28.3697 0 25.9766 0ZM14.5701 17.5C14.57 17.7366 14.5471 17.9726 14.5018 18.2048L21.3719 21.6398C21.9648 20.9822 22.7761 20.5618 23.6553 20.4565C24.5345 20.3513 25.4221 20.5682 26.1535 21.0673C26.885 21.5663 27.4108 22.3135 27.6335 23.1706C27.8562 24.0276 27.7607 24.9363 27.3647 25.7283C26.9687 26.5203 26.2991 27.1419 25.4798 27.478C24.6606 27.814 23.7473 27.8418 22.9092 27.556C22.0711 27.2702 21.3649 26.6904 20.9216 25.9238C20.4783 25.1573 20.3279 24.2561 20.4982 23.3871L13.6281 19.9521C13.1323 20.502 12.4815 20.8887 11.7615 21.0612C11.0414 21.2337 10.286 21.1839 9.59483 20.9184C8.90367 20.6528 8.30924 20.184 7.88996 19.5737C7.47067 18.9635 7.24623 18.2404 7.24623 17.5C7.24623 16.7596 7.47067 16.0365 7.88996 15.4263C8.30924 14.816 8.90367 14.3472 9.59483 14.0816C10.286 13.8161 11.0414 13.7663 11.7615 13.9388C12.4815 14.1113 13.1323 14.498 13.6281 15.0479L20.4982 11.6129C20.3278 10.7436 20.4782 9.84198 20.9216 9.07509C21.365 8.30821 22.0714 7.72806 22.9098 7.44214C23.7483 7.15621 24.6619 7.1839 25.4815 7.52008C26.3011 7.85625 26.971 8.47812 27.3672 9.27045C27.7634 10.0628 27.8589 10.9719 27.6361 11.8292C27.4133 12.6866 26.8872 13.4341 26.1554 13.9333C25.4236 14.4325 24.5357 14.6495 23.6561 14.5442C22.7766 14.4388 21.965 14.0181 21.3719 13.3602L14.5018 16.7952C14.5471 17.0274 14.57 17.2634 14.5701 17.5Z" fill="#0080FF"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_2643_31718">
                                    <rect width="35" height="35" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="col-md-12 job-banner">
                <div class="row ">
                    <div class="col-md-8">
                        <div class="job-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span>(90)</span>
                        </div>
                        <div class="job-title">
                            <h1>{{ $project->title }}</h1>
                        </div>
                        <div class="d-flex flex-wrap gap-2 my-2">
                            <div class="mb-2 job-timestamp"><i class="fa-solid fa-calendar-days"></i> {{ __("common/home.registered_on") }}: <span>{{ $project->created_at->format('M d, Y') }}</span></div>
                            <div class="mb-2 job-timestamp"><i class="fa-solid fa-rotate"></i> {{ __("common/home.updated_on") }}: <span>{{ $project->updated_at->format('M d, Y') }}</span></div>
                            <div class="mb-2 job-timestamp"><i class="fa-regular fa-hourglass-half"></i> {{ __("common/home.duration") }}: <span>{{ $project->deadline->format('M d, Y') }}</span></div>
                        </div>
                        <div class="d-flex gap-2 user-data">
                            <div class="avatar-view me-3">
                                <img src="{{ $project->company->company_logo_url ?? 'https://picusm.photos/32/32' }}" alt="" class="img-fluid" height="32" width="32"/>
                                <span class="online"></span>
                            </div>
                            <h6 class="mt-3">{{ $project->company->company_name ?? Auth::user()->name }}</h6>
                        </div>
                    </div>
                    <div class="col-md-4 pt-5">
                        <img src="{{ asset("images/project-detail-hero-image.png") }}" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
            <div class="col-md-12 job-detail">
                <livewire:projects.apply-for-project :project="$project"/>
            </div>
        </div>
        <div class="row align-items-center similar-projects">
            <div class="col-md-12">
                <h4 class="you-may-like">{{ __("projects/show.project_you_may_like") }}</h4>
            </div>
            @foreach(\App\Models\Project::where('id', '!=', $project->id)->limit(3)->get() as $similar_project)
                <div class="col-md-4 mb-5">
                    <div class="project-card">
                        <a href="" class="add-to-favourite"><i class="fas fa-star"></i> </a>
                        <div class="project-card-body p-3">
                            <div class="d-md-flex justify-content-between">
                                <div class="d-inline-flex pe-2 project-payout-type">{{ __("common/home.fixed") }}</div>
                                <span class="project-date">{{ $similar_project->created_at->format('M d, Y') }}</span>
                            </div>
                            <h2 class="project-title">{{ $similar_project->title }}</h2>
                            <h6 class="project-budget">{{ __("projects/show.project_budget") }}: <span>{{ __("talents/index.currency_text") }}{{ $project->salary_range }}/{{ __("common/home.month") }}</span></h6>
                            <p class="project-overview">{{ \Illuminate\Support\Str::limit($similar_project->project_description, 180) }}</p>
                            <div class="d-flex gap-3">
                                <h5 class="project-location"><i class="fas fa-location-dot"></i> {{ __("common/home.japan_tokyo") }}</h5>
                                <h6 class="project-location">{{ __("projects/show.remote") }}</h6>
                            </div>
                            <div class="d-flex gap-2 my-2">
                                @foreach($similar_project->subCategories as $subcategory)
                                    <div class="project-skill">{{ $subcategory->title }}</div>
                                    @break($loop->iteration >= 3)
                                @endforeach
                                @if($similar_project->subCategories->count() > 3)
                                    <div class="project-skill more"> {{ $similar_project->subCategories->count() - 3 }}+</div>
                                @endif
                            </div>
                        </div>
                        <div class="project-card-footer">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="{{ $similar_project->company->company_logo_url }}" alt="..." class="img-avatar" height="32" width="32">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div class="avatar">
                                            <span class="text-break">{{ $similar_project->company->company_name ?? __("pro") }}</span>
                                            <i class="fa-regular fa-circle-check"></i>
                                        </div>
                                        <a href="{{ route("project.show", $similar_project) }}" class="btn btn-theme">{{ __("projects/show.project_detail") }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
