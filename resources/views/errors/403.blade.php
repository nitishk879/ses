<x-guest>
    <x-slot name="title">
        {{ __("common/common.401_error_title") }}
    </x-slot>

    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="text-center">
            <h1 class="display-1 fw-bold">401</h1>
            <p class="fs-3"> {{ __("common/common.401_error_title") }}</p>
            <p class="lead">
                {{ __("common/common.401_error_text") }}
            </p>
            <a href="{{ config("app.url") }}" class="btn btn-primary">{{ __("common/common.back_to_home") }}</a>
        </div>
    </div>
</x-guest>
