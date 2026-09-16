@extends('layouts.app')

@section('title', __("talents/registration.registration"))

@section('content')
    <div class="container-fluid container-lg" id="dashboard">
        <div class="row">
            <div class="col-md-12 text-center">
                {{-- .page-heading carries margin-top 3.75rem + margin-bottom 2.25rem
                     globally, and the wrapper below added py-3 on top of the
                     progress bar's own mb-4. Three spacers stacked to roughly
                     200px of empty page above the first field. Overridden here
                     with utilities rather than by editing .page-heading, which
                     every other page also uses. --}}
                <h1 class="page-heading mt-4 mb-3">{{ __('talents/registration.registration') }}</h1>
            </div>
            <div class="col-md-12">
                <!-- Progress Bar -->
                <div class="progress mb-4">
                    <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
            </div>
        </div>
        <form action="{{ route("talents.store") }}" method="post" id="progressForm" class="col-md-12 needs-validation" data-invalid-message="{{ __('talents/registration.form_has_errors') }}" enctype="multipart/form-data" novalidate>
            @csrf
            {{-- Step one, and deliberately the whole width of the page. The CV is
                 the input that produces most of what follows — name, contact,
                 education, history, skill areas — so it is asked for before the
                 fields it fills, not after them. Everything below this card is
                 then a review pass over extracted values rather than typing. --}}
            <div class="row">
                <div class="col-md-12 bg-light mb-4">
                    <div class="bg-light p-3">
                        <h2>{{ __('talents/registration.start_with_cv') }}</h2>
                        <p class="text-muted small mb-3">{{ __('talents/registration.start_with_cv_hint') }}</p>
                        <div class="row align-items-start">
                            <div class="col-md-6 mb-3">
                                <label for="formFile" class="form-label required">{{ __("talents/registration.upload_resume") }}</label>
                                <input class="form-control @error('resume') is-invalid @enderror"
                                       type="file"
                                       name="resume"
                                       id="formFile"
                                       accept=".pdf,.doc,.docx">
                                <div class="form-text">{{ __('talents/registration.file_acceptance') }}</div>
                                @error('resume')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Opt-in rather than automatic on file selection: this is a
                                 language-model call, and someone re-picking a file three
                                 times should not pay for three of them. --}}
                            <div class="col-md-6 mb-3">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <button type="button" class="btn btn-primary"
                                            id="autofillFromResume" disabled>
                                        <span class="spinner-border spinner-border-sm d-none me-1"
                                              id="autofillSpinner" role="status" aria-hidden="true"></span>
                                        {{ __('talents/registration.autofill_button') }}
                                    </button>
                                    <span class="small text-muted">{{ __('talents/registration.autofill_hint') }}</span>
                                </div>
                                <div id="autofillResult" class="small mt-2" role="status" aria-live="polite"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="row px-2">
                        <!-- Basic detail --->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __('talents/registration.personal_information') }}</h2>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="firstName"
                                               class="col-form-label required">{{ __("talents/registration.firstname") }}</label>
                                        <input type="text" class="form-control @error('firstname') is-invalid @enderror"
                                               id="firstName" name="firstname"
                                               placeholder="{{ __("talents/registration.firstname") }}"
                                               value="{{ old('firstname') ?? '' }}" aria-label="First name" required>
                                        @error('firstname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName"
                                               class="col-form-label required">{{ __("talents/registration.lastname") }}</label>
                                        <input type="text" class="form-control @error('firstname') is-invalid @enderror"
                                               id="lastName" name="lastname"
                                               placeholder="{{ __("talents/registration.lastname") }}"
                                               value="{{ old('lastname') ?? '' }}" aria-label="Last name" required>
                                        @error('lastname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="emailAddress"
                                               class="form-label required">{{ __('talents/registration.email') }}</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                               id="emailAddress" value="{{ old('email') ?? '' }}" placeholder="johndoe@email.com"
                                               required>
                                        @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Both this field and the email input carried
                                             id="emailAddress", so clicking "Date of birth"
                                             focused the email box. --}}
                                        <label for="dateOfBirth"
                                               class="form-label required">{{ __('talents/registration.date_of_birth') }}</label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth"
                                               id="dateOfBirth" value="{{ old('date_of_birth') ?? '' }}"
                                               required>
                                        @error('date_of_birth')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- The number the screening call is placed to.
                                     Required, because an interview invitation
                                     promises a phone call: without this the
                                     candidate picks a time for a call that can
                                     never be dialled. --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phoneNumber"
                                               class="form-label required">{{ __('talents/registration.phone') }}</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                               name="phone" id="phoneNumber"
                                               value="{{ old('phone') ?? '' }}"
                                               placeholder="090-1234-5678" maxlength="32" required>
                                        <div class="form-text">{{ __('talents/registration.phone_help') }}</div>
                                        @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="affiliation"
                                               class="form-label required">{{ __("talents/registration.affiliation") }}</label>
                                        <select class="form-select @error('affiliation') is-invalid @enderror"
                                                name="affiliation" id="affiliation" aria-label="affiliation" required>
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Enums\AffiliationEnum::cases() as $case)
                                                <option value="{{ $case->value }}">{{ \App\Enums\AffiliationEnum::toName($case->value) ?? __("talents/index.{$case->name}") ?? __('One') }}</option>
                                            @endforeach
                                        </select>
                                        @error('affiliation')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contractType" class="form-label">{{ __('talents/registration.contract_type') }}</label>
                                        <select class="form-select @error('contract_type') is-invalid @enderror"
                                                name="contract_type" id="contractType" aria-label="contractType">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Enums\ContractClassificationEnum::cases() as $contract)
                                                <option
                                                    value="{{ $contract->value }}">{{ \App\Enums\ContractClassificationEnum::toName($contract) ?? __("talents/index.{$contract->toName($contract)}") }}</option>
                                            @endforeach
                                        </select>
                                        @error('contract_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="gender" class="form-label">{{ __("talents/registration.gender") }}</label>
                                        <select class="form-select @error('gender') is-invalid @enderror" name="gender" id="gender"
                                                aria-label="gender">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Enums\GenderEnum::cases() as $gender)
                                                <option value="{{ $gender->value }}"
                                                    @selected(old('gender') == $gender->value)
                                                >{{ __("talents/registration.{$gender->value}") }}</option>
                                            @endforeach
                                        </select>
                                        @error('gender')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nationality"
                                               class="form-label">{{ __('talents/registration.nationality') }}</label>
                                        <select class="form-select @error('nationality') is-invalid @enderror"
                                                name="nationality" id="nationality" aria-label="nationality">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            <option value="japanese"> {{ __("talents/registration.japanese") }}</option>
                                            <option value="other">{{ __("talents/registration.english") }}</option>
                                        </select>
                                        @error('nationality')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="language"
                                               class="form-label">{{ __('talents/registration.language') }}</label>
                                        <select class="form-select @error('nationality') is-invalid @enderror"
                                                name="language" id="language" aria-label="language">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Enums\LangEnum::cases() as $lang)
                                                <option value="{{ $lang->value }}" @selected(old('language') == $lang->value)> {{ \App\Enums\LangEnum::toName($lang->value) ?? __("talents/registration.japanese") }}</option>
                                            @endforeach
                                        </select>
                                        @error('language')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="talentAddress" class="form-label">{{ __('talents/registration.address') }}</label>
                                    <input class="form-control @error('address') is-invalid @enderror" id="talentAddress" name="address" value="{{ old("address") ?? '' }}" placeholder="{{ __('talents/registration.type_your_address_here') }}">
                                    @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <!-- Basic detail --->
                        {{-- The Nearest Station block was here. Dropped from this
                             form on request: it is registration, and a station is
                             not needed to create a talent. The column and the field
                             still exist on the edit form, the public registration
                             form and the profile page, so the listing card's
                             "Nearest Station" row is still fed from there. --}}
                        <!-- Cover Latter block --->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __('talents/registration.cover_letter_resume') }}</h2>
                                <div class="mb-3">
                                    <label for="targetTextarea1"
                                           class="form-label">{{ __('talents/registration.cover_letter') }}</label>
                                    <textarea class="form-control tinyEditor @error('cover_letter') is-invalid @enderror"
                                              id="targetTextarea1"
                                              name="cover_letter" rows="3"
                                              placeholder="{{ __('talents/registration.cover_letter_placeholder') }}">{!! old("cover_letter") !!}</textarea>
                                    {{-- Was @error('address'): the address field's error message
                                         was being printed under the cover letter. --}}
                                    @error('cover_letter')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="ms-auto text-end mt-2">
                                        <a href="" onclick="openDynamicModal(1)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                    </div>
                                </div>
                                {{-- The CV upload and its "fill from this CV" button used to
                                     live here, at the bottom of the form. They are now the
                                     first thing on the page: reading the document is what
                                     fills most of these fields, so asking for it last meant
                                     everyone typed the form out by hand first. --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="resumePrivacy" class="form-label">{{ __("talents/registration.resume_privacy") }}</label>
                                        <select class="form-select @error('privacy') is-invalid @enderror" name="privacy" id="resumePrivacy">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            <option value="1">{{ __("talents/registration.release") }}</option>
                                            <option value="0">{{ __("talents/registration.private") }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="possibleParticipation"
                                               class="form-label">{{ __('talents/registration.possible_participation') }}</label>
                                        <select class="form-select @error('participation') is-invalid @enderror"
                                                id="possibleParticipation"
                                                name="participation"
                                                aria-label="Default select example">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Enums\ParticipationEnum::cases() as $participation)
                                                <option value="{{ $participation->value }}">{{ __("talents/index.{$participation->value}") }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3" id="joiningDateField" style="display:none;">
                                        <label for="joiningDate" class="form-label">{{ __("talents/registration.joining_date") }}</label>
                                        <input type="date" class="form-control @error('joining_date') is-invalid @enderror"
                                               name="joining_date"
                                               id="joiningDate"
                                               value="{{ old("joining_date") ?? '' }}"
                                               placeholder="{{ __('talents/registration.joining_date') }}" required
                                        />
                                        @error('joining_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3" >
                                        <label class="form-label" for="workExperience">{{ __("talents/registration.work_experience") }}</label>
                                        <input type="number" class="form-control @error('work_experience') is-invalid @enderror"
                                               name="work_experience"
                                               id="workExperience"
                                               min="0"
                                               value="{{ old("work_experience") ?? '' }}"
                                               placeholder="{{ __('talents/registration.work_experience_placeholder') }}"
                                        />
                                        @error('work_experience')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Cover Latter block --->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __("talents/registration.characteristics") }}</h2>
                                <div class="mb-3">
                                    @foreach(\App\Enums\TalentCharEnum::cases() as $case)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input @error('experience_in_2') is-invalid @enderror"
                                                   type="checkbox" id="experience_in_{{$case->value}}" name="characteristics[]" value="{{ $case->value }}"
                                                   @checked(in_array($case->value, (array) old('characteristics', [])))>
                                            <label class="form-check-label"
                                                   for="experience_in_{{$case->value}}">{{ __("talents/registration.experience_in_{$case->value}") }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row px-2">
                        <!---- Salary details block ---->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <div class="mb-3">
                                    <label for="expectedMinSalary" class="form-label required">{{ __('talents/registration.expected_salary') }}</label>
                                    <div class="row align-items-center">
                                        <div class="col-md-6 mb-3">
                                            <label for="expectedMinSalary" class="form-label">{{ __("common/sidebar.min_salary") }}</label>
                                            <input type="number" class="form-control @error('min_monthly_price') is-invalid @enderror"
                                                   name="min_monthly_price"
                                                   id="expectedMinSalary"
                                                   value="{{ old("min_monthly_price") ?? '' }}"
                                                   placeholder="{{ __('talents/registration.salary_min_placeholder') }}" required
                                            />
                                            @error('min_monthly_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="expectedMaxSalary" class="form-label">{{ __("common/sidebar.max_salary") }}</label>
                                            <input type="number" class="form-control @error('max_monthly_price') is-invalid @enderror"
                                                   name="max_monthly_price"
                                                   id="expectedMaxSalary"
                                                   value="{{ old("max_monthly_price") ?? '' }}"
                                                   placeholder="{{ __('talents/registration.salary_max_placeholder') }}" required
                                            />
                                            @error('max_monthly_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row justify-content-around">
                                    <div class="col-md-12">
                                        <label for="multiple-select-field" class="form-label">{{ __("projects/form.locations") }}</label>
                                    </div>
                                    <div class="mb-3 col-md-12">
                                        <select class="form-select form-select-sm" name="locations[]" id="multiple-select-field" data-placeholder="{{ __("talents/registration.choose") }}" multiple>
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Models\Location::orderBy('title')->get() as $location)
                                                <option value="{{ $location->id }}"
                                                    @selected(in_array($location->id, (array) old('locations', [])))>{{ $location->title }}</option>
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
{{--                                                    {{ in_array($location->id, old('locations', [])) ? 'checked' : '' }}--}}
{{--                                                >--}}
{{--                                                <label class="form-check-label"--}}
{{--                                                       for="{{ $location->slug."_".$location->id }}">{{ $location->title ?? '' }}</label>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    @endforeach--}}
                                    <div class="col-md-12">
                                        <span class="form-label d-block">{{ __("projects/form.work_mode") }}</span>
                                    </div>
                                    @foreach(\App\Enums\WorkLocationEnum::cases() as $workLocation)
                                            <div class="mb-3 col-md-4">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="workLocations[]"
                                                           @checked(in_array($workLocation->value, (array) old('workLocations', [])))
                                                           id="{{ "work_location_".$workLocation->value }}"
                                                           value="{{ $workLocation->value }}">
                                                    <label class="form-check-label"
                                                           for="{{ "work_location_".$workLocation->value }}">{{ \App\Enums\WorkLocationEnum::toName($workLocation->value) ?? '' }}</label>
                                                </div>
                                            </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <!---- Salary details block ---->
                        <!-- Qualification & experience block --->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __('talents/registration.qualification') }}</h2>
                                <div class="mb-3">
                                    <label for="targetTextarea2" class="form-label">{{ __('talents/registration.education_details') }}</label>
                                    <textarea class="form-control tinyEditor @error('education') is-invalid @enderror"
                                              id="targetTextarea2"
                                              name="education"
                                              rows="3"
                                              placeholder="{{ __('talents/registration.education_details_placeholder') }}">{!! old("education") ?? '' !!}</textarea>
                                    @error('education')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="ms-auto text-end mt-2">
                                        <a href="" onclick="openDynamicModal(2)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="targetTextarea3"
                                           class="form-label">{{ __('talents/registration.experience_details') }}</label>
                                    <textarea class="form-control tinyEditor @error('experience') is-invalid @enderror"
                                              id="targetTextarea3"
                                              name="experience"
                                              rows="3"
                                              placeholder="{{ __('talents/registration.experience_details_placeholder') }}">{!! old("experience") ?? '' !!}</textarea>
                                    @error('experience')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="ms-auto text-end mt-2">
                                        <a href="#" onclick="openDynamicModal(3)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.consult_sample_input") }}</a>
                                        <a href="#" onclick="openDynamicModal(4)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.system_engineer_sample") }}</a>
                                        <a href="#" onclick="openDynamicModal(5)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.infrastructure_engineer_sample") }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Qualification & experience block --->
                        <!-- Categories block --->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __("talents/registration.experience_fields") }}</h2>
                                @foreach(\App\Models\Category::whereHas('subcategories')->orderBy('title')->get() as $category)
                                    <div class="mb-3">
                                        <h4 class="category-heading">{{ $category->title ?? '' }}</h4>
                                    </div>
                                    <div class="mb-3">
                                        @foreach($category->subcategories as $subcategory)
                                            <div class="form-check form-check-inline">
                                                {{-- Was @selected(old('subcategory[]') == $subcategory->id):
                                                     two bugs in one line. @selected renders
                                                     selected="selected", which does nothing to a
                                                     checkbox, and old('subcategory[]') is not a
                                                     key — the value is under 'subcategory' and is
                                                     an array. So every ticked skill area was lost
                                                     on a failed submit, and since `subcategory` is
                                                     itself required, that is precisely when the
                                                     form comes back. --}}
                                                <input class="form-check-input" type="checkbox"
                                                       name="subcategory[]"
                                                       @checked(in_array($subcategory->id, (array) old('subcategory', [])))
                                                       id="{{ $subcategory->slug."_".$subcategory->id }}"
                                                       value="{{ $subcategory->id }}"
                                                >
                                                <label class="form-check-label"
                                                       for="{{ $subcategory->slug."_".$subcategory->id }}">{{ $subcategory->title ?? '' }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <!-- Categories block --->
                    </div>
                </div>
                <div class="col-md-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 text-center d-grid gap-2 mb-3">
                    <button type="submit" class="g-col-4 btn btn-submit">{{ __("talents/registration.submit") }}</button>
                </div>
            </div>
        </form>
    </div>

@endsection

@section('modals', true)

@section('select2', true)
{{--@section('editor', true)--}}

@push('scripts')
    {{-- Pre-fill the form from the attached CV. --}}
    <script>
        (function () {
            const fileInput = document.getElementById('formFile');
            const button    = document.getElementById('autofillFromResume');
            const spinner   = document.getElementById('autofillSpinner');
            const result    = document.getElementById('autofillResult');

            if (!fileInput || !button) {
                return;
            }

            const LABELS = @json(__('talents/registration.autofill_labels'));

            // Nothing to read until a file is attached, and a file that has
            // been swapped deserves a fresh read rather than the last answer.
            fileInput.addEventListener('change', function () {
                button.disabled = fileInput.files.length === 0;
                result.innerHTML = '';
            });

            /**
             * Write a value into one field.
             *
             * Never overwrites something the user has already typed: they know
             * things the document does not, and losing their input to a machine
             * guess is the fastest way to make a feature like this untrusted.
             *
             * @returns {boolean} whether the field was actually changed
             */
            function looksLikeHtml(value) {
                return typeof value === 'string' && /<[a-z][\s\S]*>/i.test(value);
            }

            /**
             * Markup the server generated, rendered as text a person can read.
             *
             * Parsed, not regex-stripped: the values come from somebody's CV by
             * way of a language model, and "<" in a job title is not a tag.
             * Handing it to the browser's own parser is both correct and the
             * only version that decodes &amp; back to "&".
             *
             * List items and line breaks become newlines rather than being
             * dropped, because the structure is the information — three roles
             * run together on one line is a worse answer than three lines.
             */
            function htmlToText(html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');

                doc.querySelectorAll('br').forEach(function (br) {
                    br.replaceWith(doc.createTextNode('\n'));
                });
                doc.querySelectorAll('li, p, div').forEach(function (block) {
                    block.append(doc.createTextNode('\n'));
                });

                return (doc.body.textContent || '')
                    .split('\n')
                    .map(function (line) { return line.trim(); })
                    .filter(function (line, i, all) {
                        // Collapse the blank runs the block handling leaves behind.
                        return line !== '' || (i > 0 && all[i - 1] !== '');
                    })
                    .join('\n')
                    .trim();
            }

            const FILLED = 'filled';   // we wrote it
            const KEPT   = 'kept';     // the person had already answered
            const ABSENT = 'absent';   // this form has no such field

            function fill(name, value) {
                if (name === 'subcategory') {
                    const boxes = document.querySelectorAll('input[name="subcategory[]"]');
                    if (boxes.length === 0) {
                        return ABSENT;
                    }
                    let changed = false;
                    (value || []).forEach(function (id) {
                        const box = document.querySelector('input[name="subcategory[]"][value="' + id + '"]');
                        if (box && !box.checked) {
                            box.checked = true;
                            changed = true;
                        }
                    });
                    return changed ? FILLED : KEPT;
                }

                const field = document.querySelector('[name="' + name + '"]');
                if (!field) {
                    return ABSENT;
                }

                // The rich-text fields are Summernote *when the editor is
                // loaded*, and their visible content then lives in the editor
                // rather than in the textarea underneath it.
                if (field.classList.contains('tinyEditor') && window.jQuery && jQuery(field).next('.note-editor').length) {
                    if (jQuery(field).summernote('isEmpty')) {
                        jQuery(field).summernote('code', value);
                        return FILLED;
                    }
                    return KEPT;
                }

                if (field.value && field.value.trim() !== '') {
                    return KEPT;
                }

                // No rich-text editor on this page — the editor section is
                // commented out near the top of this view — so these are plain
                // textareas. The server sends HTML because that is what the
                // field stores and what the profile modal renders, but writing
                // markup straight into a plain textarea shows the reader
                // "<ul><li>…" instead of their own work history. Flatten it to
                // readable lines.
                //
                // (Do not name a Blade directive in this comment. Blade
                // compiles the whole file, script blocks and JS comments
                // included: an `@`-directive written here opens a real section
                // mid-push, and the page's entire <head> — stylesheet link and
                // all — ends up emitted inside this <script> tag.)
                field.value = looksLikeHtml(value) ? htmlToText(value) : value;
                // Let select2 and any other listener see the change.
                field.dispatchEvent(new Event('change', { bubbles: true }));
                return FILLED;
            }

            function busy(on) {
                button.disabled = on || fileInput.files.length === 0;
                spinner.classList.toggle('d-none', !on);
            }

            function notice(cssClass, html) {
                result.innerHTML = '<div class="alert ' + cssClass + ' py-2 px-3 mb-0">' + html + '</div>';
            }

            function escapeHtml(text) {
                const d = document.createElement('div');
                d.textContent = text;
                return d.innerHTML;
            }

            // Parsing runs on the queue and takes tens of seconds, so the
            // upload only hands back a token; the result is collected by
            // polling. See App\Jobs\ParseUploadedResume for why it cannot be
            // done inside the request.
            const POLL_EVERY_MS = 2000;
            const GIVE_UP_AFTER_MS = 180000;

            function poll(token, startedAt) {
                return fetch(@json(url('talents/parse-resume')) + '/' + token, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.status === 'done') {
                            return data;
                        }
                        if (data.status === 'failed') {
                            throw new Error(data.message || '');
                        }
                        if (Date.now() - startedAt > GIVE_UP_AFTER_MS) {
                            throw new Error(@json(__('talents/registration.autofill_timeout')));
                        }
                        return new Promise(function (resolve) {
                            setTimeout(function () { resolve(poll(token, startedAt)); }, POLL_EVERY_MS);
                        });
                    });
            }

            button.addEventListener('click', function () {
                if (fileInput.files.length === 0) {
                    return;
                }

                const body = new FormData();
                body.append('resume', fileInput.files[0]);
                // This form's own token — not the first one on the page, which
                // may belong to the header's sign-out form.
                body.append('_token', document.querySelector('#progressForm input[name="_token"]').value);

                busy(true);
                notice('alert-secondary', @json(__('talents/registration.autofill_working')));

                fetch(@json(route('talents.parse-resume')), {
                    method: 'POST',
                    body: body,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(
                                    data.message
                                    || (data.errors && data.errors.resume && data.errors.resume[0])
                                    || @json(__('talents/registration.autofill_failed'))
                                );
                            }
                            return data;
                        });
                    })
                    .then(function (queued) {
                        return poll(queued.token, Date.now());
                    })
                    .then(function (res) {
                        const changed = [];
                        const skipped = [];
                        const absent  = [];

                        const fields = res.fields || {};
                        const bucket = { filled: changed, kept: skipped, absent: absent };

                        Object.keys(fields).forEach(function (name) {
                            bucket[fill(name, fields[name])].push(LABELS[name] || name);
                        });

                        if (changed.length === 0) {
                            notice('alert-secondary', @json(__('talents/registration.autofill_nothing')));
                            return;
                        }

                        // Say exactly what was touched. A form that quietly
                        // changed underneath you is a form you have to re-read
                        // from the top before you dare submit it.
                        let html = '<strong>' + @json(__('talents/registration.autofill_done')) + '</strong>'
                            + '<div class="mt-1">' + escapeHtml(changed.join(', ')) + '</div>';

                        if (skipped.length) {
                            html += '<div class="text-muted mt-1">'
                                + @json(__('talents/registration.autofill_kept')) + ' '
                                + escapeHtml(skipped.join(', ')) + '</div>';
                        }

                        // Distinct from "kept": the CV answered these and this
                        // form has nowhere to put the answer. Reporting that as
                        // "left as you had them" claimed the person had made a
                        // choice they were never offered.
                        if (absent.length) {
                            html += '<div class="text-muted mt-1">'
                                + @json(__('talents/registration.autofill_absent')) + ' '
                                + escapeHtml(absent.join(', ')) + '</div>';
                        }

                        if ((res.unmapped_skills || []).length) {
                            html += '<div class="text-muted mt-1">'
                                + @json(__('talents/registration.autofill_unmapped')) + ' '
                                + escapeHtml(res.unmapped_skills.join(', ')) + '</div>';
                        }

                        notice('alert-success', html);
                    })
                    .catch(function (err) {
                        // Every rejection above carries a message written for a
                        // person — a validation message, the parse failure, or
                        // the give-up notice. Falling back to the generic text
                        // only when something threw without one.
                        notice('alert-warning', escapeHtml(
                            (err && err.message)
                                ? err.message
                                : @json(__('talents/registration.autofill_failed'))
                        ));
                    })
                    .finally(function () {
                        busy(false);
                    });
            });
        })();
    </script>

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
