<div class="container">
    <div class="row justify-content-center align-items-center">
        <form wire:submit.prevent="update" class="col-md-12">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="col-md-12 mb-4">
                        <div class="bg-light p-3">
                            <div class="col-md-12">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <label for="firstName"
                                               class="col-form-label required">{{ __("talents/registration.firstname") }}</label>
                                        <input type="text" class="form-control @error('firstname') is-invalid @enderror"
                                               id="firstName" wire:model.blur="profile.firstname"
                                               placeholder="{{ __("talents/registration.firstname") }}"
                                               aria-label="First name" required>
                                        @error('firstname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName"
                                               class="col-form-label required">{{ __("talents/registration.lastname") }}</label>
                                        <input type="text" class="form-control @error('firstname') is-invalid @enderror"
                                               id="lastName" wire:model.blur="profile.lastname"
                                               placeholder="{{ __("talents/registration.lastname") }}"
                                               aria-label="Last name" required>
                                        @error('lastname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="userEmail" class="col-form-label required">{{ __("users/form.email") }}</label>
                                <input type="email" class="form-control @error('profile.email') is-invalid @enderror"
                                       id="userEmail"
                                       wire:model="profile.email"
                                       placeholder="{{ __("users/form.email") }}"
                                       aria-label="user email" required>
                                @error('profile.email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="userPhone" class="col-form-label required">{{ __("users/form.phone") }}</label>
                                <input type="text" class="form-control @error('profile.phone') is-invalid @enderror"
                                       id="userPhone"
                                       wire:model="profile.phone"
                                       placeholder="{{ __("users/form.phone") }}"
                                       aria-label="user phone" required>
                                @error('profile.phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6 mb-3">
                                        <label for="language"
                                               class="form-label">{{ __('talents/registration.language') }}</label>
                                        <select class="form-select @error('profile.languages') is-invalid @enderror"
                                                wire:model.blur="profile.languages" id="language" aria-label="language">
                                            <option value="">{{ __("talents/registration.choose") }}</option>
                                            @foreach(\App\Enums\LangEnum::cases() as $lang)
                                                <option value="{{ $lang->value }}" @selected(old('language') == $lang->value) selected> {{ \App\Enums\LangEnum::toName($lang->value) ?? __("talents/registration.japanese") }}</option>
                                            @endforeach
                                        </select>
                                        @error('profile.languages')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-4 text-center d-grid gap-2 mb-3">
                    <button type="submit" class="g-col-4 btn btn-submit">{{ __("users/form.submit") }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
