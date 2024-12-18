@extends('layouts.app')

@section('title', $title)

@section('content')
    @php($auth = Auth::user())
    <div class="container" id="dashboard">
        @if($auth->hasRole('admin'))
            <div class="row">
                <x-sidebar>
                    <form wire:submit.prevent="search" class="input-group mb-3">
                        <button type="submit" class="input-group-text" id="search-keyword"><i class="fa-solid fa-magnifying-glass"></i></button>
                        <input type="text" class="form-control search-input" wire:model.live="search" placeholder="{{ __("common/sidebar.search_with_keyword") }}" aria-label="Username" aria-describedby="search-keyword">
                    </form>
                </x-sidebar>
                <div class="col-md-9 col-xl-9">
                    <div class="d-flex w-100 justify-content-between my-3">
                        <h2 class="search-keyword">{{ __("talents/index.showing_results_for") }}: <span>"{{ __("talents/index.search_keyword") }}"</span></h2>
                        <div class="search-sort">
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="inputGroupSelect01">{{ __("talents/index.sort_by") }}</label>
                                <select class="form-select" id="inputGroupSelect01">
                                    <option selected>{{ __("talents/index.recent_listings") }}</option>
                                    <option value="1">Price</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <livewire:companies.index />
                </div>
            </div>
        @else
            @php($company = $auth->company)
            <div class="row align-items-center justify-content-center" style="height: 100vh;">
                <div class="col-md-10 talent-card" wire:key="{{ $company->id }}">
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
                                <h5 class="talent-body-heading">{{__("projects/form.locations")}}</h5>
                                @if($company->addresses)
                                    @foreach($company->addresses as $address)
                                        <h4>{{ $address->street_address }} {{ $address->city }} {{ $address->state }} {{ $address->country }}-{{ $address->zipcode }}</h4>
                                    @endforeach
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
                                            <div class="col-6 col-md-5 feature-head">{{ __("company/index.company_established") }}: </div>
                                            <div class="col-6 col-md-7 feature-text">{{ $company->established ?? '' }}</div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-6 col-md-5 feature-head">{{ __("company/index.company_total_employees") }}: </div>
                                            <div class="col-6 col-md-7 feature-text">{{ $company->number_of_employees ?? '' }}</div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-6 col-md-5 feature-head">{{ __("company/index.dispatch_license") }}: </div>
                                            <div class="col-6 col-md-7 feature-text">{{ $company->dispatch_business_license ?? '' }}</div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-6 col-md-5 feature-head">{{ __('company/index.specialization') }}: </div>
                                            <div class="col-6 col-md-7 feature-text">{{ $company->specialized_industries ?? '' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row align-items-center">
                                            <div class="col-6 col-md-5 feature-head">{{ __("company/index.company_capital") }}: </div>
                                            <div class="col-6 col-md-7 feature-text">{{ $company->capital }}</div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-6 col-md-5 feature-head">{{ __("company/index.company_total_employees") }}: </div>
                                            <div class="col-6 col-md-7 feature-text">
                                                {{ $company->number_of_employees ?? '' }}
                                            </div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-6 col-md-5 feature-head">{{ __("company/index.company_location") }}: </div>
                                            <div class="col-6 col-md-7 feature-text">
                                                {{ $company->company_location }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
