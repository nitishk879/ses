<div>
    @if($confirmingOenModal)
        <div class="modal fade show" id="talentResumeModal" tabindex="-1" aria-modal="true" role="dialog" style="display: block;">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content modal-resume">
                    <div class="d-flex w-100 justify-content-center py-3 px-6">
                        <h5 class="modal-title ">{{ __("talents/show.resume_details") }}</h5>

                    </div>
                    <div class="modal-body">
                        @if($talent)
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-md-12 mb-3">
                                        <h2 class="talent-name">{{ $talent->user->short_name }}</h2>
                                        <div class="d-flex w-100 gap-2 talent-data">
                                            <div class="">{{ __("talents/index.gender") }}: <strong>{{ \App\Enums\GenderEnum::toName($talent->user->gender->value) }}</strong></div>
                                            <div class="">{{ __("talents/index.age") }}: <strong>{{ $talent->user->age }}</strong></div>
                                        </div>

                                        {{-- Contact details, up here rather than buried in the
                                             grid below: they are the two things a recruiter
                                             opens this modal to act on. Rendered as links so
                                             the number can be dialled and the address mailed
                                             without retyping either — retyping a phone number
                                             is how a digit gets lost. --}}
                                        <div class="d-flex flex-wrap gap-3 talent-data mt-2">
                                            <div>
                                                {{ __("talents/registration.email") }}:
                                                @if(filled($talent->user->email))
                                                    <a href="mailto:{{ $talent->user->email }}"><strong>{{ $talent->user->email }}</strong></a>
                                                @else
                                                    <strong class="text-muted">—</strong>
                                                @endif
                                            </div>
                                            <div>
                                                {{ __("talents/registration.phone") }}:
                                                @if(filled($talent->user->phone))
                                                    <a href="tel:{{ $talent->user->phone }}"><strong>{{ $talent->user->phone }}</strong></a>
                                                @else
                                                    {{-- Said plainly, because a blank here is
                                                         the reason this candidate cannot be
                                                         invited to a screening call. --}}
                                                    <strong class="text-danger">{{ __("talents/show.no_phone") }}</strong>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 talent-feature py-3 mb-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="row align-items-center">
                                                    <div class="col-6 col-md-5 feature-head">{{ __("talents/index.monthly_income") }}: </div>
                                                    {{-- `salary_range`, as the listing card uses:
                                                         the raw columns printed "700000 - 900000"
                                                         next to a listing that said "700K - 900K"
                                                         for the same candidate. --}}
                                                    <div class="col-6 col-md-7 feature-text">{{ $talent->salary_range ?? '' }} {{ __('talents/index.currency_text') }}</div>
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-6 col-md-5 feature-head">{{ __("talents/index.nationality") }}: </div>
                                                    <div class="col-6 col-md-7 feature-text">{{ $talent->user->nationality ?? '' }}</div>
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-6 col-md-5 feature-head">{{ __("talents/index.nearest_station") }}: </div>
                                                    <div class="col-6 col-md-7 feature-text">{{ filled($talent->user->nearest_station_prefecture) ? $talent->user->nearest_station_prefecture : '—' }}</div>
                                                </div>
                                                <div class="row align-items-center">
                                                    {{-- Was labelled "Operations" and read
                                                         `$talent->availability ? 'Available
                                                         Immediately' : '--'`. There is no
                                                         operations column on talents, and an enum
                                                         is never falsy, so every candidate was
                                                         shown as immediately available — including
                                                         one whose record says "Inquire About
                                                         Participation". Same fabrication as the
                                                         station: a claim the record does not make,
                                                         which a recruiter would staff against. --}}
                                                    <div class="col-6 col-md-5 feature-head">{{ __('talents/index.availability') }}: </div>
                                                    <div class="col-6 col-md-7 feature-text">{{ \App\Enums\ParticipationEnum::toName($talent->availability->value) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row align-items-center">
                                                    <div class="col-6 col-md-5 feature-head">{{ __("talents/registration.affiliation") }}: </div>
                                                    <div class="col-6 col-md-7 feature-text">{{ $talent->affiliation }}</div>
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-6 col-md-5 feature-head">{{ __("talents/index.type_of_contract") }}: </div>
                                                    <div class="col-6 col-md-7 feature-text">
                                                        {{ $talent->myContract!==null ? __("talents/index.{$talent->myContract}"): '' }}
                                                    </div>
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-6 col-md-5 feature-head">{{ __("talents/index.preferred_location") }}: </div>
                                                    <div class="col-6 col-md-7 feature-text">
                                                        @forelse($talent->locations as $location)
                                                            {{ $location->title ?? '' }}@if(!$loop->last), @endif
                                                        @empty
                                                            —
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 my-2">
                                        <div class="p-head mb-2"> {{ __("talents/index.experience") }}</div>
                                        <div>{!! $talent->experience_pr !!}</div>
                                    </div>
                                    <div class="col-md-12 my-2">
                                        <div class="p-head mb-2"> {{ __("talents/index.qualification") }}</div>
                                        <div>{!! $talent->qualifications !!}</div>
                                    </div>
                                    @if($talent->characteristics)
                                        <div class="col-md-12 my-2">
                                            <div class="p-head mb-2"> {{ __("talents/show.hr_char") }}</div>
                                            <div>
                                                <ul class="list-group list-group-flush">
                                                    @foreach($talent->characteristics as $characteristic)
                                                        <li class="list-group-item">{{ $characteristic }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-12 my-2">
                                        <div class="p-head mb-2"> {{ __("talents/show.work_location_details") }}</div>
                                        <div>
                                            <ul class="list-group list-group-flush">
                                                @foreach($talent->locations as $location)
                                                    <li class="list-group-item">{{ $location->title }}</li>
                                                @endforeach
                                            </ul>
                                            <div class="p-head py-2"> {{ __("talents/show.work_mode") }}</div>
                                            <ul class="list-group list-group-flush">
                                                @foreach($talent->work_location as $wLocation)
                                                    <li class="list-group-item">{{ $wLocation }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p>{{ __("talents/show.no_talents_found") }}</p>
                        @endif
                    </div>
                    <div class="d-flex w-100 gap-2 justify-content-center px-6 py-3">
                        <button type="button" class="btn btn-primary print-window" wire:click="download">{{ __("talents/show.download") }}</button>
                        <button type="button" class="btn btn-outline-primary" wire:click="close">{{ __("talents/show.close") }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @else
        <div class="modal fade {{ $confirmingOenModal ? 'show': '' }}" id="staticBackdrop"
             data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Modal title</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Understood</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
