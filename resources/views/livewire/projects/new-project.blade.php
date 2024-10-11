<div class="col-md-12">
    <form class="row justify-content-center" wire:submit.prevent="updateOrCreateProject">
        <div class="col-md-6">
            <div class="col-md-12 mb-4">
                <div class="bg-light p-3">
                    <div class="col-md-12 mb-3">
                        <label for="projectTitle" class="col-form-label required">{{ __("projects/form.project_title") }}</label>
                        <input type="text" class="form-control @error('form.title') is-invalid @enderror"
                               id="projectTitle"
                               wire:model="form.title"
                               placeholder="{{ __("projects/form.enter_your_project_title") }}"
                               value="{{ old('form.title') ?? '' }}" aria-label="Project Title" required>
                        @error('form.title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="projectDescription" class="form-label">{{ __('projects/form.project_description') }}</label>
                        <textarea class="form-control tinyEditor @error('form.project_description') is-invalid @enderror"
                                  id="projectDescription"
                                  wire:ignore="form.project_description"
                                  rows="3"
                                  placeholder="{{ __('talents/registration.write_bio') }}">{!! old("form.project_description") ?? '' !!}</textarea>
                        @error('form.project_description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="ms-auto text-end mt-2">
                            <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="projectRequirement" class="form-label">{{ __('projects/form.project_requirements') }}</label>
                        <textarea class="form-control tinyEditor @error('form.personnel_requirement') is-invalid @enderror"
                                  id="projectRequirement"
                                  wire:model="form.personnel_requirement"
                                  rows="3"
                                  placeholder="{{ __('talents/registration.write_bio') }}">{!! old("form.personnel_requirement") ?? '' !!}</textarea>
                        @error('form.personnel_requirement')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="ms-auto text-end mt-2">
                            <a href="" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __("talents/registration.sample_input") }}</a>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="projectInCharge" class="col-form-label required">{{ __("projects/form.project_in_charge_name") }}</label>
                        <input type="text" class="form-control @error('form.person_in_charge') is-invalid @enderror"
                               id="projectInCharge"
                               wire:model="form.person_in_charge"
                               placeholder="{{ __("projects/form.project_in_charge_name") }}"
                               value="{{ old("form.person_in_charge") ?? '' }}"
                               aria-label="Project In-charge Name"
                               required
                        >
                        @error('form.person_in_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <label for="projectStarted" class="form-label required">{{ __("projects/form.contract_period") }}</label>
                        </div>
                        <div class="col-md-6">
                            <input type="date" class="form-control @error('form.contract_start_date') is-invalid @enderror"
                                   id="projectStarted"
                                   wire:model="form.contract_start_date"
                                   placeholder="{{ __("projects/form.project_start_date") }}"
                                   value="{{ old("form.contract_start_date") ?? '' }}"
                                   aria-label="Company website"
                                   required
                            >
                            @error('form.contract_start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <input type="date" class="form-control @error('form.contract_end_date') is-invalid @enderror"
                                   id="companyPhone"
                                   wire:model="form.contract_end_date"
                                   placeholder="{{ __("projects/form.project_end_date") }}"
                                   value="{{ old("form.contract_end_date") ?? '' }}"
                                   aria-label="Company phone"
                                   required
                            >
                            @error('form.contract_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        @foreach(\App\Enums\ParticipationEnum::cases() as $case)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="form.participation" value="{{$case->value}}" id="category_{{$case->value}}">
                                <label class="form-check-label" for="category_{{$case->value}}">
                                    {{ __("projects/form.{$case->name}") }}
                                </label>
                                @error('form.participation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="projectStatus" class="col-form-label required">{{ __("projects/form.status_of_the_project") }}</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" wire:model="form.project_confirm" id="projectStatus">
                                <label class="form-check-label" for="projectStatus">{{ __("common/sidebar.confirmed") }}</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="projectStatus" class="col-form-label required">{{ __("projects/form.project_published") }}</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" wire:model="form.is_public" id="projectStatus">
                                <label class="form-check-label" for="projectStatus">{{ __("projects/form.public") }}</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="projectStatus" class="col-form-label required">{{ __("projects/form.company_information_disclosure_settings") }}</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" wire:model="form.company_info_disclose" id="projectStatus">
                                <label class="form-check-label" for="projectStatus">{{ __("projects/form.public") }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="bg-light p-3">
                    <div class="mb-3">
                        <label for="expectedMinSalary" class="form-label required">{{ __('talents/registration.expected_salary') }}</label>
                        <div class="row ">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control @error('form.minimum_price') is-invalid @enderror"
                                       wire:model="form.minimum_price"
                                       id="expectedMinSalary"
                                       value="{{ old("form.minimum_price") ?? '' }}"
                                       placeholder="{{ __('projects/form.salary_min_placeholder') }}" required
                                />
                                @error('form.minimum_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control @error('form.maximum_price') is-invalid @enderror"
                                       wire:model="form.maximum_price"
                                       id="expectedMinSalary"
                                       value="{{ old("form.maximum_price") ?? '' }}"
                                       placeholder="{{ __('projects/form.salary_max_placeholder') }}" required
                                />
                                @error('form.maximum_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="preferredLocation"
                               class="form-label required">{{ __('talents/registration.preferred_location') }}</label>
                        <input type="text" class="form-control @error('preferred_location') is-invalid @enderror"
                               name="preferred_location" id="preferredLocation"
                               placeholder="{{ __('talents/registration.location_placeholder') }}"
                               value="{{ old("preferred_location") ?? '' }}"
                               required>
                        @error('preferred_location')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="otherDesiredLocation"
                               class="form-label required">{{ __('talents/registration.other_desired_location') }}</label>
                        <input type="text"
                               class="form-control @error('other_desired_location') is-invalid @enderror"
                               name="other_desired_location" id="otherDesiredLocation"
                               value="{{ old("other_desired_location") ?? "" }}"
                               placeholder="{{ __('talents/registration.location_placeholder') }}" required>
                        @error('other_desired_location')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
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
                                    <input class="form-check-input" type="checkbox" wire:model="form.trade_classification" value="{{$trade->value}}" id="category_{{$trade->value}}">
                                    <label class="form-check-label" for="category_{{$trade->value}}">
                                        {{ __("projects/form.{$trade->name}") }}
                                    </label>
                                    @error('form.trade_classification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-12 mb-3">
                            <h4>{{ __("projects/form.contract_type") }}</h4>
                            @foreach(\App\Enums\ContractClassificationEnum::cases() as $contract)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" wire:model="form.contract_classification" value="{{$contract->value}}" id="category_{{$contract->value}}">
                                    <label class="form-check-label" for="category_{{$contract->value}}">
                                        {{ __("projects/form.{$contract->name}") }}
                                    </label>
                                    @error('form.contract_classification') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h4>{{ __("projects/form.eligibility") }}</h4>
                            @foreach(\App\Enums\AffiliationEnum::cases() as $eligible)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" wire:model="form.eligibility" value="{{$eligible->value}}" id="category_{{$eligible->value}}">
                                    <label class="form-check-label" for="category_{{$eligible->value}}">
                                        {{ __("projects/form.{$eligible->name}") }}
                                    </label>
                                    @error('form.eligibility') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                        <input class="form-check-input" type="checkbox" wire:model="form.category_id" value="{{$subCategory->id}}" id="category_{{$subCategory->id}}">
                                        <label class="form-check-label" for="category_{{$subCategory->id}}">
                                            {{ $subCategory->title ?? __("company/register.dispatch_business") }}
                                        </label>
                                        @error('form.dispatch_business') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                    <input class="form-check-input" type="checkbox" wire:model="project_features" value="{{$feature->id}}" id="feature_{{$feature->id}}">
                                    <label class="form-check-label" for="category_{{$feature->id}}">
                                        {{ __("projects/form.{$feature->slug}") }}
                                    </label>
                                    @error('form.project_features') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        @endforeach
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

    @prepend('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            tinymce.init({
                selector: '#projectDescription',
                setup: function (editor) {
                    editor.on('change', function () {
                        Livewire.emit('updateProjectDescription', editor.getContent());
                    });
                }
            });
        });
    </script>
    @endprepend
</div>
