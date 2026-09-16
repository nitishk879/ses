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
            /* Right padding leaves room for the actions kebab pinned in the
               corner, so a long reason line does not run underneath it. */
            padding: 0.85rem 3.25rem 0.85rem 3rem;
            border-bottom: 1px solid #E4E5E8;
            background: #FFFFFF;
        }

        /*
         * The card's actions, in its top-right corner.
         *
         * Absolute rather than a flex child of the match band: that band only
         * renders when a project is selected, so anything inside it disappears
         * on the plain Find Talent screen. Pinned to the card instead, it sits
         * level with the score row when there is one and in the same place when
         * there is not.
         *
         * z-index 2 clears `.add-to-favourite`, which is z-index 1 — the same
         * stacking fight that once hid the leading digits of the score.
         */
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

        /*
         * The score used to be a tinted pill with the caption floating beside
         * it: two boxes competing for the same job, the number framed twice,
         * and "64" sitting in mid-air with nothing to say how full 64 is.
         *
         * Now it reads top-down the way a figure should — label, then value,
         * then a meter that gives the value its scale — so one glance answers
         * "how good is this" and the reasons underneath answer "why".
         */
        .talent-match-head {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .talent-match-caption {
            font-family: "Mulish", sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #8A9099;
        }

        .talent-match-figure {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .talent-match-score {
            display: inline-flex;
            align-items: baseline;
            font-family: "Mulish", sans-serif;
            white-space: nowrap;
            /* No background. The number is the element, not a chip on top of one. */
        }

        .talent-match-number {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
        }

        /*
         * Always present, and deliberately not faded into illegibility: a bare
         * "64" invites the reader to supply their own scale.
         */
        .talent-match-outof {
            font-size: 0.875rem;
            font-weight: 600;
            color: #8A9099;
            margin-left: 0.15rem;
        }

        /*
         * The meter is what makes the number mean something at a glance. It is
         * decoration only — the figure beside it carries the same information,
         * so nothing is lost in greyscale or to a screen reader.
         */
        .talent-match-meter {
            flex: 1 1 8rem;
            max-width: 12rem;
            height: 6px;
            border-radius: 999px;
            background: #ECEEF1;
            overflow: hidden;
        }

        .talent-match-meter-fill {
            display: block;
            height: 100%;
            border-radius: inherit;
        }

        /*
         * Colour is a second signal, never the only one — the number itself
         * carries the meaning, so these stay legible in greyscale.
         */
        .talent-match-score--strong .talent-match-number { color: #12653A; }
        .talent-match-score--fair   .talent-match-number { color: #8A5A00; }
        .talent-match-score--weak   .talent-match-number { color: #4A4D55; }

        .talent-match-meter-fill--strong { background: #2E9E63; }
        .talent-match-meter-fill--fair   { background: #E0A32E; }
        .talent-match-meter-fill--weak   { background: #A9AEB6; }

        .talent-match-score--none {
            display: inline-flex;
            background: #F1F2F4;
            border: 1px solid #D6D8DC;
            border-radius: 0.5rem;
            color: #4A4D55;
            font-family: "Mulish", sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
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
            .talent-match-number { font-size: 1.6rem; }
            .talent-match-meter { max-width: none; }
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

