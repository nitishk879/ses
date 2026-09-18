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

    {{--
        Match-score block on the talent card.

        Declared here rather than in resources/sass/stylesheet.scss on purpose.
        `public/build` is gitignored and the bundle deployed on the server was
        built in December 2024, so running the asset build now would ship every
        unrelated stylesheet change made since — a whole-site visual change to
        fix one card. This ships with the page instead and needs no build.

        Pushed from the parent view, not from the Livewire component: a stack
        push from inside a component runs after the layout's @stack has already
        been rendered on a Livewire update, so the rules would be missing on
        every re-render after the first.
    --}}
    <style>
        /* z-index 2 clears .add-to-favourite, which is z-index 1. */
        .talent-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.75rem;
            z-index: 2;
        }

        .talent-actions-toggle {
            border: 1px solid transparent;
            border-radius: 50%;
            width: 2rem;
            height: 2rem;
            padding: 0;
            line-height: 1;
            color: #767F8C;
            background: transparent;
        }

        .talent-actions-toggle:hover,
        .talent-actions-toggle:focus-visible,
        .talent-actions.show .talent-actions-toggle {
            color: #1F2430;
            background: #F1F2F4;
            border-color: #D6D8DC;
        }

        /* Bootstrap draws no caret when the toggle carries no .dropdown-toggle,
           but be explicit: the icon is the affordance. */
        .talent-actions-toggle::after { display: none; }
    </style>
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

