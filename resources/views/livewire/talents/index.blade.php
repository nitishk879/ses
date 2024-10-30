<div>
    <div class="d-flex w-100 justify-content-between my-3">
        <h2 class="search-keyword">
            @if($search)
                {{ __("talents/index.showing_results_for") }}: <span>"{{ $search ?? __("talents/index.search_keyword") }}"</span>
            @endif
        </h2>
        <div class="search-sort">
            <div class="input-group mb-3">
                <label class="input-group-text" for="sortResultBy">{{ __("talents/index.sort_by") }}</label>
                <select class="form-select" id="sortResultBy">
                    <option wire:click="sortBy('created_at')">{{ __("talents/index.recent_listings") }}</option>
                    <option wire:click="sortBy('updated_on')">{{ __("talents/index.updated_on") }}</option>
                    <option wire:click="sortBy('created_on')">{{ __("talents/index.registered_on") }}</option>
                    <option wire:click="sortBy('joining_date')">{{ __("talents/index.favourite") }}</option>
                    <option wire:click="sortBy('deadline')">{{ __("talents/index.application_deadline") }}</option>
                </select>
            </div>
        </div>
    </div>
    @if($talents->count() >=1)
        @foreach($talents as $talent)
            <div class="talent-card" wire:key="{{ $talent->id }}">
                <a href="" class="add-to-favourite">
                    <i class="fa-solid fa-star"></i>
                </a>
                <div class="talent-card-header">
                    <div class="row justify-content-between">
                        <div class="col-md-4 ps-md-3">
                            <span class="talent-name">{{ $talent->user->short_name }}</span>
                            <div class="d-flex gap-2">
                                <span class="talent-age">{{ __("talents/index.gender") }} : <b>{{ \App\Enums\GenderEnum::toName($talent->user->gender->value) }}</b></span>
                                <span class="talent-age">{{ __("talents/index.age") }} : <b>{{ $talent->user->age }}</b></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2 align-items-center talent-updated"><i class="fa-solid fa-calendar-days"></i>
                                {{ __("talents/index.registered_on") }}: {{ $talent->user->created_at->format('M d, Y') }}</div>
                            <div class="d-flex gap-2 align-items-center talent-updated"><i class="fa-solid fa-rotate"></i>
                                {{ __("talents/index.updated_on") }}: {{ $talent->user->updated_at->format('M d, Y') }}</div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="" class="talent-save"><i class="fas fa-heart"></i> {{ random_int(18, 50) }}</a>
                        </div>
                    </div>
                </div>
                <div class="talent-card-body">
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <h3 class="talent-availability">{{ __('talents/index.availability') }}:
                                <span>{{ \App\Enums\ParticipationEnum::toName($talent->availability->value) ?? __("talents/index.{$talent->availability}") }}</span>
                            </h3>
                        </div>
                        <div class="col-md-12">
                            <h5 class="talent-body-heading">{{ __("talents/index.experience") }}</h5>
                            <p>{!! $talent->experience_pr ?? __("talents/index.talent_description") !!}</p>
                        </div>
                        <div class="col-md-12">
                            <h5 class="talent-body-heading">{{__("talents/index.qualification")}}</h5>
                            @if($talent->qualifications)
                                {!! $talent->qualifications !!}
                            @else
                                <ul>
                                    <li>{{ __("talents/index.talent_q1") }}</li>
                                    <li>{{ __("talents/index.talent_q2") }}</li>
                                    <li>{{ __("talents/index.talent_q3") }}</li>
                                </ul>
                            @endif
                        </div>
                        <div class="talent-featured-section">
                            <div class="row justify-content-evenly">
                                <div class="col-md-5">
                                    <div class="row align-items-center">
                                        <div class="col-6 col-md-5 feature-head">{{ __("talents/index.monthly_income") }}: </div>
                                        <div class="col-6 col-md-7 feature-text">{{ $talent->salary_range ?? '' }} {{ __('talents/index.currency_text') }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-6 col-md-5 feature-head">{{ __("talents/index.nationality") }}: </div>
                                        <div class="col-6 col-md-7 feature-text">{{ $talent->user->nationality ?? '' }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-6 col-md-5 feature-head">{{ __("talents/index.nearest_station") }}: </div>
                                        <div class="col-6 col-md-7 feature-text">{{ $talent->user->nearest_station_prefecture ?? 'Maihama Station on the Keiyo Line (Chiba Prefecture)' }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-6 col-md-5 feature-head">{{ __('talents/index.operations') }}: </div>
                                        <div class="col-6 col-md-7 feature-text">{{ \App\Enums\ParticipationEnum::toName($talent->availability->value) ?? __("talents/index.{$talent->availability}") }}</div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row align-items-center">
                                        <div class="col-6 col-md-5 feature-head">{{ __("talents/registration.affiliation") }}: </div>
                                        <div class="col-6 col-md-7 feature-text">{{ $talent->affiliation }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-6 col-md-5 feature-head">{{ __("talents/index.type_of_contract") }}</div>
                                        <div class="col-6 col-md-7 feature-text">
                                            {{ $talent->myContract!==null ? __("talents/index.{$talent->myContract}"): '' }}
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-6 col-md-5 feature-head">{{ __("talents/index.preferred_location") }}: </div>
                                        <div class="col-6 col-md-7 feature-text">
                                            @foreach($talent->locations as $location)
                                                {{ $location->title ?? '' }} @if(!$loop->last), @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-grid gap-2">
                                        <button type="button"
                                                wire:click="$dispatch('confirmingOenModal', { id:{{ $talent->id }} })"
                                                id="openTalentModal"
                                                class="btn btn-outline-primary">
                                            {{ __("talents/index.view_resume") }}
                                        </button>
                                        <button type="button"
                                                id="openTalentModal"
                                                wire:click="$dispatch('confirmingOenModal', { id:{{ $talent->id }} })"
                                                class="btn btn-outline-primary">
                                            {{ __("talents/index.view_profile") }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div>
            {{ $talents->links() }}
        </div>
    @else
        <div class="">
            <p>{!! __("talents/show.search_error") !!}</p>
        </div>
    @endif
    <livewire:talents.talent-modal />
</div>
@push('scripts')
    <script>
        Livewire.on('openModal', () => {
            const resumeModal = new bootstrap.Modal(document.getElementById('talentResumeModal'));
            resumeModal.show();
        });

        Livewire.on('closeModal', () => {
            const resumeModal = new bootstrap.Modal(document.getElementById('talentResumeModal'));
            resumeModal.hide();
        });

    </script>
@endpush
