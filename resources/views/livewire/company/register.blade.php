<div class="col-md-12 my-3">
    <form class="row justify-content-center" wire:submit.prevent="updateOrCreateCompany">
        <div class="col-md-6">
            <div class="col-md-12 mb-4">
                <div class="bg-light p-3">
                    <div class="col-md-12 mb-3">
                        <label for="companyName" class="col-form-label required">{{ __("company/register.company_name") }}</label>
                        <input type="text" class="form-control @error('state.company_name') is-invalid @enderror"
                               id="companyName"
                               wire:model="state.company_name"
                               placeholder="{{ __("company/register.company_name") }}"
                               value="{{ old('state.company_name') ?? '' }}" aria-label="Company name" required>
                        @error('company.company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-9 mb-3">
                            <label for="formFile" class="form-label">{{ __("company/register.company_logo") }}
                                ({{ __('company/register.file_acceptance') }})</label>
                            <input class="form-control @error('state.company_logo') is-invalid @enderror"
                                   type="file"
                                   name="company_logo"
                                   accept="image/*"
                                   wire:model="state.company_logo"
                                   id="companyLogo">
                            @error('state.company_logo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <img src="{{ $company->company_logo_url ?? "" }}" height="32" width="32" class="rounded-5 mt-4" alt="">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="companyUrl" class="col-form-label required">{{ __("company/register.company_website") }}</label>
                        <input type="text" class="form-control @error('state.company_url') is-invalid @enderror"
                               id="companyUrl"
                               wire:model="state.company_url"
                               placeholder="{{ __("company/register.company_website") }}"
                               value="{{ old("state.company_url") ?? '' }}"
                               aria-label="Company website"
                               required
                        >
                        @error('state.company_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="companyEmail" class="col-form-label required">{{ __("company/register.company_email") }}</label>
                        <input type="text" class="form-control @error('state.company_email') is-invalid @enderror"
                               id="companyEmail"
                               wire:model="state.company_email"
                               placeholder="{{ __("company/register.company_email") }}"
                               value="{{ old("state.company_email") ?? '' }}"
                               aria-label="Company website"
                               required
                        >
                        @error('state.company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="companyIndustry" class="form-label required">{{ __("company/register.company_industry") }}</label>
                            <select class="form-select @error('state.industry') is-invalid @enderror"
                                    wire:model="state.industry"
                                    id="companyIndustry" aria-label="company industry" required>
                                <option value="">{{ __("talents/registration.choose") }}</option>
                                @foreach(\App\Models\Category::all() as $case)
                                    <option value="{{ $case->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $case->title ?? '' }}</option>
                                @endforeach
                            </select>
                            @error('state.industry')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="companyPhone" class="col-form-label required">{{ __("company/register.company_phone") }}</label>
                            <input type="text" class="form-control @error('state.telephone_number') is-invalid @enderror"
                                   id="companyPhone"
                                   wire:model="state.telephone_number"
                                   placeholder="{{ __("company/register.company_phone") }}"
                                   value="{{ old("state.telephone_number") ?? '' }}"
                                   aria-label="Company phone"
                                   required
                            >
                            @error('state.telephone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="companyLocation" class="col-form-label required">{{ __("company/register.company_address") }}</label>
                        <input type="text" class="form-control @error('state.company_location') is-invalid @enderror"
                               id="companyLocation"
                               wire:model="state.company_location"
                               placeholder="{{ __("company/register.company_address") }}"
                               value="{{ old("state.company_location") ?? '' }}"
                               aria-label="Company website"
                               required
                        >
                        @error('state.company_location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="bg-light p-3">
                    <div class="mb-3">
                        <label for="educationDetails" class="form-label">{{ __('company/register.company_area_of_expertise') }}</label>
                        <textarea class="form-control tinyEditor @error('state.expertise') is-invalid @enderror"
                                  id="educationDetails"
                                  wire:model="state.expertise"
                                  rows="3"
                                  placeholder="{{ __('talents/registration.write_bio') }}">{!! old("state.expertise") ?? '' !!}</textarea>
                        @error('state.expertise')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="ms-auto text-end mt-2">
                            <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="experienceDetails"
                               class="form-label">{{ __('company/register.specialization') }}</label>
                        <textarea class="form-control tinyEditor @error('state.specialised') is-invalid @enderror"
                                  id="experienceDetails"
                                  wire:model="state.specialised"
                                  rows="3"
                                  placeholder="{{ __('talents/registration.write_bio') }}">{!! old("state.specialised") ?? '' !!}</textarea>
                        @error('state.specialised')
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
                        <textarea class="form-control @error('state.address') is-invalid @enderror"
                                  id="educationDetails"
                                  wire:model="state.address"
                                  rows="3"
                                  placeholder="{{ __('talents/registration.type_your_address_here') }}">{!! old("state.address") ?? '' !!}</textarea>
                        @error('state.address')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="experienceDetails"
                               class="form-label">{{ __('company/register.head_office_address_alter') }}</label>
                        <textarea class="form-control @error('state.address2') is-invalid @enderror"
                                  id="experienceDetails"
                                  wire:model="state.address2"
                                  rows="3"
                                  placeholder="{{ __('talents/registration.type_your_address_here') }}">{!! old("state.specialised") ?? '' !!}</textarea>
                        @error('state.address2')
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
                            <input type="text" class="form-control disabled @error('state.firstname') is-invalid @enderror"
                                   id="companyRepresentativeFirstName"
                                   wire:model="state.firstname"
                                   placeholder="{{ __("company/register.firstname") }}"
                                   value="{{ Auth::user()->firstname ?? '' }}"
                                   aria-label="First Name"
                                   required
                                   disabled
                            >
                            @error('state.firstname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="companyRepresentativeLastName" class="col-form-label required">{{ __("company/register.lastname") }}</label>
                            <input type="text" class="form-control disabled @error('state.lastname') is-invalid @enderror"
                                   id="companyRepresentativeLastName"
                                   wire:model="state.lastname"
                                   placeholder="{{ __("company/register.lastname") }}"
                                   value="{{ Auth::user()->lastname ?? '' }}"
                                   aria-label="First Name"
                                   required
                                   disabled
                            >
                            @error('state.lastname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="emailAddress" class="form-label required">{{ __('talents/registration.email') }}</label>
                            <input type="email" class="form-control disabled @error('state.email') is-invalid @enderror"
                                   wire:model="state.email"
                                   id="emailAddress" value="{{ Auth::user()->email ?? '' }}" placeholder="johndoe@email.com"
                                   required
                                   disabled
                            >
                            @error('state.email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label required">{{ __("talents/registration.gender") }}</label>
                            <select class="form-select disabled @error('gender') is-invalid @enderror" wire:model="state.gender" name="gender" disabled aria-label="gender">
                                <option value="">{{ __("talents/registration.choose") }}</option>
                                @foreach(\App\Enums\GenderEnum::cases() as $gender)
                                    <option value="{{ $gender->value }}"
                                        @selected($gender->value == $state['gender'])
                                    >{{ __("talents/registration.{$gender->name}") }}</option>
                                @endforeach
                            </select>
                            @error('gender')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="companyPhone" class="col-form-label required">{{ __("company/register.company_phone") }}</label>
                            <input type="text" class="form-control @error('state.phone_number') is-invalid @enderror"
                                   id="companyPhone"
                                   wire:model="state.phone_number"
                                   placeholder="{{ __("company/register.company_phone") }}"
                                   value="{{ old("state.phone_number") ?? '' }}"
                                   aria-label="Company phone"
                                   required
                            >
                            @error('state.phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <input type="text" class="form-control @error('state.established') is-invalid @enderror"
                                   id="companyEstablish"
                                   wire:model="state.established"
                                   placeholder="{{ __("company/register.establishment_date") }}"
                                   value="{{ old("state.established") ?? '' }}"
                                   aria-label="Company website"
                                   required
                            >
                            @error('state.established') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="companyPhone" class="col-form-label required">{{ __("company/register.no_of_employees") }}</label>
                            <input type="text" class="form-control @error('state.number_of_employees') is-invalid @enderror"
                                   id="companyPhone"
                                   wire:model="state.number_of_employees"
                                   placeholder="{{ __("company/register.no_of_employees") }}"
                                   value="{{ old("state.number_of_employees") ?? '' }}"
                                   aria-label="Company phone"
                                   required
                            >
                            @error('state.number_of_employees') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="companyNetWorth" class="col-form-label required">{{ __("company/register.net_worth") }}</label>
                        <input type="text" class="form-control @error('state.capital') is-invalid @enderror"
                               id="companyNetWorth"
                               wire:model="state.capital"
                               placeholder="{{ __("company/register.net_worth") }}"
                               value="{{ old("state.capital") ?? '' }}"
                               aria-label="Company website"
                               required
                        >
                        @error('state.capital') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model="state.dispatch_business" value="1" id="dispatchBusiness">
                        <label class="form-check-label" for="dispatchBusiness">
                            {{ __("company/register.dispatch_business") }}
                        </label>
                        @error('state.dispatch_business') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @if($state['dispatch_business'])
                        <label for="companyNetWorth" class="col-form-label required">{{ __("company/register.dispatch_business_license") }}</label>
                        <input type="text" class="form-control @error('state.dispatch_business_license') is-invalid @enderror"
                               id="companyNetWorth"
                               wire:model="state.dispatch_business_license"
                               placeholder="{{ __("company/register.dispatch_business_license") }}"
                               value="{{ old("state.dispatch_business_license") ?? '' }}"
                               aria-label="Company website"
                               required
                        >
                        @error('state.dispatch_business_license') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model="state.company_information_disclose" value="1" id="companyInfoDisclose">
                        <label class="form-check-label" for="companyInfoDisclose">
                            {{ __("Company Information Disclose") }}
                        </label>
                        @error('state.company_information_disclose') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
