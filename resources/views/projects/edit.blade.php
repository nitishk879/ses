@extends('layouts.app')

@section('title', $project->title)

@section('content')
    <div class="container-fluid container-lg" id="dashboard">
        <div class="row">
            <div class="col-md-12 px-4 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb pt-3">
                        <li class="breadcrumb-item"><a href="#">{{ __("projects/show.home") }}</a></li>
                        <li class="breadcrumb-item"><a href="#">{{ __("projects/form.update_project") }}</a></li>
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
            <div class="col-md-12">
                <form class="row justify-content-center" id="progressForm" action="{{ route("project.update", $project) }}" method="post">
                    @csrf @method('PUT')
                    <div class="col-md-6">
                        <div class="col-md-12 mb-4">
                            <div class="bg-light p-3">
                                <div class="col-md-12 mb-3">
                                    <label for="projectTitle" class="col-form-label required">{{ __("projects/form.project_title") }}</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           id="projectTitle"
                                           name="title"
                                           placeholder="{{ __("projects/form.enter_your_project_title") }}"
                                           value="{{ $project->title ?? old('title') ?? '' }}" aria-label="Project Title" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="targetTextarea1" class="form-label">{{ __('projects/form.project_description') }}</label>
                                    <textarea class="form-control tinyEditor @error('project_description') is-invalid @enderror"
                                              id="targetTextarea1"
                                              name="project_description"
                                              rows="3"
                                              placeholder="{{ __('talents/registration.project_description_placeholder') }}">{!! $project->project_description ?? old("project_description") ?? '' !!}</textarea>
                                    @error('project_description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="ms-auto text-end mt-2">
                                        <a href="#" onclick="openDynamicModal(1)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="targetTextarea2" class="form-label">{{ __('projects/form.project_requirements') }}</label>
                                    <textarea class="form-control tinyEditor @error('personnel_requirement') is-invalid @enderror"
                                              id="targetTextarea2"
                                              name="personnel_requirement"
                                              rows="3"
                                              placeholder="{{ __('talents/registration.project_requirements_placeholder') }}">{!! $project->personnel_requirement ?? old("personnel_requirement") ?? '' !!}</textarea>
                                    @error('personnel_requirement')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="ms-auto text-end mt-2">
                                        <a href="" onclick="openDynamicModal(2)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-id="" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="projectInCharge" class="col-form-label required">{{ __("projects/form.project_in_charge_name") }}</label>
                                    <input type="text" class="form-control @error('person_in_charge') is-invalid @enderror"
                                           id="projectInCharge"
                                           name="person_in_charge"
                                           placeholder="{{ __("projects/form.project_in_charge_name") }}"
                                           value="{{ $project->person_in_charge ?? old("person_in_charge") ?? '' }}"
                                           aria-label="Project In-charge Name"
                                           required
                                    >
                                    @error('person_in_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12 mb-3">
                                        <label for="projectStarted" class="form-label required">{{ __("projects/form.contract_period") }}</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" class="form-control @error('contract_start_date') is-invalid @enderror"
                                               id="projectStarted"
                                               name="contract_start_date"
                                               placeholder="{{ __("projects/form.project_start_date") }}"
                                               value="{{ $project->contract_start_date->format('Y-m-d') ?? old("contract_start_date") ?? '' }}"
                                               aria-label="Start date"
                                               required
                                        >
                                        @error('contract_start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" class="form-control @error('contract_end_date') is-invalid @enderror"
                                               id="contractEndDate"
                                               name="contract_end_date"
                                               placeholder="{{ __("projects/form.project_end_date") }}"
                                               value="{{ $project->contract_end_date->format('Y-m-d') ?? old("contract_end_date") ?? '' }}"
                                               aria-label="contract end date"
                                               required
                                        >
                                        @error('contract_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.status_of_the_project") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="accept" @checked($project->accept==1 ? 1: 0 ?? old('accept', $project->accept)) value="{{ $project->accept ?? old("accept" ?? 1) }}" id="projectStatus">
                                            <label class="form-check-label" for="projectStatus">{{ __("common/sidebar.confirmed") }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.project_published") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="is_public" value="1" id="projectVisibility"
                                                @checked($project->is_public==1 ? 1: 0 ?? old('is_public', $project->is_public))
                                            >
                                            <label class="form-check-label" for="projectStatus">{{ __("projects/form.public") }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.company_information_disclosure_settings") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="company_info_disclose" value="1" id="projectInfoDisclouser"
                                                @checked($project->company_info_disclose ?? old('company_info_disclose', $project->company_info_disclose))
                                            >
                                            <label class="form-check-label" for="projectStatus">{{ __("projects/form.public") }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.skill_matching") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="skill_matching" value="1" id="projectSkill" aria-label="{{ __("projects/form.skill_matching") }}"
                                                @checked($project->skill_matching ?? old('skill_matching', $project->skill_matching))
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.project_finalise") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="project_finalized" value="1" id="projectSkill" aria-label="{{ __("projects/form.project_finalise") }}"
                                                @checked($project->project_finalized ?? old('project_finalized', $project->project_finalized))
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectSustainability" class="col-form-label required">{{ __("projects/form.possible_to_continue") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="possible_to_continue" value="1" id="projectSustainability" aria-label="{{ __("projects/form.possible_to_continue") }}"
                                                @checked($project->possible_to_continue ?? old('possible_to_continue', $project->possible_to_continue))
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectSustainability" class="col-form-label required">{{ __("projects/form.remote_operation_possible") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="remote_operation_possible" value="1" id="projectSustainability" aria-label="{{ __("projects/form.remote_operation_possible") }}"
                                                @checked($project->remote_operation_possible ?? old('remote_operation_possible', $project->remote_operation_possible))
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-4">
                            <div class="bg-light p-3">
                                <div class="mb-3">
                                    <label for="expectedMinSalary" class="form-label required">{{ __('talents/registration.expected_salary') }}</label>
                                    <div class="row align-items-center">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="minBudget">{{ __("common/sidebar.min_salary") }}</label>
                                            <input type="number" class="form-control @error('minimum_price') is-invalid @enderror"
                                                   name="minimum_price"
                                                   id="expectedMinSalary"
                                                   value="{{ $project->minimum_price ?? old("minimum_price") ?? '' }}"
                                                   placeholder="{{ __('projects/form.salary_min_placeholder') }}" required
                                            />
                                            @error('minimum_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="maxBudget">{{ __("common/sidebar.max_salary") }}</label>
                                            <input type="number" class="form-control @error('maximum_price') is-invalid @enderror"
                                                   name="maximum_price"
                                                   id="expectedMaxSalary"
                                                   value="{{ $project->maximum_price ?? old("maximum_price") ?? '' }}"
                                                   placeholder="{{ __('projects/form.salary_max_placeholder') }}" required
                                            />
                                            @error('maximum_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label" for="locations">{{ __("projects/form.locations") }}</label>
                                    </div>
                                    <div class="mb-3 col-md-12">
                                        <select class="form-select form-select-sm" name="locations[]" id="multiple-select-field" data-placeholder="{{ __("talents/registration.choose") }}" multiple>
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Models\Location::orderBy('title')->get() as $location)
                                                <option value="{{ $location->id }}" @selected($project->locations->contains('id', $location->id) ?? old('locations', $location->id))>{{ $location->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
{{--                                    @foreach(\App\Models\Location::orderBy('title')->get() as $location)--}}
{{--                                        <div class="mb-3 col-md-4">--}}
{{--                                            <div class="form-check form-check-inline">--}}
{{--                                                <input class="form-check-input" type="checkbox"--}}
{{--                                                       name="locations[]"--}}
{{--                                                       id="{{ $location->slug."_".$location->id }}"--}}
{{--                                                       value="{{ $location->id }}"--}}
{{--                                                       @checked($project->locations->contains('id', $location->id) ?? old('locations', $location->id))--}}
{{--                                                >--}}
{{--                                                <label class="form-check-label"--}}
{{--                                                       for="{{ $location->slug."_".$location->id }}">{{ $location->title ?? '' }}</label>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    @endforeach--}}
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label" for="locations">{{ __("projects/form.work_mode") }}</label>
                                    </div>
                                    @foreach(\App\Enums\WorkLocationEnum::cases() as $workLocation)
                                        <div class="mb-3 col-md-6">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox"
                                                       name="workLocations[]"
                                                       id="{{ __("work_{$workLocation->value}") }}"
                                                       value="{{ $workLocation->value }}"
                                                    @checked(in_array($workLocation->value, old('workLocations', $project->work_location_prefer ?? [])))
                                                >
                                                <label class="form-check-label"
                                                       for="{{ __("work_{$workLocation->value}") }}">{{ __("common/sidebar.{$workLocation->name}") ?? '' }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="col-md-12 mb-4">
                            <div class="bg-light p-3">
                                <div class="row">
                                    <div class="col-md-12 mb-1">
                                        <h4>{{ __("projects/form.project_flow") }}</h4>
                                        @foreach(\App\Enums\TradeClassification::cases() as $trade)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="trade_classification" value="{{$trade->value}}" id="category_{{$trade->value}}"
                                                       @checked(old('trade_classification', $project->trade_classification))
                                                >
                                                <label class="form-check-label" for="category_{{$trade->value}}">
                                                    {{ __("projects/form.{$trade->name}") }}
                                                </label>
                                                @error('trade_classification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <h4>{{ __("projects/form.contract_type") }}</h4>
                                        @foreach(\App\Enums\ContractClassificationEnum::cases() as $contract)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="contract_classification" value="{{$contract->value}}" id="category_{{$contract->value}}"
                                                       @checked(old('contract_classification', $project->contract_classification))
                                                >
                                                <label class="form-check-label" for="category_{{$contract->value}}">
                                                    {{ __("projects/form.{$contract->name}") }}
                                                </label>
                                                @error('contract_classification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <h4>{{ __("projects/form.eligibility") }}</h4>
                                        @foreach(\App\Enums\AffiliationEnum::cases() as $eligible)
                                            <div class="form-check form-check-inline" data-bs-popper="{{ $eligible->value }}" data-bs-target="{{ $project->eligibility }}">
                                                <input class="form-check-input" type="checkbox" name="eligibility[]" value="{{$eligible->value}}" id="category_{{$eligible->value}}"
                                                    @checked(in_array($eligible->value, old('eligibility', $project->affiliation ?? [])))
                                                >
                                                <label class="form-check-label" for="category_{{$eligible->value}}">
                                                    {{ __("projects/form.{$eligible->name}") }}
                                                </label>
                                                @error('eligibility') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <h4>{{ __("projects/form.scoring") }}</h4>
                                        @foreach(\App\Enums\ScoringEnum::cases() as $score)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="scoring[]" value="{{$score->value}}" id="category_{{$score->value}}"
                                                    @checked(in_array($score->value, old('scoring', $project->scoring ?? [])))
                                                >
                                                <label class="form-check-label" for="category_{{$score->value}}">
                                                    {{ __("projects/form.score_{$score->name}") }}
                                                </label>
                                                @error('eligibility') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __("projects/form.categories") }}</h2>
                                <div class="mb-3">
                                    @foreach(\App\Models\Category::orderBy('title')->get() as $category)
                                        <h4>{{ $category->title }}</h4>
                                        <div class="mb-3">
                                            @foreach($category->subCategories as $subCategory)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="category_id[]" value="{{$subCategory->id}}" id="category_{{$subCategory->id}}"
                                                        @checked($project->subCategories->contains('id', $subCategory->id) ?? old('category_id', $subCategory->id))
                                                    >
                                                    <label class="form-check-label" for="category_{{$subCategory->id}}">
                                                        {{ $subCategory->title ?? __("company/register.dispatch_business") }}
                                                    </label>
                                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __("projects/form.project_features") }}</h2>
                                <div class="row mb-3">
                                    @foreach(\App\Models\Feature::orderBy('title')->get() as $feature)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="project_features[]" value="{{$feature->id}}" id="feature_{{$feature->id}}"
                                                    @checked($project->features->contains('id', $feature->id) ?? old('project_features', $feature->id))
                                                >
                                                <label class="form-check-label" for="category_{{$feature->id}}">
                                                    {{ __("projects/form.{$feature->slug}") }}
                                                </label>
                                                @error('project_features') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-4">
                            <div class="bg-light p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6 mb-3">
                                        <label for="projectDeadline" class="form-label required">{{ __('projects/form.deadline') }}</label>
                                        <input type="date" class="form-control @error('deadline') is-invalid @enderror"
                                               name="deadline"
                                               id="projectDeadline"
                                               value="{{ $project->deadline->format('Y-m-d') ?? old("deadline") ?? '' }}"
                                               placeholder="{{ __('projects/form.deadline_placeholder') }}" required
                                        />
                                        @error('deadline')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="expectedApplications" class="form-label required">{{ __('projects/form.no_of_application') }}</label>
                                        <input type="number" class="form-control @error('number_of_application') is-invalid @enderror"
                                               name="number_of_application"
                                               id="expectedApplications"
                                               min="0"
                                               value="{{ $project->number_of_application ?? old("number_of_application") ?? '' }}"
                                               placeholder="{{ __('projects/form.no_of_application_placeholder') }}" required
                                        />
                                        @error('number_of_application')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <h5>{{ __("projects/form.number_of_interview") }}</h5>
                                    @foreach(\App\Enums\InterviewEnum::cases() as $i)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="number_of_interviewers" value="{{$i->value}}" id="interview_{{$i->value}}"
                                                   @checked(old('number_of_interviewers', $project->number_of_interviewers))
                                            >
                                            <label class="form-check-label" for="interview_{{$i->value}}">
                                                {{ __("projects/form.interview_{$i->value}") }}
                                            </label>
                                            @error('number_of_interviewers') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    @endforeach
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="commercialFlow" class="form-label required">{{ __("projects/form.commercial_flow") }}</label>
                                            <select class="form-select @error('commercial_flow') is-invalid @enderror"
                                                    name="commercial_flow" id="commercialFlow" aria-label="commercial_flow" required>
                                                <option value="">{{ __("talents/registration.choose") }}</option>
                                                @foreach(\App\Enums\CommercialFlow::cases() as $case)
                                                    <option value="{{ $case->value }}" {{ $loop->first ? 'selected' : '' }}
                                                        @selected($project->commercial_flow == $case->name ?? old('commercial_flow', $project->commercial_flow) == $case->value)
                                                    >{{ __("projects/form.{$case->name}") ?? __('One') }}</option>
                                                @endforeach
                                            </select>
                                            @error('commercial_flow')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <h5>{{ __("projects/form.experience") }}</h5>
                                        <div class="form-group">
                                            <label class="form-label" for="experience">
                                                {{ __("projects/form.experience_x") }}
                                            </label>
                                            <textarea class="form-control" name="experience" id="experience">{{ $project->experience ?? old('experience' ?? '') }}</textarea>
                                            @error('experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <h5>{{ __("talents/registration.language") }}</h5>
                                    @foreach(\App\Enums\LangEnum::cases() as $lang)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="languages" value="{{$lang->value}}" id="language_{{$lang->value}}"
                                                @checked($project->languges ?? old('languages', $project->languages))
                                            >
                                            <label class="form-check-label" for="language_{{$lang->value}}">
                                                {{ __("common/sidebar.{$lang->name}") ?? __("projects/form.interview_{$lang->value}") }}
                                            </label>
                                            @error('languages') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="col-md-12 py-3">
                        <!-- Progress Bar -->
                        <div class="progress mb-4">
                            <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3 text-center">
                        @foreach($errors as $error)
                            <p>{{ $message }}</p>
                        @endforeach
                        <button type="submit" class="btn btn-primary">{{ __("Submit") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('modals', true)
@section('select2', true)
@push('scripts')
    <!-- Modal Popup call -->
    <script>
        $( '#multiple-select-field' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            closeOnSelect: false,
        } );
    </script>
    <!-- Modal Popup call -->
    @vite(['resources/js/main.js'])
@endpush
