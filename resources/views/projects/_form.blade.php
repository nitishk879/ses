{{--
    The project form's fields, shared by create and edit.

    One file, because there are two screens that must ask exactly the same
    questions. `projects/edit.blade.php` had no form at all — the kebab's "Edit"
    action opened a read-only copy of the show page — and the obvious fix, a
    second 470-line form, would have drifted from this one within a release.
    Every wording fix the client asked for would then have had to be made twice,
    and the second copy would be the one nobody remembered.

    Callers provide the <form> element, its action, its method and @csrf; this
    file is only the inside. It takes:

      $project    the project being edited, or null when creating
      $categories selectable categories (Category::scopeSelectable)
      $features   project features

    Every value reads `old($field, <the project's value>)`, so a rejected
    submission comes back as the recruiter left it, and an edit opens on what is
    actually stored.
--}}

@php
    /**
     * Ids already attached, as plain int lists.
     *
     * Computed once here rather than inside each loop: the location block alone
     * runs 46 times, and `$project->locations` inside it would be 46 reads of
     * the same relation.
     */
    $chosenLocations  = collect(old('locations',      $project?->locations->pluck('id')->all() ?? []))->map(fn ($v) => (int) $v)->all();
    $chosenWorkModes  = collect(old('workLocations',  $project?->work_location_prefer ?? []))->map(fn ($v) => (int) $v)->all();
    $chosenEligible   = collect(old('eligibility',    $project?->affiliation ?? []))->map(fn ($v) => (int) $v)->all();
    $chosenCategories = collect(old('category_id',    $project?->subCategories->pluck('id')->all() ?? []))->map(fn ($v) => (int) $v)->all();
    $chosenFeatures   = collect(old('project_features', $project?->features->pluck('id')->all() ?? []))->map(fn ($v) => (int) $v)->all();

    // `languages` is posted as a single radio but stored as a JSON list, and
    // the model's accessor turns it into display names — so the raw column is
    // what a radio can be compared against.
    $storedLanguages  = json_decode($project?->getRawOriginal('languages') ?? '[]', true);
    $storedLanguages  = is_array($storedLanguages) ? array_map('intval', $storedLanguages) : [(int) $storedLanguages];
    // "Bilingual" is saved as [1, 2], so both together read back as the
    // bilingual radio. Reading only the first entry showed "English", and
    // saving the edit unchanged then dropped Japanese from the project.
    $chosenLanguage   = old('languages', match (true) {
        in_array(1, $storedLanguages, true) && in_array(2, $storedLanguages, true) => 3,
        default => $storedLanguages[0] ?? null,
    });

    // Required experience is stored in months; the form asks in years + months.
    $experience       = $project?->experienceParts() ?? ['years' => null, 'months' => null];

    $chosenTrade      = old('trade_classification',    $project?->trade_classification?->value);
    $chosenContract   = old('contract_classification', $project?->contract_classification?->value);
    $chosenFlow       = old('commercial_flow',         $project?->commercial_flow?->value);
    $chosenInterviews = old('number_of_interviewers',  $project?->number_of_interviewers?->value);

    // Date inputs want Y-m-d; the columns are Carbon (or a plain string, for
    // `deadline`, which is a string column with a datetime cast).
    $asDate = fn ($value) => $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value;
@endphp

