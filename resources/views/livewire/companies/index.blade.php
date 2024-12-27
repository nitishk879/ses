<div>

    <div>
        @foreach ($companies as $company)
            <div class="talent-card" wire:key="{{ $company->id }}">
                <a href="" class="add-to-favourite">
                    <i class="fa-solid fa-star"></i>
                </a>
                <div class="talent-card-header">
                    <div class="row justify-content-between align-items-end">
                        <div class="col-md-6 ps-md-3">
                            <span class="talent-name">
                                <img src="{{ $company->company_logo_url }}" alt="" height="32" width="32" class="rounded-5 me-3"/>
                                {{ $company->company_name ?? '' }}
                            </span>
                            <div class="d-flex gap-2">
                                <span class="talent-age">{{ __("company/index.company_email") }} : <b>{{ \Illuminate\Support\Str::mask($company->company_email, "*", -15, 3) ?? '' }}</b></span>
                                <span class="talent-age">{{ __("company/index.company_phone") }} : <b>{{ \Illuminate\Support\Str::mask($company->telephone_number, "*", -7, 3) }}</b></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 align-items-center talent-updated"><i class="fa-solid fa-calendar-days"></i>
                                {{ __("talents/index.registered_on") }}: {{ $company->created_at->format('M d, Y') }}</div>
                            <div class="d-flex gap-2 align-items-center talent-updated"><i class="fa-solid fa-rotate"></i>
                                {{ __("talents/index.updated_on") }}: {{ $company->updated_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
                <div class="talent-card-body">
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <h3 class="talent-availability">{{ __("company/index.company_website") }}:
                                <span>{{ $company->company_url ?? '' }}</span>
                            </h3>
                        </div>
                        <div class="col-md-12">
                            <h5 class="talent-body-heading">{{ __('company/index.company_introduction') }}</h5>
                            <p>{!! $company->company_introduction ?? __("talents/index.talent_description") !!}</p>
                        </div>
                        <div class="col-md-12">
                            <h5 class="talent-body-heading">{{__("talents/index.qualification")}}</h5>
                            @if($company->qualifications)
                                {!! $company->qualifications !!}
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
                                        <div class="col-lg-5 feature-head">{{ __("company/index.company_established") }}: </div>
                                        <div class="col-lg-7 feature-text">{{ $company->established ?? '' }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-5 feature-head">{{ __("company/index.company_total_employees") }}: </div>
                                        <div class="col-lg-7 feature-text">{{ $company->number_of_employees ?? '' }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-5 feature-head">{{ __("company/index.dispatch_license") }}: </div>
                                        <div class="col-lg-7 feature-text">{{ $company->dispatch_business_license ?? '' }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-5 feature-head">{{ __('company/index.specialization') }}: </div>
                                        <div class="col-lg-7 feature-text">{{ $company->specialized_industries ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row align-items-center">
                                        <div class="col-lg-5 feature-head">{{ __("company/index.company_capital") }}: </div>
                                        <div class="col-lg-7 feature-text">{{ $company->capital }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-5 feature-head">{{ __("company/index.company_total_employees") }}: </div>
                                        <div class="col-lg-7 feature-text">
                                            {{ $company->number_of_employees ?? '' }}
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-5 feature-head">{{ __("company/index.company_location") }}: </div>
                                        <div class="col-lg-7 feature-text">
                                            {{ $company->company_location }}
                                        </div>
                                    </div>
                                </div>
{{--                                <div class="col-md-2">--}}
{{--                                    <div class="d-grid gap-2">--}}
{{--                                        <button type="button"--}}
{{--                                                wire:click="$dispatch('confirmingOenModal', { id:{{ $company->id }} })"--}}
{{--                                                id="openTalentModal"--}}
{{--                                                class="btn btn-outline-primary">--}}
{{--                                            {{ __("talents/index.view_resume") }}--}}
{{--                                        </button>--}}
{{--                                        <button type="button"--}}
{{--                                                id="openTalentModal"--}}
{{--                                                wire:click="$dispatch('confirmingOenModal', { id:{{ $company->id }} })"--}}
{{--                                                class="btn btn-outline-primary">--}}
{{--                                            {{ __("talents/index.view_profile") }}--}}
{{--                                        </button>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{ $companies->links() }}

</div>
