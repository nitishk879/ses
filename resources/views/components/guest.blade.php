<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} || {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <!-- Favicons -->
    <link rel="icon" href="{{ asset("images/logo.png") }}">

    @hasSection('select2')
        <!-- Styles -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
        <!-- Or for RTL support -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    @endif

    @stack('stylesheets')
</head>
<body>
<div id="app">
    {{ $slot }}
</div>
@hasSection('modals')
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="d-flex justify-content-center align-items-center w-100 p-3">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">{{ __("common/common.sample_data_title") }}</h1>
                </div>
                <div class="modal-body">
                    <div class="cover-letter ">
                        <h4 id="modalTitle">{{ __("common/common.modal_title") }}</h4>
                        <div class="cover-letter-description border border-secondary-subtle p-4"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("common/common.close") }}</button>
                    <button type="button" class="btn btn-primary" data-bs-dissmiss="modal" id="pasteButton">{{ __("common/common.use") }}</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function openDynamicModal(id) {
            // Make an Axios request to fetch data for the modal
            axios.get('/sample/' + id)
                .then(response => {
                    const data = response.data;
                    // console.log(data);
                    // Set the modal title and content dynamically
                    document.getElementById('staticBackdropLabel').textContent = data.title;
                    document.getElementById('modalTitle').textContent = data.title;
                    document.querySelector('.cover-letter-description').innerHTML = data.content;
                    document.getElementById('pasteButton').addEventListener('click', function () {
                        // Target the textarea and set its value
                        let targetId = id > 3 ? 3 : id;
                        const textarea = document.getElementById('targetTextarea'+targetId);
                        textarea.value = data.content;
                        // // Hide the modal
                        const modal = new bootstrap.Modal('#staticBackdrop');
                        modal.hide();
                        // Optional: Bring focus to the textarea
                        textarea.focus();
                    });

                })
                .catch(error => {
                    console.error('There was an errors fetching the data!', error);
                    alert('Failed to fetch data.');
                });
        }
    </script>
@endif
@hasSection('select2')
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
@endif
@hasSection('editor')
    <!-- TinyMCE CDN -->
    <!---- Summer note libraries -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
    <script>
        $('.tinyEditor').summernote({
            placeholder: "{{ __("talents/registration.write_bio") }}",
            tabsize: 2,
            height: 120,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    </script>
    <!---- Summer note libraries -->
@endif

@stack('scripts')
</body>
</html>
