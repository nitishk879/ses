@extends('layouts.app')

@section('title', __("company/register.heading_title"))

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="page-heading">{{ __('company/register.heading_title') }}</h1>
            </div>
        </div>
        <div class="row justify-content-center align-items-center">
            @php($user = Auth::user())
            @livewire('company.register', ['user' => $user, 'company' => $user?->company])
        </div>
    </div>
@endsection

@push('scripts')
    <!-- TinyMCE CDN -->
    <script src="https://cdn.tiny.cloud/1/5rfctkmlgw069tt5rp30sjxqu32fzlox611u5kxk18tm9g0k/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        tinymce.init({
            selector: 'textarea.tinyEditor',  // change this value according to your HTML
            license_key: '5rfctkmlgw069tt5rp30sjxqu32fzlox611u5kxk18tm9g0k',
            height: 220
        });
        // Prevent Bootstrap dialog from blocking focusin
        document.addEventListener('focusin', (e) => {
            if (e.target.closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
                e.stopImmediatePropagation();
            }
        });
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (() => {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
@endpush
