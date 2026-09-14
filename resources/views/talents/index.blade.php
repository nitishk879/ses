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
        /*
         * 3rem of left padding is not arbitrary. `.talent-card .add-to-favourite`
         * is a 3.5rem circle pinned at left:-1rem, so its right edge lands 2.5rem
         * inside the card and it carries z-index:1. Anything laid out in that
         * strip is drawn underneath it — which is exactly what hid the leading
         * digits of the score. 3rem clears the circle with room to spare, and
         * leaves the star clickable, which raising z-index here would not.
         */
        .talent-match {
            padding: 0.85rem 1.5rem 0.85rem 3rem;
            border-bottom: 1px solid #E4E5E8;
            background: #FFFFFF;
        }

        .talent-match-head {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        /*
         * The score is the reason this screen exists when a project is picked,
         * so it is set as a number to be read, not a chip to be glanced at.
         * "/100" is always present: a bare "64" invites the reader to supply
         * their own scale.
         */
        .talent-match-score {
            display: inline-flex;
            align-items: baseline;
            padding: 0.2rem 0.65rem;
            border-radius: 0.5rem;
            border: 1px solid transparent;
            font-family: "Mulish", sans-serif;
            white-space: nowrap;
        }

        .talent-match-number {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .talent-match-outof {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.7;
            margin-left: 0.1rem;
        }

        /*
         * Colour is a second signal, never the only one — the number itself
         * carries the meaning, so these stay legible in greyscale and none of
         * them relies on white-on-yellow.
         */
        .talent-match-score--strong { background: #E3F6EA; border-color: #9BD9B2; color: #12653A; }
        .talent-match-score--fair   { background: #FFF4DC; border-color: #F0C475; color: #7A4E00; }
        .talent-match-score--weak   { background: #F1F2F4; border-color: #D6D8DC; color: #4A4D55; }
        .talent-match-score--none   {
            background: #F1F2F4;
            border-color: #D6D8DC;
            color: #4A4D55;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
        }

        .talent-match-caption {
            font-family: "Mulish", sans-serif;
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #767F8C;
        }

        .talent-match-flag {
            font-family: "Mulish", sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            color: #4A4D55;
            background: #FFFFFF;
            border: 1px solid #D6D8DC;
            border-radius: 999px;
            padding: 0.15rem 0.6rem;
            cursor: help;
        }

        .talent-match-reasons {
            list-style: none;
            margin: 0.6rem 0 0;
            padding: 0;
            font-family: "Mulish", sans-serif;
            font-size: 0.875rem;
            line-height: 1.5rem;
            color: #535353;
        }

        .talent-match-reasons .is-blocker    { color: #C0392B; }
        .talent-match-reasons .is-unverified { color: #767F8C; }

        .talent-match-note {
            font-family: "Mulish", sans-serif;
            font-size: 0.875rem;
            color: #767F8C;
            margin-top: 0.4rem;
        }

        /* The circle is the same size at every breakpoint, so the clearance
           has to be too — only the right-hand gutter tightens. */
        @media (max-width: 575.98px) {
            .talent-match { padding-right: 1rem; }
            .talent-match-number { font-size: 1.35rem; }
        }
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

