@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="page-heading">{{ __('projects/form.project_registration') }}</h1>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <form class="row justify-content-center" action="{{ route("project.store") }}" method="post">
                    @csrf
                    <div class="col-md-6">
                        <div class="col-md-12 mb-4">
                            <div class="bg-light p-3">
                                <div class="col-md-12 mb-3">
                                    <label for="projectTitle" class="col-form-label required">{{ __("projects/form.project_title") }}</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           id="projectTitle"
                                           name="title"
                                           placeholder="{{ __("projects/form.enter_your_project_title") }}"
                                           value="{{ old('title') ?? '' }}" aria-label="Project Title" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="projectDescription" class="form-label">{{ __('projects/form.project_description') }}</label>
                                    <textarea class="form-control tinyEditor @error('project_description') is-invalid @enderror"
                                              id="projectDescription"
                                              name="project_description"
                                              rows="3"
                                              placeholder="{{ __('talents/registration.write_bio') }}">{!! old("project_description") ?? '' !!}</textarea>
                                    @error('project_description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="ms-auto text-end mt-2">
                                        <a href="#" onclick="openDynamicModal(1)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="projectRequirement" class="form-label">{{ __('projects/form.project_requirements') }}</label>
                                    <textarea class="form-control tinyEditor @error('personnel_requirement') is-invalid @enderror"
                                              id="projectRequirement"
                                              name="personnel_requirement"
                                              rows="3"
                                              placeholder="{{ __('talents/registration.write_bio') }}">{!! old("personnel_requirement") ?? '' !!}</textarea>
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
                                           value="{{ old("person_in_charge") ?? '' }}"
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
                                               value="{{ old("contract_start_date") ?? '' }}"
                                               aria-label="Company website"
                                               required
                                        >
                                        @error('contract_start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" class="form-control @error('contract_end_date') is-invalid @enderror"
                                               id="companyPhone"
                                               name="contract_end_date"
                                               placeholder="{{ __("projects/form.project_end_date") }}"
                                               value="{{ old("contract_end_date") ?? '' }}"
                                               aria-label="Company phone"
                                               required
                                        >
                                        @error('contract_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.status_of_the_project") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="accept" value="{{ old("accept" ?? 1) }}" id="projectStatus">
                                            <label class="form-check-label" for="projectStatus">{{ __("common/sidebar.confirmed") }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.project_published") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="is_public" value="{{ old("is_public" ?? 1) }}" id="projectStatus">
                                            <label class="form-check-label" for="projectStatus">{{ __("projects/form.public") }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.company_information_disclosure_settings") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="company_info_disclose" value="{{ old("company_info_disclose" ?? 1) }}" id="projectStatus">
                                            <label class="form-check-label" for="projectStatus">{{ __("projects/form.public") }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.skill_matching") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="skill_matching" value="{{ old("skill_matching" ?? 1) }}" id="projectSkill" aria-label="{{ __("projects/form.skill_matching") }}">
                                            {{--                                            <label class="form-check-label" for="projectSkill">{{ __("projects/form.skill_matching") }}</label>--}}
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectStatus" class="col-form-label required">{{ __("projects/form.project_finalise") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="project_finalized" value="{{ old("project_finalized" ?? 1) }}" id="projectSkill" aria-label="{{ __("projects/form.project_finalise") }}">
                                            {{--                                            <label class="form-check-label" for="projectSkill">{{ __("projects/form.skill_matching") }}</label>--}}
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectSustainability" class="col-form-label required">{{ __("projects/form.possible_to_continue") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="possible_to_continue" value="{{ old("possible_to_continue" ?? 1) }}" id="projectSustainability" aria-label="{{ __("projects/form.possible_to_continue") }}">
                                            {{--                                            <label class="form-check-label" for="projectSkill">{{ __("projects/form.skill_matching") }}</label>--}}
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="projectSustainability" class="col-form-label required">{{ __("projects/form.remote_operation_possible") }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="remote_operation_possible" value="{{ old("remote_operation_possible" ?? 1) }}" id="projectSustainability" aria-label="{{ __("projects/form.remote_operation_possible") }}">
                                            {{--                                            <label class="form-check-label" for="projectSkill">{{ __("projects/form.skill_matching") }}</label>--}}
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
                                            <input type="text" class="form-control @error('minimum_price') is-invalid @enderror"
                                                   name="minimum_price"
                                                   id="expectedMinSalary"
                                                   value="{{ old("minimum_price") ?? '' }}"
                                                   placeholder="{{ __('projects/form.salary_min_placeholder') }}" required
                                            />
                                            @error('minimum_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="maxBudget">{{ __("common/sidebar.max_salary") }}</label>
                                            <input type="text" class="form-control @error('maximum_price') is-invalid @enderror"
                                                   name="maximum_price"
                                                   id="expectedMaxSalary"
                                                   value="{{ old("maximum_price") ?? '' }}"
                                                   placeholder="{{ __('projects/form.salary_max_placeholder') }}" required
                                            />
                                            @error('maximum_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label" for="maxBudget">{{ __("projects/form.locations") }}</label>
                                    </div>
                                    @foreach(\App\Models\Location::orderBy('title')->get() as $location)
                                        <div class="mb-3 col-md-4">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox"
                                                       name="locations[]"
                                                       id="{{ $location->slug."_".$location->id }}"
                                                       value="{{ $location->id }}">
                                                <label class="form-check-label"
                                                       for="{{ $location->slug."_".$location->id }}">{{ $location->title ?? '' }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label" for="maxBudget">{{ __("projects/form.work_mode") }}</label>
                                    </div>
                                    @foreach(\App\Enums\WorkLocationEnum::cases() as $workLocation)
                                        <div class="mb-3 col-md-6">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox"
                                                       name="workLocations[]"
                                                       id="{{ __("work_{$workLocation->value}") }}"
                                                       value="{{ $workLocation->value }}">
                                                <label class="form-check-label"
                                                       for="{{ __("work_{$workLocation->value}") }}">{{ __("common/sidebar.{$workLocation->name}") ?? '' }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
{{--                                <div class="mb-3">--}}
{{--                                    <label for="preferredLocation"--}}
{{--                                           class="form-label">{{ __('talents/registration.preferred_location') }}</label>--}}
{{--                                    <input type="text" class="form-control @errors('preferred_location') is-invalid @enderror"--}}
{{--                                           name="preferred_location" id="preferredLocation"--}}
{{--                                           placeholder="{{ __('talents/registration.location_placeholder') }}"--}}
{{--                                           value="{{ old("preferred_location") ?? '' }}"--}}
{{--                                           >--}}
{{--                                    @errors('preferred_location')--}}
{{--                                    <div class="invalid-feedback d-block">{{ $message }}</div>--}}
{{--                                    @enderror--}}
{{--                                </div>--}}
{{--                                <div class="mb-3">--}}
{{--                                    <label for="otherDesiredLocation"--}}
{{--                                           class="form-label">{{ __('talents/registration.other_desired_location') }}</label>--}}
{{--                                    <input type="text"--}}
{{--                                           class="form-control @errors('other_desired_location') is-invalid @enderror"--}}
{{--                                           name="other_desired_location" id="otherDesiredLocation"--}}
{{--                                           value="{{ old("other_desired_location") ?? "" }}"--}}
{{--                                           placeholder="{{ __('talents/registration.location_placeholder') }}">--}}
{{--                                    @errors('other_desired_location')--}}
{{--                                    <div class="invalid-feedback d-block">{{ $message }}</div>--}}
{{--                                    @enderror--}}
{{--                                </div>--}}
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
                                                <input class="form-check-input" type="radio" name="trade_classification" value="{{$trade->value}}" id="category_{{$trade->value}}">
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
                                                <input class="form-check-input" type="radio" name="contract_classification" value="{{$contract->value}}" id="category_{{$contract->value}}">
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
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="eligibility[]" value="{{$eligible->value}}" id="category_{{$eligible->value}}">
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
                                                <input class="form-check-input" type="checkbox" name="scoring[]" value="{{$score->value}}" id="category_{{$score->value}}">
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
                                    @foreach($categories as $category)
                                        <h4>{{ $category->title }}</h4>
                                        <div class="mb-3">
                                            @foreach($category->subCategories as $subCategory)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="category_id[]" value="{{$subCategory->id}}" id="category_{{$subCategory->id}}">
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
                                    @foreach($features as $feature)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="project_features[]" value="{{$feature->id}}" id="feature_{{$feature->id}}">
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
                                               value="{{ old("deadline") ?? '' }}"
                                               placeholder="{{ __('projects/form.deadline_placeholder') }}" required
                                        />
                                        @error('deadline')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="expectedApplications" class="form-label required">{{ __('projects/form.no_of_application') }}</label>
                                        <input type="number" class="form-control @error('number_of_application') is-invalid @enderror"
                                               name="number_of_application"
                                               id="expectedApplications"
                                               value="{{ old("number_of_application") ?? '' }}"
                                               placeholder="{{ __('projects/form.no_of_application_placeholder') }}" required
                                        />
                                        @error('number_of_application')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <h5>{{ __("projects/form.number_of_interview") }}</h5>
                                    @foreach(\App\Enums\InterviewEnum::cases() as $i)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="number_of_interviewers" value="{{$i->value}}" id="interview_{{$i->value}}">
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
                                                    <option value="{{ $case->value }}" {{ $loop->first ? 'selected' : '' }}>{{ __("projects/form.{$case->name}") ?? __('One') }}</option>
                                                @endforeach
                                            </select>
                                            @error('commercial_flow')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h5>{{ __("talents/registration.language") }}</h5>
                                        @foreach(\App\Enums\LangEnum::cases() as $lang)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="languages" value="{{$lang->value}}" id="language_{{$lang->value}}">
                                                <label class="form-check-label" for="language_{{$lang->value}}">
                                                    {{ __("common/sidebar.{$lang->name}") ?? __("projects/form.interview_{$lang->value}") }}
                                                </label>
                                                @error('languages') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-md-12">
                                        <h5>{{ __("projects/form.experience") }}</h5>
                                        <div class="form-group">
                                            <label class="form-label" for="experience">
                                                {{ __("projects/form.experience_x") }}
                                            </label>
                                            <textarea class="form-control" name="experience" id="experience">{{ old('experience' ?? '') }}</textarea>
                                            @error('experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
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
                    <div class="col-md-12 mb-3 text-center">
                        @foreach($errors as $error)
                            <p>{{ $message }}</p>
                        @endforeach
                        <button type="submit" class="btn btn-primary">{{ __("Submit") }}</button>
                    </div>
                </form>
            </div>
{{--            <livewire:projects.new-project />--}}
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="d-flex justify-content-center align-items-center w-100">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">{{ __("Sample Cover Letter") }}</h1>
                </div>
                <div class="modal-body">
                    <div class="cover-letter ">
                        <h4 id="modalTitle">{{ __("Cover Letter") }}</h4>
                        <div class="cover-letter-description border border-secondary-subtle p-4"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Understood</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!---- Summer note libraries -->
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
        <script>
            $('.tinyEditor').summernote({
                placeholder: "{{ __("talents/registration.write_bio") }}",
                tabsize: 2,
                height: 120,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        </script>
        <!---- Summer note libraries -->
        <!-- Add Axios via CDN (optional if not already included) -->
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <!-- Modal Popup call -->
        <script>
            function openDynamicModal(id) {
                // Make an Axios request to fetch data for the modal
                axios.get('/sample/' + id)
                    .then(response => {
                        const data = response.data;
                        console.log(data);
                        // Set the modal title and content dynamically
                        document.getElementById('staticBackdropLabel').textContent = data.title;
                        document.getElementById('modalTitle').textContent = data.title;
                        document.querySelector('.cover-letter-description').innerHTML = data.content;
                    })
                    .catch(error => {
                        console.error('There was an errors fetching the data!', error);
                        alert('Failed to fetch data.');
                    });
            }
        </script>
        <!-- Modal Popup call -->
    @endpush
@endsection
