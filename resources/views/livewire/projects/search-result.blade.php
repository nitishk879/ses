<div>
    @once
        <style>
            .project-actions {
                position: absolute;
                top: 0.5rem;
                right: 0.75rem;
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

            .project-actions-toggle:hover,
            .project-actions-toggle:focus-visible,
            .project-actions.show .project-actions-toggle {
                color: #1F2430;
                background: #F1F2F4;
                border-color: #D6D8DC;
            }

            .project-actions-toggle::after { display: none; }
        </style>
    @endonce
    
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if($subcategories)
                @foreach(\App\Models\Category::all() as $cat)
                    @php
                        $isOpen = $cat->subCategories->contains(function ($subCategory){
                            return in_array($subCategory->id, $this->subcategories);
                        });
                    @endphp
                    <span class="badge text-bg-primary text-white">{{ $isOpen ? $cat->title : '' }}</span>
                    @foreach($cat->subCategories as $subCat)
                        <span class="badge text-bg-secondary">{{ in_array($subCat->id, $this->subcategories) ? $subCat->title : '' }}</span>
                    @endforeach
                @endforeach
            @endif

            @if($this->search)
                    <span class="badge text-bg-primary text-white">{{ __("common/sidebar.search_keyword") }}</span>
                    <span class="badge text-bg-secondary">{{ $this->search }}</span>
            @endif

            @if($workLocation)
                <span class="badge text-bg-primary text-white">{{ __("projects/form.locations") }} </span>
                @foreach(\App\Models\Location::all() as $lok)
                    <span class="badge text-bg-secondary">{{ in_array($lok->id, $workLocation) ? $lok->display_title : '' }}</span>
                @endforeach
            @endif

            @if($work_mode)
                    <span class="badge text-bg-primary text-white">{{ __("projects/form.work_mode") }} </span>
                @foreach(\App\Enums\WorkLocationEnum::cases() as $wmode)
                    <span class="badge text-bg-secondary">{{ in_array($wmode->value, $this->work_mode) ? __("common/sidebar.{$wmode->name}") : '' }}</span>
                @endforeach
            @endif
            @if($min_salary || $max_salary)
                <span class="badge text-bg-primary text-white">{{ __("common/sidebar.monthly_salary_range") }}</span>
                <span class="badge text-bg-secondary">{{ __("common/sidebar.min_salary") }}: {{ $this->min_salary ?? '' }}</span>
                <span class="badge text-bg-secondary">{{ __("common/sidebar.max_salary") }}: {{ $this->max_salary ?? '' }}</span>
            @endif

            @if($commercial_flow)
                <span class="badge text-bg-primary text-white">{{ __("projects/form.commercial_flow") }} </span>
                @foreach(\App\Enums\CommercialFlow::cases() as $flow)
                    <span class="badge text-bg-secondary">{{ in_array($flow->value, $this->commercial_flow) ? $flow->name : '' }}</span>
                @endforeach
            @endif

            @if($trade_classification)
                <span class="badge text-bg-primary text-white">{{ __("common/sidebar.project_flow") }} </span>
                @foreach(\App\Enums\TradeClassification::cases() as $trade)
                    <span class="badge text-bg-secondary">{{ in_array($trade->value, $this->trade_classification) ? $trade->name : '' }}</span>
                @endforeach
            @endif

            @if($contract_classification)
                <span class="badge text-bg-primary text-white">{{ __("projects/form.contract_type") }} </span>
                @foreach(\App\Enums\ContractClassificationEnum::cases() as $contract)
                    <span class="badge text-bg-secondary">{{ in_array($contract->value, $this->contract_classification) ? __("projects/form.{$contract->name}") : '' }}</span>
                @endforeach
            @endif

            @if($interview)
                <span class="badge text-bg-primary text-white">{{ __("projects/form.no_of_interviews") }} </span>
                @foreach(\App\Enums\InterviewEnum::cases() as $inter)
                    <span class="badge text-bg-secondary">{{ in_array($inter->value, $this->interview) ? __("common/sidebar.interview_{$inter->name}") : '' }}</span>
                @endforeach
            @endif
        </div>
    </div>
    @foreach($projects as $project)
        <x-ui.card-layout :item="$project">
            <x-slot:header>
                <div class="col-md-6 d-flex gap-2">
                    @if($project->created_at >= today()->subMonth())
                        <span class="job-status">{{ __("common/home.new") }}</span>
                    @endif
                    @if($project->deadline <= today()->addMonth())
                            <span class="job-status">{{ __("Urgent") }}</span>
                    @endif
                    <span class="job-location"><i class="fa-solid fa-location-dot"></i> {{ $project->locations->first()?->display_title ?? __("common/home.japan_tokyo") }}</span>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="job-budget"><i class="fa-solid fa-yen-sign"></i> {{ $project->salary_range }}</h4>
                </div>
             {{--
    Project actions (kebab / 3-dot menu)

    Shows a dropdown on the project card header with actions for the project.
    Currently: Edit -> route 'project.edit'.

    Visibility: only rendered if the user passes the 'update' policy on $project.
    Requires: Bootstrap 5 JS (dropdown) and Font Awesome 6 (ellipsis icon).
    Styling: .project-actions / .project-actions-toggle in the app stylesheet.
--}}
@can('update', $project)
    <div class="dropdown project-actions">
        <button type="button"
                class="btn btn-sm project-actions-toggle"
                id="projectActions{{ $project->id }}"
                data-bs-toggle="dropdown"
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
            </x-slot:header>
            <!--- Card Body content ---->
            <div class="col-md-8 mb-4 ps-md-3">
                <h3 class="job-title">{{ $project->title ?? '' }}</h3>
            </div>
            <div class="col-md-4 mb-4 text-end">
                <h6 class="">{{ __('projects/form.remote_operation_possible') }}:
                    <span>{!! $project->remote_operation_possible ? '<i class="fa-regular fa-circle-check text-success"></i>' : '<i class="fa-regular fa-circle-xmark text-danger"></i>' !!}</span>
                </h6>
            </div>
            <div class="col-md-8">
                <p class="job-overview">
                    {!! \Illuminate\Support\Str::limit($project->project_description, 200) !!}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-grid d-md-block gap-2">
                    <a href="{{ route("project.show", $project) }}" class="btn btn-sm btn-primary">{{ __("common/index.apply_now") }}</a>
{{--                    <a href="{{ route("project.save-for-later", $project) }}"--}}
{{--                       onclick="event.preventDefault(); document.getElementById('save-for-later').submit();"--}}
{{--                       class="btn btn-sm btn-secondary" @if($project->saves->contains($project->id)) disabled @endif>{{ __("common/index.apply_later") }}</a>--}}
                    <form action="{{ route('project.save-for-later', $project) }}" method="post" id="save-for-later" class="d-inline">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <button type="submit" class="btn btn-sm btn-secondary" @if($project->saves?->contains($project->id)) disabled @endif>{{ __("common/index.apply_later") }}</button>
                    </form>
                </div>
            </div>
            <div class="col-md-12 job-pills">
                <div class="row align-items-center">
                    <div class="col-md-6 col-lg text-center">
                        <div class="job-pill">{{ __('common/home.apply') }}: <span>{{ today()->format('d/m/y') }}</span></div>
                    </div>
                    <div class="col-md-6 col-lg text-center">
                        <div class="job-pill">{{ __("projects/form.remote_operation_possible") }}:
                            <span>{!! $project->remote_operation_possible ? '<i class="fa-regular fa-circle-check text-success"></i>' : '<i class="fa-regular fa-circle-xmark text-danger"></i>' !!}</span>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg text-center">
                        <div class="job-pill">{{ __("common/home.no_of_interviews") }}: <span>{{ \App\Enums\InterviewEnum::toName($project->number_of_interviewers) ?? __('5') }}</span></div>
                    </div>
                    <div class="col-md-6 col-lg text-center">
                        <div class="job-pill">{{ __("common/home.eligibility") }}: <span>{{ __('B.Tech') }}</span></div>
                    </div>
                </div>
            </div>
            <!--- Card Body content ---->
        </x-ui.card-layout>
    @endforeach
    <div class="paginator">
        {{ $projects->links() }}
    </div>
</div>
