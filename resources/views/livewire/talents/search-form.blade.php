<div>
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-light text-muted">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-muted text-decoration-none">
            <svg class="bi pe-none me-2" width="40" height="32"><use xlink:href="#bootstrap"></use></svg>
            <span class="fs-4 mb-3">{{ __("talents/index.search") }}</span>
        </a>
        <div class="input-group mb-3">
            <span class="input-group-text" id="search-keyword"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input wire:model.live="search" type="text" class="form-control search-input" placeholder="{{ __("common/sidebar.search_with_keyword") }}" aria-label="search-input" aria-describedby="search-keyword">
        </div>
        <div class="participation">
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <!-- Categories block -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            {{ __("common/sidebar.categories") }}
                        </button>
                    </h2>
                    <div id="flush-collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @foreach($categories as $category)
                                <div class="form-check">
                                    <input class="form-check-input" wire:model.live="category" type="checkbox" value="{{ $category->id }}" id="{{ $category->id }}">
                                    <label class="form-check-label" for="{{ $category->id }}">
                                        {{ __("common/category.{$category->slug}") }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Categories block -->

                <!---- Possible Participation -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsePossibleParticipation" aria-expanded="false" aria-controls="flush-collapsePossibleParticipation">
                            {{ __("common/sidebar.possible_participation") }}
                        </button>
                    </h2>
                    <div id="flush-collapsePossibleParticipation" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="input-group mb-3">
                                <input type="date" class="form-control" wire:model.live="startingDate" placeholder="Starting Date" aria-label="budget" aria-describedby="search-starting">
                            </div>
                            <div class="input-group mb-3">
                                <input type="date" class="form-control" wire:model.live="endDate" placeholder="Ending Date" aria-label="budget" aria-describedby="search-ending">
                            </div>
                            <div class="my-3">
                                @foreach(\App\Enums\ParticipationEnum::cases() as $case)
                                    <div class="form-check">
                                        <input class="form-check-input" wire:model.live="availability" type="checkbox" value="{{ $case->value }}" id="available{{$case->value}}">
                                        <label class="form-check-label" for="available{{$case->value}}">
                                            {{ __("common/sidebar.{$case->value}") }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!---- Possible Participation -->

                <!--- Age block -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseAge" aria-expanded="false" aria-controls="flush-collapseAge">
                            {{ __("talents/index.age") }}
                        </button>
                    </h2>
                    <div id="flush-collapseAge" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @foreach($ages as $age_range)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" wire:model.live="age" type="checkbox" id="inline{{$age_range}}" value="{{$age_range}}">
                                    <label class="form-check-label" for="inline{{$age_range}}">{{ $age_range }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!--- Age block -->

                <!---- Gender Block --->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseGender" aria-expanded="false" aria-controls="flush-collapseGender">
                            {{ __("talents/index.gender") }}
                        </button>
                    </h2>
                    <div id="flush-collapseGender" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <select class="form-select @error('gender') is-invalid @enderror" wire:model.live="gender" aria-label="gender">
                                <option value="">{{ __("talents/registration.choose") }}</option>
                                @foreach(\App\Enums\GenderEnum::cases() as $gender)
                                    <option value="{{ $gender->value }}"
                                        {{--                                            @selected($gender->name == $talent?->user?->gender)--}}
                                    >{{ __("talents/registration.{$gender->value}") }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <!---- Gender Block --->

                <!---- Nationality section Block --->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseNationality" aria-expanded="false" aria-controls="flush-collapseNationality">
                            {{ __("talents/index.nationality") }}
                        </button>
                    </h2>
                    <div id="flush-collapseNationality" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @foreach($nationalities as $nation)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" wire:model.live="nationality" type="checkbox" id="inline{{$nation}}" value="{{ $nation }}">
                                    <label class="form-check-label" for="inline{{$nation}}">{{ $nation =='other' ? __("talents/registration.other") : __("common/header.japanese") }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!---- Nationality section Block --->

                <!---- Work Location -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseWorkLocation" aria-expanded="false" aria-controls="flush-collapseWorkLocation">
                            {{ __("common/sidebar.work_location") }}
                        </button>
                    </h2>
                    <div id="flush-collapseWorkLocation" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <select class="form-control form-select mb-3" id="multiple-select-fieldx" wire:model.live="workLocation" data-placeholder="e.g. Tokyo" aria-describedby="search-location" multiple>
                                @foreach(\App\Models\Location::orderBy('title')->get() as $location)
                                    <option value="{{$location->id}}">{{ $location->title ?? '' }}</option>
                                @endforeach
                            </select>
                            <h4>{{ __("projects/form.work_mode") }}</h4>
                            @foreach(\App\Enums\WorkLocationEnum::cases() as $case)
                                <div class="form-check">
                                    <input class="form-check-input" name="work_mode[]" type="checkbox" value="{{ $case->value }}" id="work_mode_{{$case->value}}">
                                    <label class="form-check-label" for="work_mode_{{$case->value}}">
                                        {{ __("common/sidebar.{$case->name}") }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!---- Work Location -->

                <!---   Affiliation --->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSix" aria-expanded="false" aria-controls="flush-collapseSix">
                            {{ __("common/sidebar.affiliation") }}
                        </button>
                    </h2>
                    <div id="flush-collapseSix" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @foreach(\App\Enums\AffiliationEnum::cases() as $case)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.live="affiliation" value="{{$case->value}}" id="affiliation_{{$case->value}}">
                                    <label class="form-check-label" for="affiliation_{{$case->value}}">
                                        {{ \App\Enums\AffiliationEnum::toName($case->value) ?? __("talents/index.affiliation_{$case->value}") }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!---   Affiliation --->

                <!---- Salary Range ------>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSalary" aria-expanded="false" aria-controls="flush-collapseSalary">
                            {{ __("common/sidebar.monthly_salary_range") }}
                        </button>
                    </h2>
                    <div id="flush-collapseSalary" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body px-0">
                            <div class="row g-0">
                                <div class="col-md-6 px-0">
                                    <label class="form-label" for="minSalary">{{ __("common/sidebar.min_salary") }}</label>
                                    <input type="text" class="form-control" wire:model.live="min_salary" placeholder="{{ __("common/sidebar.min_salary_placeholder") }}" aria-label="Min Salary">
                                </div>
                                <div class="col-md-6 px-0">
                                    <label class="form-label" for="maxSalary">{{ __("common/sidebar.max_salary") }}</label>
                                    <input type="text" class="form-control" wire:model.live="max_salary" placeholder="{{ __("common/sidebar.max_salary_placeholder") }}" aria-label="Last name">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!---- Salary Range ------>

                <!-- Contract Type -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
                            {{ __("projects/form.contract_type") }}
                        </button>
                    </h2>
                    <div id="flush-collapseFive" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @foreach(\App\Enums\ContractClassificationEnum::cases() as $contract)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.live="contract" value="{{ $contract->value }}" id="contract_{{$contract->value}}">
                                    <label class="form-check-label" for="contract_{{$contract->value}}">
                                        {{ __("projects/form.{$contract->name}") }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Contract Type -->
            </div>
        </div>
    </div>
</div>
