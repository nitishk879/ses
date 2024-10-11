@extends('layouts.app')

@section('title', 'Talents')

@section('content')
    <div class="container pb-4" id="dashboard">
        <div class="row justify-content-center">
            <div class="col-md-3 col-xl-3 mb-5" id="sidebar">
                <livewire:talents.search-form />
            </div>
            <div class="col-md-9 col-xl-9">
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

