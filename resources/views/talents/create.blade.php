@extends('layouts.app')

@section('title', __("talents/registration.registration"))

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="page-heading">{{ __('talents/registration.registration') }}</h1>
            </div>
        </div>
        <form action="{{ route("talents.store") }}" method="post" class="col-md-12 needs-validation" enctype="multipart/form-data" novalidate>
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
                                                <option value="{{ $case->value }}" {{ $loop->first ? 'selected' : '' }}>{{ __("talents/index.{$case->name}") ?? __('One') }}</option>
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
                                                    value="{{ $contract->value }}" {{ $loop->first ? 'selected' : '' }}>{{ __("talents/index.{$contract->toName($contract)}") }}</option>
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
                                                    {{--                                            @selected($gender->name == $talent?->user?->gender)--}}
                                                >{{ __("talents/registration.{$gender->name}") }}</option>
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
                                </div>
                                <div class="mb-3">
                                    <label for="talentAddress" class="form-label">{{ __('talents/registration.address') }}</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="talentAddress" name="address" rows="1" placeholder="{{ __('talents/registration.type_your_address_here') }}">
                                        {{ old("address") ?? '' }}
                                    </textarea>
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
                                               class="form-label required">{{ __('talents/registration.prefectures') }}</label>
                                        <input type="text" class="form-control @error('nearest_station_prefecture') is-invalid @enderror"
                                               name="nearest_station_prefecture" id="preferredLocation"
                                               placeholder="{{ __('talents/registration.prefectures') }}"
                                               value="{{ old("nearest_station_prefecture") ?? '' }}"
                                               required>
                                        @error('nearest_station_prefecture')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="otherDesiredLocation"
                                               class="form-label required">{{ __('talents/registration.route_name') }}</label>
                                        <input type="text"
                                               class="form-control @error('nearest_station_line') is-invalid @enderror"
                                               name="nearest_station_line" id="otherDesiredLocation"
                                               value="{{ old("nearest_station_line") ?? "" }}"
                                               placeholder="{{ __('talents/registration.route_name') }}" required>
                                        @error('nearest_station_line')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="otherDesiredLocation"
                                               class="form-label required">{{ __('talents/registration.station_name') }}</label>
                                        <input type="text"
                                               class="form-control @error('nearest_station_name') is-invalid @enderror"
                                               name="nearest_station_name" id="otherDesiredLocation"
                                               value="{{ old("nearest_station_name") ?? "" }}"
                                               placeholder="{{ __('talents/registration.station_name') }}" required>
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
                                        <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="formFile" class="form-label">{{ __("talents/registration.upload_resume") }}
                                        ({{ __('talents/registration.file_acceptance') }})</label>
                                    <input class="form-control @error('resume') is-invalid @enderror"
                                           type="file"
                                           name="resume"
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
                                                <option value="{{ $participation->name }}" {{ $loop->first ? 'selected' : '' }}>{{ __("talents/index.{$participation->value}") }}</option>
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
                                </div>
                            </div>
                        </div>
                        <!-- Cover Latter block --->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __("talents/registration.experience_details") }}</h2>
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
                                            <input type="text" class="form-control @error('min_monthly_price') is-invalid @enderror"
                                                   name="min_monthly_price"
                                                   id="expectedMinSalary"
                                                   value="{{ old("min_monthly_price") ?? '' }}"
                                                   placeholder="{{ __('talents/registration.salary_min_placeholder') }}" required
                                            />
                                            @error('min_monthly_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
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
                                        <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
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
                                        <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Qualification & experience block --->
                        <!-- Categories block --->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __("talents/registration.experience_details") }}</h2>
                                @foreach(\App\Models\Category::limit(2)->get() as $category)
                                    <div class="mb-3">
                                        <h4 class="category-heading">{{ $category->title ?? '' }}</h4>
                                    </div>
                                    <div class="mb-3">
                                        @foreach($category->subcategories as $subcategory)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox"
                                                       name="subcategory[]"
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
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="d-flex justify-content-center align-items-center w-100">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">{{ __("Sample Cover Letter") }}</h1>
                </div>
                <div class="modal-body">
                   <div class="cover-letter ">
                       <h4>{{ __("Cover Letter") }}</h4>
                       <div class="cover-letter-description border border-secondary-subtle p-4">

                       </div>
                   </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Understood</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- TinyMCE CDN -->
    <script src="https://cdn.tiny.cloud/1/5rfctkmlgw069tt5rp30sjxqu32fzlox611u5kxk18tm9g0k/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        tinymce.init({
            selector: 'textarea.tinyEditor',  // change this value according to your HTML
            license_key: '5rfctkmlgw069tt5rp30sjxqu32fzlox611u5kxk18tm9g0k',
            height: 220
        });
        // Prevent Bootstrap dialog from blocking focusin
        document.addEventListener('focusin', (e) => {
            if (e.target.closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
                e.stopImmediatePropagation();
            }
        });
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (() => {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
    <script>
        // Get the select element and the additional input field
        const selectBox = document.getElementById('possibleParticipation');
        const additionalInput = document.getElementById('joiningDateField');

        // Listen for changes in the dropdown
        selectBox.addEventListener('change', function() {
            // Check if the selected value is "other"
            if (this.value !== 'IMMEDIATELY') {
                additionalInput.style.display = 'block'; // Show the input field
            } else {
                additionalInput.style.display = 'none';  // Hide the input field
            }
        });
    </script>


    <script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>

@endpush
