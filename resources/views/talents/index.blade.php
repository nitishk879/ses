@extends('layouts.app')

@section('title', 'Talents')

@section('content')
    <div class="container-fluid container-lg pb-4" id="dashboard">
        <div class="row justify-content-center">
            <div class="col-md-4 col-xl-3 offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
                <div class="offcanvas-body">
                    <livewire:talents.search-form />
                </div>
            </div>
            <div class="col-md-8 col-lg-9 col-xl-9">
                <!-- Top Nav -->
                <div class="my-2">
                    <button class="btn btn-primary d-md-none offCanvasBtn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>
                </div>
                <!--End Top Nav -->
                <livewire:talents.index/>
            </div>
        </div>
    </div>
@endsection

@push('stylesheets')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Small using Bootstrap 5 classes
        $("#multiple-select-field").select2({
            theme: "bootstrap-5",
            dropdownParent: $("#multiple-select-field").parent(), // Required for dropdown styling
        });
    </script>
@endpush

