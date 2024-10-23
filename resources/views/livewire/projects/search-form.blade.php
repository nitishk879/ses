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
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="from_home">
                                <label class="form-check-label" for="from_home">
                                    {{ __('Work From Home') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="2" id="from_home">
                                <label class="form-check-label" for="from_home">
                                    {{ __('Part time') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <!---- Work Location -->

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
                                <div class="col-md-5 px-0">
                                    <input type="text" class="form-control" wire:model.live="min_salary" placeholder="{{ __("common/sidebar.min_salary_placeholder") }}" aria-label="First name">
                                </div>
                                <div class="col-md-2 px-2">{{ __("To") }}</div>
                                <div class="col-md-5 px-0">
                                    <input type="text" class="form-control" wire:model.live="max_salary" placeholder="{{ __("common/sidebar.max_salary_placeholder") }}" aria-label="Last name">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!---- Salary Range ------>

                <!---- Possible Participation -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsePossibleParticipation" aria-expanded="false" aria-controls="flush-collapsePossibleParticipation">
                            {{ __("common/sidebar.contract_period") }}
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
                        </div>
                    </div>
                </div>
                <!---- Possible Participation -->

                <!--- Status of The Project ----->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseNationality" aria-expanded="false" aria-controls="flush-collapseNationality">
                            {{ __("projects/form.status_of_the_project") }}
                        </button>
                    </h2>
                    <div id="flush-collapseNationality" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @foreach(\App\Enums\CommercialFlow::cases() as $nation)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" wire:model.live="nationality" type="checkbox" id="inline{{$nation}}" value="{{ $nation }}">
                                    <label class="form-check-label" for="inline{{$nation}}">{{ $nation->value ==1 ? __("common/sidebar.confirmed") : __("common/sidebar.before_confirm") }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!--- Status of The Project ----->

                <!--- Project Flow ----->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseGender" aria-expanded="false" aria-controls="flush-collapseGender">
                            {{ __("common/sidebar.project_flow") }}
                        </button>
                    </h2>
                    <div id="flush-collapseGender" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @foreach(\App\Enums\TradeClassification::cases() as $flow)
                                <div class="form-check">
                                    <input class="form-check-input" wire:model.live="trade_classification" type="checkbox" id="inline{{$flow}}" value="{{ $flow }}">
                                    <label class="form-check-label" for="inline{{$flow}}">{{ __("common/sidebar.{$flow->name}") }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!--- Project Flow ----->

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
                                    <input class="form-check-input" type="checkbox" wire:model.live="contract_classification" value="{{ $contract->value }}" id="contract_{{$contract->value}}">
                                    <label class="form-check-label" for="contract_{{$contract->value}}">
                                        {{ __("projects/form.{$contract->name}") }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Contract Type -->

                <!-- No of Interview -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseInterview" aria-expanded="false" aria-controls="flush-collapseInterview">
                            {{ __("common/home.no_of_interviews") }}
                        </button>
                    </h2>
                    <div id="flush-collapseInterview" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="d-block mb-3">
                                @foreach(\App\Enums\InterviewEnum::cases() as $case)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               wire:model.live="interview"
                                               value="{{ $case->value }}"
                                               id="interview_{{ $case->value }}">
                                        <label class="form-check-label" for="interview_{{ $case->value }}">{{ __("common/sidebar.interview_{$case->name}") }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!-- No of Interview -->
                <div class="col-md-12 text-center my-3">
                    <button class="btn btn-outline-secondary" wire:click="clear">{{ __("common/sidebar.clear") }}</button>
                </div>
            </div>
        </div>
    </div>
</div>