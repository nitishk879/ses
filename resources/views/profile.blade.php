@extends('layouts.app')

@section('title', __("users/form.update_profile"))

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="page-heading">{{ __('users/form.update_profile') }}</h1>
                <h4 class="pb-3">{{ __("talents/index.last_logged_in") }}: {{ $user->last_login }}</h4>
            </div>
        </div>
        <form action="{{ route("profile.update") }}" method="post" class="col-md-12 needs-validation" enctype="multipart/form-data" novalidate>
            @csrf @method('PUT')
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
                                               value="{{ $user->firstname ?? old('firstname') ?? '' }}" aria-label="First name" required>
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
                                               value="{{ $user->lastname ?? old('lastname') ?? '' }}" aria-label="Last name" required>
                                        @error('lastname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="emailAddress"
                                               class="form-label required">{{ __('talents/registration.email') }}</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                               id="emailAddress" value="{{ $user->email ?? old('email') ?? '' }}" placeholder="johndoe@email.com"
                                               required>
                                        @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="userPhone"
                                               class="form-label required">{{ __('users/form.phone') }}</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone"
                                               id="userPhone" value="{{ $user->phone ?? old('phone') ?? '' }}" placeholder="919858025443"
                                               required>
                                        @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="dateOfBirth"
                                               class="form-label required">{{ __('talents/registration.date_of_birth') }}</label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth"
                                               id="dateOfBirth" value="{{ $user->date_of_birth->format('Y-m-d') ?? old('date_of_birth') ?? '' }}" placeholder="10/02/2000"
                                               required>
                                        @error('date_of_birth')
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
                                                    @selected(old('gender', $user->gender->value) == $gender->value)
                                                >{{ __("talents/registration.{$gender->value}") }}</option>
                                            @endforeach
                                        </select>
                                        @error('gender')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="talentAddress" class="form-label">{{ __('talents/registration.address') }}</label>
                                    <input class="form-control @error('address') is-invalid @enderror" id="talentAddress" name="address" value="{{ $user->address ?? old("address") ?? '' }}" placeholder="{{ __('talents/registration.type_your_address_here') }}">
                                    @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <!-- Basic detail --->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row px-2">
                        <!-- Station detail Block --->
                        <div class="col-md-12 bg-light mb-4">
                            <div class="bg-light p-3">
                                <h2>{{ __("talents/registration.nearest_station") }}</h2>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="preferredLocation"
                                               class="form-label">{{ __('talents/registration.prefectures') }}</label>
                                        <input type="text" class="form-control @error('nearest_station_prefecture') is-invalid @enderror"
                                               name="nearest_station_prefecture" id="preferredLocation"
                                               placeholder="{{ __('talents/registration.prefectures') }}"
                                               value="{{ $user->nearest_station_prefecture ?? old("nearest_station_prefecture") ?? '' }}"
                                        >
                                        @error('nearest_station_prefecture')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="otherDesiredLocation"
                                               class="form-label">{{ __('talents/registration.route_name') }}</label>
                                        <input type="text"
                                               class="form-control @error('nearest_station_line') is-invalid @enderror"
                                               name="nearest_station_line" id="otherDesiredLocation"
                                               value="{{ $user->nearest_station_line ?? old("nearest_station_line") ?? "" }}"
                                               placeholder="{{ __('talents/registration.route_name') }}">
                                        @error('nearest_station_line')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="otherDesiredLocation"
                                               class="form-label">{{ __('talents/registration.station_name') }}</label>
                                        <input type="text"
                                               class="form-control @error('nearest_station_name') is-invalid @enderror"
                                               name="nearest_station_name" id="otherDesiredLocation"
                                               value="{{ $user->nearest_station_name ?? old("nearest_station_name") ?? "" }}"
                                               placeholder="{{ __('talents/registration.station_name') }}">
                                        @error('nearest_station_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="country"
                                               class="form-label">{{ __('users/form.country') }}</label>
                                        <select class="form-select @error('country') is-invalid @enderror"
                                                name="country" id="country" aria-label="country">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            <option value="japanese"
                                                @selected(old('country', $user->country) == 'Japan')
                                            > {{ __("users/form.japan") }}</option>
                                            <option value="other"
                                                @selected(old('country', $user->country) != 'Japan')
                                            >{{ __("users/form.other") }}</option>
                                        </select>
                                        @error('country')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nationality"
                                               class="form-label">{{ __('talents/registration.nationality') }}</label>
                                        <select class="form-select @error('nationality') is-invalid @enderror"
                                                name="nationality" id="nationality" aria-label="nationality">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            <option value="japanese"
                                                @selected(old('nationality', $user->nationality) == 'japanese')
                                            > {{ __("talents/registration.japanese") }}</option>
                                            <option value="other"
                                                @selected(old('nationality', $user->nationality) != 'japanese')
                                            >{{ __("talents/registration.english") }}</option>
                                        </select>
                                        @error('nationality')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="language" class="form-label">{{ __('talents/registration.language') }}</label>
                                        <select class="form-select @error('language') is-invalid @enderror"
                                                name="language" id="language" aria-label="language">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Enums\LangEnum::cases() as $lang)
                                                <option value="{{ $lang->value }}" @selected(in_array($lang->value, $user->languages ?? []) ?? old('language') == $lang->value)
                                                > {{ \App\Enums\LangEnum::toName($lang->value) ?? __("talents/registration.japanese") }}</option>
                                            @endforeach
                                        </select>
                                        @error('language')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Station detail Block --->
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
                    <button type="submit" class="g-col-4 btn btn-submit">{{ __("users/form.submit") }}</button>
                </div>
            </div>
        </form>
        @env('local')
            @php($roles = Auth::user()->roles)
            <div class="row justify-content-center py-3">
                <div class="col-md-6">
                    @if($roles->count() >=1)
                        <ul class="list-group">
                            {{ __("users/roles.roles") }}:
                            @foreach($roles as $role)
                                <li class="list-group-item" itemprop="{{ $role->slug }}">{{ $role->title ?? $role->slug }}</li>
                            @endforeach
                        </ul>
                    @else
                        <form method="post" class="col-md-10 col-xl-8">
                            @csrf
                            <label for="roles" class="form-label">{{ __("users/roles.roles") }}</label>
                            @foreach(\App\Models\Role::whereIn('id', [2,3,])->get() as $role)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input @error('roles') is-invalid @enderror"
                                           type="checkbox" id="roles_{{$role->id}}" name="roles[]" value="{{ $role->id }}">
                                    <label class="form-check-label"
                                           for="roles_{{$role->id}}">{{ __("users/roles.{$role->slug}") }}</label>
                                </div>
                            @endforeach
                        </form>
                    @endif
                </div>
            </div>
        @endenv
    </div>
@endsection
