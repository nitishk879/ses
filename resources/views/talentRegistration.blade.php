<x-guest>
    <x-slot name="title">
        Login
    </x-slot>

    <x-authentication-card>
        <div class="row justify-content-center align-items-center">
            <div class="col-md-12">
                <div class="card my-auto">
                    <div class="card-header mb-5">
                        <img src="{{ asset("images/logo-dark.png") }}" alt="" class="img-fluid" style="max-height: 90px!important;"/>
                        {{--                                {{ __('Login') }}--}}
                    </div>
                    <div class="card-body">
                        <form action="{{ route("talent.registration") }}" method="post" class="col-md-12 needs-validation" enctype="multipart/form-data" novalidate>
                            @csrf
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
                                                        <label for="emailAddress"
                                                               class="form-label required">{{ __('talents/registration.date_of_birth') }}</label>
                                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth"
                                                               id="emailAddress" value="{{ old('date_of_birth') ?? '' }}" placeholder="10/02/2000"
                                                               required>
                                                        @error('date_of_birth')
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
                                                                <option value="{{ $case->value }}" {{ $loop->first ? 'selected' : '' }}>{{ \App\Enums\AffiliationEnum::toName($case->value) ?? __("talents/index.{$case->name}") ?? __('One') }}</option>
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
                                                                    value="{{ $contract->value }}" {{ $loop->first ? 'selected' : '' }}>{{ \App\Enums\ContractClassificationEnum::toName($contract) ?? __("talents/index.{$contract->toName($contract)}") }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('contract_type')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="gender" class="form-label">{{ __("talents/registration.gender") }}</label>
                                                        <select class="form-select @error('gender') is-invalid @enderror" name="gender"
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
                                                            <option value="japanese" selected> {{ __("talents/registration.japanese") }}</option>
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
                                                                <option value="{{ $lang->value }}" @selected(old('language') == $lang->value) selected> {{ \App\Enums\LangEnum::toName($lang->value) ?? __("talents/registration.japanese") }}</option>
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
                                        <!-- Station detail Block --->
                                        <div class="col-md-12 bg-light mb-4">
                                            <div class="bg-light p-3">
                                                <h2>{{ __("talents/registration.nearest_station") }}</h2>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="preferredLocation"
                                                               class="form-label">{{ __('talents/registration.prefectures') }}</label>
                                                        <input type="text" class="form-control @error('nearest_station_prefecture') is-invalid @enderror"
                                                               name="nearest_station_prefecture" id="preferredLocation"
                                                               placeholder="{{ __('talents/registration.prefectures') }}"
                                                               value="{{ old("nearest_station_prefecture") ?? '' }}"
                                                        >
                                                        @error('nearest_station_prefecture')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="otherDesiredLocation"
                                                               class="form-label">{{ __('talents/registration.route_name') }}</label>
                                                        <input type="text"
                                                               class="form-control @error('nearest_station_line') is-invalid @enderror"
                                                               name="nearest_station_line" id="otherDesiredLocation"
                                                               value="{{ old("nearest_station_line") ?? "" }}"
                                                               placeholder="{{ __('talents/registration.route_name') }}">
                                                        @error('nearest_station_line')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label for="otherDesiredLocation"
                                                               class="form-label">{{ __('talents/registration.station_name') }}</label>
                                                        <input type="text"
                                                               class="form-control @error('nearest_station_name') is-invalid @enderror"
                                                               name="nearest_station_name" id="otherDesiredLocation"
                                                               value="{{ old("nearest_station_name") ?? "" }}"
                                                               placeholder="{{ __('talents/registration.station_name') }}">
                                                        @error('nearest_station_name')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Station detail Block --->
                                        <!-- Cover Latter block --->
                                        <div class="col-md-12 bg-light mb-4">
                                            <div class="bg-light p-3">
                                                <h2>{{ __('talents/registration.cover_letter_resume') }}</h2>
                                                <div class="mb-3">
                                                    <label for="coverLetter"
                                                           class="form-label">{{ __('talents/registration.cover_letter') }}</label>
                                                    <textarea class="form-control tinyEditor @error('cover_letter') is-invalid @enderror" id="coverLetter"
                                                              name="cover_letter" rows="3"
                                                              placeholder="{{ __('talents/registration.write_bio') }}">{!! old("cover_letter") !!}</textarea>
                                                    @error('address')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                    <div class="ms-auto text-end mt-2">
                                                        <a href="" onclick="openDynamicModal(1)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="formFile" class="form-label">{{ __("talents/registration.upload_resume") }}
                                                        ({{ __('talents/registration.file_acceptance') }})</label>
                                                    <input class="form-control @error('resume') is-invalid @enderror"
                                                           type="file"
                                                           name="resume"
                                                           value="{{ old("resume") ?? '' }}"
                                                           id="formFile">
                                                    @error('resume')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="exampleFormControlInput1" class="form-label">{{ __("talents/registration.resume_privacy") }}</label>
                                                        <select class="form-select @error('privacy') is-invalid @enderror" name="privacy" aria-label="Default select example">
                                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                                            <option value="1">{{ __("talents/registration.release") }}</option>
                                                            <option value="0" selected>{{ __("talents/registration.private") }}</option>
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
                                                                <option value="{{ $participation->value }}" {{ $loop->first ? 'selected' : '' }}>{{ __("talents/index.{$participation->value}") }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3" id="joiningDateField" style="display:none;">
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
                                                                   type="checkbox" id="experience_in_{{$case->value}}" name="characteristics[]" value="{{ $case->value }}">
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
                                                            <label for="minSalary" class="form-label">{{ __("common/sidebar.min_salary") }}</label>
                                                            <input type="text" class="form-control @error('min_monthly_price') is-invalid @enderror"
                                                                   name="min_monthly_price"
                                                                   id="expectedMinSalary"
                                                                   value="{{ old("min_monthly_price") ?? '' }}"
                                                                   placeholder="{{ __('talents/registration.salary_min_placeholder') }}" required
                                                            />
                                                            @error('min_monthly_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="maxSalary" class="form-label">{{ __("common/sidebar.max_salary") }}</label>
                                                            <input type="text" class="form-control @error('max_monthly_price') is-invalid @enderror"
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
                                                        <label for="locations" class="form-label">{{ __("projects/form.locations") }}</label>
                                                    </div>
                                                    @foreach(\App\Models\Location::orderBy('title')->get() as $location)
                                                        <div class="mb-3 col-md-4">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="checkbox"
                                                                       name="locations[]"
                                                                       id="{{ $location->slug."_".$location->id }}"
                                                                       value="{{ $location->id }}"
                                                                    {{ in_array($location->id, old('locations', [])) ? 'checked' : '' }}
                                                                >
                                                                <label class="form-check-label"
                                                                       for="{{ $location->slug."_".$location->id }}">{{ $location->title ?? '' }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <div class="col-md-12">
                                                        <label for="locations" class="form-label">{{ __("projects/form.work_mode") }}</label>
                                                    </div>
                                                    @foreach(\App\Enums\WorkLocationEnum::cases() as $workLocation)
                                                        <div class="mb-3 col-md-4">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="checkbox"
                                                                       name="workLocations[]"
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
                                                    <label for="educationDetails" class="form-label">{{ __('talents/registration.education_details') }}</label>
                                                    <textarea class="form-control tinyEditor @error('education') is-invalid @enderror"
                                                              id="educationDetails"
                                                              name="education"
                                                              rows="3"
                                                              placeholder="{{ __('talents/registration.write_bio') }}">{!! old("education") ?? '' !!}</textarea>
                                                    @error('education')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                    <div class="ms-auto text-end mt-2">
                                                        <a href="" onclick="openDynamicModal(2)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="experienceDetails"
                                                           class="form-label">{{ __('talents/registration.experience_details') }}</label>
                                                    <textarea class="form-control tinyEditor @error('experience') is-invalid @enderror"
                                                              id="experienceDetails"
                                                              name="experience"
                                                              rows="3"
                                                              placeholder="{{ __('talents/registration.write_bio') }}">{!! old("experience") ?? '' !!}</textarea>
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
                                                                <input class="form-check-input" type="checkbox"
                                                                       name="subcategory[]"
                                                                       @selected(old('subcategory[]') == $subcategory->id)
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
                </div>
            </div>
        </div>
    </x-authentication-card>
</x-guest>