<div class="col-md-6">
    <div class="col-md-12 mb-4">
        <div class="bg-light p-3">
            <div class="col-md-12 mb-3">
                <label for="projectTitle" class="col-form-label required">{{ __("projects/form.project_title") }}</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror"
                       id="projectTitle"
                       name="title"
                       placeholder="{{ __("projects/form.enter_your_project_title") }}"
                       value="{{ old('title', $project?->title) }}" aria-label="Project Title" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-12 mb-3">
                <label for="projectDescription" class="form-label">{{ __('projects/form.project_description') }}</label>
                <textarea class="form-control tinyEditor @error('project_description') is-invalid @enderror"
                          id="projectDescription"
                          name="project_description"
                          rows="3"
                          placeholder="{{ __('projects/form.enter_job_duties') }}">{!! old('project_description', $project?->project_description) !!}</textarea>
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
                          placeholder="{{ __('projects/form.enter_required_skills') }}">{!! old('personnel_requirement', $project?->personnel_requirement) !!}</textarea>
                @error('personnel_requirement')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <div class="ms-auto text-end mt-2">
                    <a href="" onclick="openDynamicModal(2)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-id="" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                </div>
            </div>

            {{-- Required experience. Optional: both boxes empty means none is
                 required, and the matching screen then shows no experience
                 requirement at all. Stored as months. --}}
            <div class="col-md-12 mb-3">
                <label for="experienceYears" class="form-label">{{ __('projects/form.required_experience') }}</label>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="input-group">
                            <input type="number" class="form-control @error('experience_years') is-invalid @enderror"
                                   id="experienceYears"
                                   name="experience_years"
                                   min="0" max="40" step="1"
                                   value="{{ old('experience_years', $experience['years']) }}"
                                   aria-label="{{ __('projects/form.experience_years') }}">
                            <span class="input-group-text">{{ __('projects/form.experience_years') }}</span>
                        </div>
                        @error('experience_years')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <select class="form-select @error('experience_months') is-invalid @enderror"
                                    id="experienceMonths"
                                    name="experience_months"
                                    aria-label="{{ __('projects/form.experience_months') }}">
                                <option value="">0</option>
                                @foreach(range(1, 11) as $m)
                                    <option value="{{ $m }}" @selected((string) old('experience_months', $experience['months']) === (string) $m)>{{ $m }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-text">{{ __('projects/form.experience_months') }}</span>
                        </div>
                        @error('experience_months')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-text">{{ __('projects/form.required_experience_help') }}</div>
            </div>

            <div class="form-group mb-3">
                <label for="projectInCharge" class="col-form-label required">{{ __("projects/form.project_in_charge_name") }}</label>
                <input type="text" class="form-control @error('person_in_charge') is-invalid @enderror"
                       id="projectInCharge"
                       name="person_in_charge"
                       placeholder="{{ __("projects/form.project_in_charge_name") }}"
                       value="{{ old('person_in_charge', $project?->person_in_charge) }}"
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
                           value="{{ $asDate(old('contract_start_date', $project?->contract_start_date)) }}"
                           aria-label="{{ __('projects/form.project_start_date') }}"
                           required
                    >
                    @error('contract_start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    {{-- `id="companyPhone"` on a contract-end date was a copy-paste
                         left over from the company form. --}}
                    <input type="date" class="form-control @error('contract_end_date') is-invalid @enderror"
                           id="projectEnded"
                           name="contract_end_date"
                           value="{{ $asDate(old('contract_end_date', $project?->contract_end_date)) }}"
                           aria-label="{{ __('projects/form.project_end_date') }}"
                           required
                    >
                    @error('contract_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- The switches.

                 Each was written as `value="{{ old("accept" ?? 1) }}"`. The `??`
                 sits inside the call, so it read `old("accept")` — null until a
                 submission is redisplayed — and the control rendered
                 `value=""`. Ticking it posted an empty string, which the
                 controller turned into false: every one of these seven saved the
                 opposite of what the recruiter chose.

                 Now `value="1"` with a hidden `0` in front. The hidden field is
                 what makes an *unticked* box post something at all, which is
                 what an edit form needs — without it, clearing a switch would
                 leave the field absent from the request and the old value would
                 stand.

                 The ids were duplicated too (three `projectStatus`, two each of
                 `projectSkill` and `projectSustainability`), so several labels
                 pointed at a different switch than the one beside them. --}}
            <div class="row mb-3">
                @foreach([
                    ['accept',                    'projects/form.status_of_the_project',                'common/sidebar.confirmed', 'col-md-6'],
                    ['is_public',                 'projects/form.project_published',                    'projects/form.public',     'col-md-6'],
                    ['company_info_disclose',     'projects/form.company_information_disclosure_settings', 'projects/form.public',  'col-md-12'],
                    ['skill_matching',            'projects/form.skill_matching',                       null,                       'col-md-6'],
                    ['project_finalized',         'projects/form.project_finalise',                     null,                       'col-md-6'],
                    ['possible_to_continue',      'projects/form.possible_to_continue',                 null,                       'col-md-6'],
                    ['remote_operation_possible', 'projects/form.remote_operation_possible',            null,                       'col-md-6'],
                ] as [$name, $labelKey, $switchLabelKey, $width])
                    <div class="{{ $width }} mb-3">
                        <label for="switch_{{ $name }}" class="col-form-label required">{{ __($labelKey) }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="{{ $name }}" value="1" id="switch_{{ $name }}"
                                   aria-label="{{ __($labelKey) }}"
                                   @checked(old($name, $project?->{$name}))>
                            @if($switchLabelKey)
                                <label class="form-check-label" for="switch_{{ $name }}">{{ __($switchLabelKey) }}</label>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-12 mb-4">
        <div class="bg-light p-3">
            <div class="mb-3">
                <label for="expectedMinSalary" class="form-label required">{{ __('talents/registration.expected_salary') }}</label>
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="expectedMinSalary">{{ __("common/sidebar.min_salary") }}</label>
                        <input type="text" class="form-control @error('minimum_price') is-invalid @enderror"
                               name="minimum_price"
                               id="expectedMinSalary"
                               value="{{ old('minimum_price', $project?->minimum_price) }}"
                               placeholder="{{ __('projects/form.salary_min_placeholder') }}" required
                        />
                        @error('minimum_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="expectedMaxSalary">{{ __("common/sidebar.max_salary") }}</label>
                        <input type="text" class="form-control @error('maximum_price') is-invalid @enderror"
                               name="maximum_price"
                               id="expectedMaxSalary"
                               value="{{ old('maximum_price', $project?->maximum_price) }}"
                               placeholder="{{ __('projects/form.salary_max_placeholder') }}" required
                        />
                        @error('maximum_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                @foreach(\App\Models\Location::orderBy('title')->get() as $location)
                    <div class="mb-3 col-md-4">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox"
                                   name="locations[]"
                                   id="location_{{ $location->id }}"
                                   value="{{ $location->id }}"
                                   @checked(in_array($location->id, $chosenLocations, true))>
                            <label class="form-check-label"
                                   for="location_{{ $location->id }}">{{ $location->display_title }}</label>
                        </div>
                    </div>
                @endforeach
                @error('locations')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="row mb-3">
                @foreach(\App\Enums\WorkLocationEnum::cases() as $workLocation)
                    <div class="mb-3 col-md-6">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox"
                                   name="workLocations[]"
                                   id="work_mode_{{ $workLocation->value }}"
                                   value="{{ $workLocation->value }}"
                                   @checked(in_array($workLocation->value, $chosenWorkModes, true))>
                            <label class="form-check-label"
                                   for="work_mode_{{ $workLocation->value }}">{{ __("common/sidebar.{$workLocation->name}") }}</label>
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
                    {{-- Ids are prefixed per group. They were all `category_{value}`,
                         which collided across the trade, contract, eligibility and
                         sub-category blocks — `category_1` existed four times, so a
                         label could toggle a control from a different question. --}}
                    @foreach(\App\Enums\TradeClassification::cases() as $trade)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trade_classification"
                                   value="{{ $trade->value }}" id="trade_{{ $trade->value }}"
                                   @checked((string) $chosenTrade === (string) $trade->value)>
                            <label class="form-check-label" for="trade_{{ $trade->value }}">
                                {{ __("projects/form.{$trade->name}") }}
                            </label>
                        </div>
                    @endforeach
                    @error('trade_classification')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12 mb-3">
                    <h4>{{ __("projects/form.contract_type") }}</h4>
                    @foreach(\App\Enums\ContractClassificationEnum::cases() as $contract)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="contract_classification"
                                   value="{{ $contract->value }}" id="contract_{{ $contract->value }}"
                                   @checked((string) $chosenContract === (string) $contract->value)>
                            <label class="form-check-label" for="contract_{{ $contract->value }}">
                                {{ __("projects/form.{$contract->name}") }}
                            </label>
                        </div>
                    @endforeach
                    @error('contract_classification')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <h4>{{ __("projects/form.eligibility") }}</h4>
                    @foreach(\App\Enums\AffiliationEnum::cases() as $eligible)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="eligibility[]"
                                   value="{{ $eligible->value }}" id="eligibility_{{ $eligible->value }}"
                                   @checked(in_array($eligible->value, $chosenEligible, true))>
                            <label class="form-check-label" for="eligibility_{{ $eligible->value }}">
                                {{ __("projects/form.{$eligible->name}") }}
                            </label>
                        </div>
                    @endforeach
                    @error('eligibility')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 mb-4">
        <div class="bg-light p-3">
            <h2>{{ __("projects/form.categories") }}</h2>
            <div class="mb-3">
                @foreach($categories as $category)
                    <h4>{{ $category->display_title }}</h4>
                    <div class="mb-3">
                        @foreach($category->subcategories as $subCategory)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="category_id[]"
                                       value="{{ $subCategory->id }}" id="subcategory_{{ $subCategory->id }}"
                                       @checked(in_array($subCategory->id, $chosenCategories, true))>
                                <label class="form-check-label" for="subcategory_{{ $subCategory->id }}">
                                    {{ $subCategory->display_title }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                            {{-- The label pointed at `category_{id}`, not at this
                                 checkbox, so clicking a feature toggled whichever
                                 sub-category happened to share the number. --}}
                            <input class="form-check-input" type="checkbox" name="project_features[]"
                                   value="{{ $feature->id }}" id="feature_{{ $feature->id }}"
                                   @checked(in_array($feature->id, $chosenFeatures, true))>
                            <label class="form-check-label" for="feature_{{ $feature->id }}">
                                {{ __("projects/form.{$feature->slug}") }}
                            </label>
                        </div>
                    </div>
                @endforeach
                @error('project_features')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-4">
        <div class="bg-light p-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3">
                    {{-- Optional: the column is nullable and the request rules have
                         always been `nullable`. Only the markup claimed otherwise. --}}
                    <label for="projectDeadline" class="form-label">{{ __('projects/form.deadline') }}</label>
                    <input type="date" class="form-control @error('deadline') is-invalid @enderror"
                           name="deadline"
                           id="projectDeadline"
                           value="{{ $asDate(old('deadline', $project?->deadline)) }}"
                    />
                    @error('deadline')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="expectedApplications" class="form-label">{{ __('projects/form.no_of_application') }}</label>
                    <input type="number" class="form-control @error('number_of_application') is-invalid @enderror"
                           name="number_of_application"
                           id="expectedApplications"
                           min="0"
                           value="{{ old('number_of_application', $project?->number_of_application) }}"
                           placeholder="{{ __('projects/form.no_of_application_placeholder') }}"
                    />
                    @error('number_of_application')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <h5>{{ __("projects/form.number_of_interview") }}</h5>
                {{-- Driven by the enum, not by a 1..4 counter, so the form can only
                     offer answers the server accepts. "Not specified" is a real
                     option and the default: radios are one-way, and without it a
                     recruiter who ticked one by accident could not clear an
                     optional field. An empty value becomes null via
                     ConvertEmptyStringsToNull, which is what the column wants. --}}
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                           name="number_of_interviewers" value=""
                           id="interview_none"
                           @checked(blank($chosenInterviews))>
                    <label class="form-check-label text-muted" for="interview_none">
                        {{ __('projects/form.interview_none') }}
                    </label>
                </div>
                @foreach(\App\Enums\InterviewEnum::cases() as $interview)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio"
                               name="number_of_interviewers"
                               value="{{ $interview->value }}"
                               id="interview_{{ $interview->value }}"
                               @checked((string) $chosenInterviews === (string) $interview->value)>
                        <label class="form-check-label" for="interview_{{ $interview->value }}">
                            {{ __("projects/form.interview_{$interview->value}") }}
                        </label>
                    </div>
                @endforeach
                @error('number_of_interviewers')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="commercialFlow" class="form-label required">{{ __("projects/form.commercial_flow") }}</label>
                <select class="form-select @error('commercial_flow') is-invalid @enderror"
                        name="commercial_flow" id="commercialFlow" aria-label="{{ __('projects/form.commercial_flow') }}" required>
                    {{-- Nothing is pre-selected on create. The first case used to
                         carry `selected`, which defeated the `required`: the browser
                         saw a value and never asked, so a recruiter who had not
                         looked at this menu still submitted an answer to it. --}}
                    <option value="">{{ __("talents/registration.choose") }}</option>
                    @foreach(\App\Enums\CommercialFlow::cases() as $case)
                        <option value="{{ $case->value }}"
                                @selected((string) $chosenFlow === (string) $case->value)>
                            {{ __("projects/form.{$case->name}") }}
                        </option>
                    @endforeach
                </select>
                @error('commercial_flow')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-12 mb-3">
                <h5>{{ __("talents/registration.language") }}</h5>
                @foreach(\App\Enums\LangEnum::cases() as $lang)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="languages"
                               value="{{ $lang->value }}" id="language_{{ $lang->value }}"
                               @checked((string) $chosenLanguage === (string) $lang->value)>
                        <label class="form-check-label" for="language_{{ $lang->value }}">
                            {{ __("common/sidebar.{$lang->name}") }}
                        </label>
                    </div>
                @endforeach
                @error('languages')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="col-md-12 mb-3 text-center">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? __("common/common.submit") }}</button>
</div>
