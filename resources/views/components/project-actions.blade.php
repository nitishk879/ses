@props(['project'])

{{--
    The kebab menu on a project card — the same control the talent cards carry,
    built the same way (see livewire/talents/index.blade.php).

    A component rather than markup inside one card, because a project card is
    drawn in two unrelated places: `projects/index.blade.php` renders your own
    company's projects inline, and `livewire/projects/search-result` renders
    everybody else's. The menu first shipped in the second one only — which is
    the one an owner never sees, since the index falls through to it exactly
    when the company has no projects of its own. The feature was invisible to
    every user it was written for.

    Kept as one file so the two cards cannot drift: an action added here appears
    in both, and the policy check below is the single place that decides who
    sees it.
--}}
@can('update', $project)
    @once
        {{-- Inline rather than pushed to the head stack: this component renders
             inside a Livewire component as well as a plain page, and a push
             from inside Livewire does not reach the layout's stack on a
             re-render. `@once` keeps it to a single copy per page. --}}
        <style>
            .project-actions {
                position: absolute;
                top: 0.5rem;
                /* Clear of the card's right edge, where the salary heading sits.
                   Any tighter and the hover pill paints over the figure. */
                right: 0.5rem;
                z-index: 2;
            }

            .project-actions-toggle {
                border: 1px solid transparent;
                border-radius: 50%;
                width: 2rem;
                height: 2rem;
                padding: 0;
                line-height: 1;
                color: #767F8C;
                background: transparent;
            }

            /* `.project-actions-toggle.show`, not `.project-actions.show ...`:
               Bootstrap 5 puts `.show` on the toggle and on the menu, never on
               the `.dropdown` wrapper — dropdown.js adds the class to
               `_element` and `_menu`. The wrapper selector never matched, so an
               open menu had no open state at all. */
            .project-actions-toggle:hover,
            .project-actions-toggle:focus-visible,
            .project-actions-toggle.show {
                color: #1F2430;
                background: #F1F2F4;
                border-color: #D6D8DC;
            }

            /* Bootstrap draws no caret when the toggle carries no
               .dropdown-toggle, but be explicit: the icon is the affordance. */
            .project-actions-toggle::after { display: none; }
        </style>
    @endonce

    <div class="dropdown project-actions">
        {{-- `data-bs-strategy="fixed"` is load-bearing, exactly as on the talent
             card. `#dashboard .job-list` sets `overflow: hidden` to clip its
             corners to the card radius, which also clips the open menu off at
             the card edge. The fixed strategy positions the menu against the
             viewport instead, and nothing in this card's ancestry sets a
             transform, so it really does escape the clip. --}}
        <button type="button"
                class="btn btn-sm project-actions-toggle"
                id="projectActions{{ $project->id }}"
                data-bs-toggle="dropdown"
                data-bs-strategy="fixed"
                aria-expanded="false"
                aria-label="{{ __('projects/index.actions') }}"
                title="{{ __('projects/index.actions') }}">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end"
            aria-labelledby="projectActions{{ $project->id }}">
            <li>
                <a class="dropdown-item" href="{{ route('project.edit', $project) }}">
                    {{ __('projects/index.edit_project') }}
                </a>
            </li>
        </ul>
    </div>
@endcan
