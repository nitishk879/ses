<x-guest>
    <x-slot name="title">
        {{ __("common/common.member_registration_title") }}
    </x-slot>

    <x-authentication-card>
        <div class="row justify-content-between align-items-center">
            <div class="col-md-12">
                <div class="card my-auto">
                    <div class="card-header mb-5">
                        <img src="{{ asset("images/logo-dark.png") }}" alt="" class="img-fluid" style="max-height: 90px!important;"/>
                        {{--                                {{ __('Login') }}--}}
                    </div>
                    <div class="card-body text-start">
                        <form class="row justify-content-center" method="post" action="{{ route("members.registration") }}" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="col-md-6">
                                <div class="col-md-12 mb-4">
                                    <div class="bg-light p-3">
                                        <div class="col-md-12 mb-3">
                                            <label for="companyName" class="col-form-label required">{{ __("company/register.company_name") }}</label>
                                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                                   id="companyName"
                                                   name="company_name"
                                                   placeholder="{{ __("company/register.company_name") }}"
                                                   value="{{ old('company_name') ?? '' }}" aria-label="Company name" required>
                                            @error('company.company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-md-9 mb-3">
                                                <label for="formFile" class="form-label">{{ __("company/register.company_logo") }}
                                                    ({{ __('company/register.file_acceptance') }})</label>
                                                <input class="form-control @error('company_logo') is-invalid @enderror"
                                                       type="file"
                                                       name="company_logo"
                                                       accept="image/*"
                                                       id="companyLogo">
                                                @error('company_logo')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <img src="{{ $company->company_logo_url ?? "" }}" height="32" width="32" class="rounded-5 mt-4" alt="">
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="companyUrl" class="col-form-label required">{{ __("company/register.company_website") }}</label>
                                            <input type="text" class="form-control @error('company_url') is-invalid @enderror"
                                                   id="companyUrl"
                                                   name="company_url"
                                                   placeholder="{{ __("company/register.company_website") }}"
                                                   value="{{ old("company_url") ?? '' }}"
                                                   aria-label="Company website"
                                                   required
                                            >
                                            @error('company_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="companyEmail" class="col-form-label required">{{ __("company/register.company_email") }}</label>
                                            <input type="text" class="form-control @error('company_email') is-invalid @enderror"
                                                   id="companyEmail"
                                                   name="company_email"
                                                   placeholder="{{ __("company/register.company_email") }}"
                                                   value="{{ old("company_email") ?? '' }}"
                                                   aria-label="Company website"
                                                   required
                                            >
                                            @error('company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3">
                                                <label for="companyIndustry" class="form-label required">{{ __("company/register.company_industry") }}</label>
                                                <select class="form-select @error('industry') is-invalid @enderror"
                                                        name="industry"
                                                        id="companyIndustry" aria-label="company industry" required>
                                                    <option value="">{{ __("talents/registration.choose") }}</option>
                                                    @foreach(\App\Models\Industry::all() as $case)
                                                        <option value="{{ $case->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $case->title ?? '' }}</option>
                                                    @endforeach
                                                </select>
                                                @error('industry')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="companyPhone" class="col-form-label required">{{ __("company/register.company_phone") }}</label>
                                                <input type="text" class="form-control @error('telephone_number') is-invalid @enderror"
                                                       id="companyPhone"
                                                       name="telephone_number"
                                                       placeholder="{{ __("company/register.company_phone") }}"
                                                       value="{{ old("telephone_number") ?? '' }}"
                                                       aria-label="Company phone"
                                                       required
                                                >
                                                @error('telephone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="companyLocation" class="col-form-label required">{{ __("company/register.company_address") }}</label>
                                            <input type="text" class="form-control @error('company_location') is-invalid @enderror"
                                                   id="companyLocation"
                                                   name="company_location"
                                                   placeholder="{{ __("company/register.company_address") }}"
                                                   value="{{ old("company_location") ?? '' }}"
                                                   aria-label="Company website"
                                                   required
                                            >
                                            @error('company_location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="bg-light p-3">
                                        <div class="mb-3">
                                            <label for="educationDetails" class="form-label">{{ __('company/register.company_area_of_expertise') }}</label>
                                            <textarea class="form-control tinyEditor @error('expertise') is-invalid @enderror"
                                                      id="educationDetails"
                                                      name="expertise"
                                                      rows="3"
                                                      placeholder="{{ __('talents/registration.write_bio') }}">{!! old("expertise") ?? '' !!}</textarea>
                                            @error('expertise')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <div class="ms-auto text-end mt-2">
                                                <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="experienceDetails"
                                                   class="form-label">{{ __('company/register.specialization') }}</label>
                                            <textarea class="form-control tinyEditor @error('specialised') is-invalid @enderror"
                                                      id="experienceDetails"
                                                      name="specialised"
                                                      rows="3"
                                                      placeholder="{{ __('talents/registration.write_bio') }}">{!! old("specialised") ?? '' !!}</textarea>
                                            @error('specialised')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <div class="ms-auto text-end mt-2">
                                                <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-12 mb-4">
                                    <div class="bg-light p-3">
                                        <div class="mb-3">
                                            <label for="educationDetails" class="form-label">{{ __('company/register.head_office_address') }}</label>
                                            <textarea class="form-control @error('address') is-invalid @enderror"
                                                      id="educationDetails"
                                                      name="address"
                                                      rows="3"
                                                      placeholder="{{ __('talents/registration.type_your_address_here') }}">{!! old("address") ?? '' !!}</textarea>
                                            @error('address')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="experienceDetails"
                                                   class="form-label">{{ __('company/register.head_office_address_alter') }}</label>
                                            <textarea class="form-control @error('address2') is-invalid @enderror"
                                                      id="experienceDetails"
                                                      name="address2"
                                                      rows="3"
                                                      placeholder="{{ __('talents/registration.type_your_address_here') }}">{!! old("specialised") ?? '' !!}</textarea>
                                            @error('address2')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="bg-light p-3">
                                        <h2>{{ __("company/register.representative_detail") }}</h2>
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3">
                                                <label for="companyRepresentativeFirstName" class="col-form-label required">{{ __("company/register.firstname") }}</label>
                                                <input type="text" class="form-control @error('firstname') is-invalid @enderror"
                                                       id="companyRepresentativeFirstName"
                                                       name="firstname"
                                                       placeholder="{{ __("company/register.firstname") }}"
                                                       value="{{ old('firstname' ?? '') }}"
                                                       aria-label="First Name"
                                                       required
                                                >
                                                @error('firstname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="companyRepresentativeLastName" class="col-form-label required">{{ __("company/register.lastname") }}</label>
                                                <input type="text" class="form-control @error('lastname') is-invalid @enderror"
                                                       id="companyRepresentativeLastName"
                                                       name="lastname"
                                                       placeholder="{{ __("company/register.lastname") }}"
                                                       value="{{ old('lastname' ?? '') }}"
                                                       aria-label="First Name"
                                                       required
                                                >
                                                @error('lastname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="emailAddress" class="form-label required">{{ __('talents/registration.email') }}</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                       name="email"
                                                       id="emailAddress" value="{{ old('email' ?? '') }}" placeholder="johndoe@email.com"
                                                       required
                                                >
                                                @error('email')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="gender" class="form-label required">{{ __("talents/registration.gender") }}</label>
                                                <select class="form-select disabled @error('gender') is-invalid @enderror" name="gender" aria-label="gender">
                                                    <option value="">{{ __("talents/registration.choose") }}</option>
                                                    @foreach(\App\Enums\GenderEnum::cases() as $gender)
                                                        <option value="{{ $gender->value }}"
                                                        >{{ __("talents/registration.{$gender->value}") }}</option>
                                                    @endforeach
                                                </select>
                                                @error('gender')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="gender" class="form-label required">{{ __("talents/registration.language") }}</label>
                                                <select class="form-select disabled @error('language') is-invalid @enderror" name="language" aria-label="language">
                                                    <option value="">{{ __("talents/registration.choose") }}</option>
                                                    @foreach(\App\Enums\LangEnum::cases() as $lang)
                                                        <option value="{{ $lang->value }}"
                                                        >{{ __("talents/registration.{$lang->value}") }}</option>
                                                    @endforeach
                                                </select>
                                                @error('language')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="companyPhone" class="col-form-label required">{{ __("company/register.company_phone") }}</label>
                                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                                       id="companyPhone"
                                                       name="phone_number"
                                                       placeholder="{{ __("company/register.company_phone") }}"
                                                       value="{{ old("phone_number") ?? '' }}"
                                                       aria-label="Company phone"
                                                       required
                                                >
                                                @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="bg-light p-3">
                                        <h2>{{ __("Credit Info") }}</h2>
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3">
                                                <label for="companyEstablish" class="col-form-label required">{{ __("company/register.establishment_date") }}</label>
                                                <input type="text" class="form-control @error('established') is-invalid @enderror"
                                                       id="companyEstablish"
                                                       name="established"
                                                       placeholder="{{ __("company/register.establishment_date") }}"
                                                       value="{{ old("established") ?? '' }}"
                                                       aria-label="Company website"
                                                       required
                                                >
                                                @error('established') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="companyPhone" class="col-form-label required">{{ __("company/register.no_of_employees") }}</label>
                                                <input type="text" class="form-control @error('number_of_employees') is-invalid @enderror"
                                                       id="companyPhone"
                                                       name="number_of_employees"
                                                       placeholder="{{ __("company/register.no_of_employees") }}"
                                                       value="{{ old("number_of_employees") ?? '' }}"
                                                       aria-label="Company phone"
                                                       required
                                                >
                                                @error('number_of_employees') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="companyNetWorth" class="col-form-label required">{{ __("company/register.net_worth") }}</label>
                                            <input type="text" class="form-control @error('capital') is-invalid @enderror"
                                                   id="companyNetWorth"
                                                   name="capital"
                                                   placeholder="{{ __("company/register.net_worth") }}"
                                                   value="{{ old("capital") ?? '' }}"
                                                   aria-label="Company website"
                                                   required
                                            >
                                            @error('capital') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="dispatch_business" value="1" id="dispatchBusiness">
                                            <label class="form-check-label" for="dispatchBusiness">
                                                {{ __("company/register.dispatch_business") }}
                                            </label>
                                            @error('dispatch_business') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <label for="companyNetWorth" class="col-form-label required">{{ __("company/register.dispatch_business_license") }}</label>
                                        <input type="text" class="form-control @error('dispatch_business_license') is-invalid @enderror"
                                               id="companyNetWorth"
                                               name="dispatch_business_license"
                                               placeholder="{{ __("company/register.dispatch_business_license") }}"
                                               value="{{ old("dispatch_business_license") ?? '' }}"
                                               aria-label="Company website"
                                               required
                                        >
                                        @error('dispatch_business_license') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="company_information_disclose" value="1" id="companyInfoDisclose">
                                            <label class="form-check-label" for="companyInfoDisclose">
                                                {{ __("Company Information Disclose") }}
                                            </label>
                                            @error('company_information_disclose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 text-center">
                                @foreach($errors as $error)
                                    <p>{{ $message }}</p>
                                @endforeach
                                <button type="submit" class="btn btn-primary">{{ __("Submit") }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-authentication-card>

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="d-flex justify-content-center align-items-center w-100">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">{{ __("Sample Cover Letter") }}</h1>
                </div>
                <div class="modal-body">
                    <div class="cover-letter ">
                        <h4 class="modalTitle">{{ __("Cover Letter") }}</h4>
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
        {{-- Summer note library --}}
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
        <!-- Add Axios via CDN (optional if not already included) -->
        {{--    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>--}}
        <!-- Modal Popup call -->
        <script>
            function openDynamicModal(id) {
                // Ensure elements exist
                const modalLabel = document.getElementById('staticBackdropLabel');
                const modalBody = document.querySelector('.cover-letter-description');
                const modalElement = document.getElementById('staticBackdrop');

                if (!modalLabel || !modalBody || !modalElement) {
                    console.error('Modal elements are not found in the DOM.');
                    return;
                }
                axios.get('/sample/' + id)
                    .then(response => {
                        const data = response.data;
                        // Set the modal title and content dynamically
                        modalLabel.textContent = data.title;
                        modalBody.innerHTML = data.content;
                    })
                    .catch(error => {
                        console.error('There was an errors fetching the data!', error);
                        alert('Failed to fetch data.');
                    });
            }
        </script>
        <!-- Modal Popup call -->
    @endpush
</x-guest>
