<div>
    @foreach($projects as $project)
        <x-ui.card-layout :item="$project">
            <x-slot:header>
                <div class="col-md-6 d-flex gap-2">
                    @if($project->created_at >= today()->subDays(7))
                        <span class="job-status">{{ __("common/home.new") }}</span>
                    @endif
                    <span class="job-location"><i class="fa-solid fa-location-dot"></i> {{ $project->locations->first()->title ?? __("common/home.japan_tokyo") }}</span>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="job-budget"><i class="fa-solid fa-yen-sign"></i> {{ $project->salary_range }}</h4>
                </div>
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
                    <a href="" class="btn btn-sm btn-secondary">{{ __("common/index.apply_later") }}</a>
                </div>
            </div>
            <div class="col-md-12 job-pills">
                <div class="row align-items-center">
                    <div class="col-md text-center">
                        <div class="job-pill">{{ __('common/home.apply') }}: <span>{{ today()->format('d/m/y') }}</span></div>
                    </div>
                    <div class="col-md text-center">
                        <div class="job-pill">{{ __("projects/form.remote_operation_possible") }}:
                            <span>{!! $project->remote_operation_possible ? '<i class="fa-regular fa-circle-check text-success"></i>' : '<i class="fa-regular fa-circle-xmark text-danger"></i>' !!}</span>
                        </div>
                    </div>
                    <div class="col-md text-center">
                        <div class="job-pill">{{ __("common/home.no_of_interviews") }}: <span>{{ \App\Enums\InterviewEnum::toName($project->number_of_interviewers) ?? __('5') }}</span></div>
                    </div>
                    <div class="col-md text-center">
                        <div class="job-pill">{{ __("common/home.eligibility") }}: <span>{{ __('B.Tech') }}</span></div>
                    </div>
                </div>
            </div>
            <!--- Card Body content ---->
        </x-ui.card-layout>
{{--        <div class="job-list mt-4">--}}
{{--            <a href="" class="add-to-favourite">--}}
{{--                <i class="fa-solid fa-star"></i>--}}
{{--            </a>--}}
{{--            <div class="job-content">--}}
{{--                <div class="row justify-content-between">--}}
{{--                    <div class="col-md-6 d-flex gap-2">--}}
{{--                        <span class="job-status">{{ __("common/home.new") }}</span>--}}
{{--                        <span class="job-location"><i class="fa-solid fa-location-dot"></i> {{ __("common/home.japan_tokyo") }}</span>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-6 text-end">--}}
{{--                        <a href="" class="add-to-wishlist"><i class="fa-solid fa-heart"></i> 54</a>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-12 text-end">--}}
{{--                        <h4 class="job-budget"><i class="fa-solid fa-yen-sign"></i> {{ $project->minimum_price }}-{{ $project->maximum_price }}/{{ __("common/home.month") }}</h4>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row justify-content-between">--}}
{{--                    <div class="col-md-12">--}}
{{--                        <h3 class="job-title">{{ $project->title }}</h3>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-12 text-end">--}}
{{--                        <h6 class="job-paid-type">{{ __("common/home.fixed_price") }}</h6>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-12 d-flex justify-content-between">--}}
{{--                        <div class="row">--}}
{{--                            <div class="col-md-6 mb-2 job-listing-date"><i class="fa-solid fa-calendar-days"></i>--}}
{{--                                {{ __("common/home.registered_on") }}: {{ $project->created_at->format('M d, Y') }}</div>--}}
{{--                            <div class="col-md-6 mb-2 job-updated"><i class="fa-solid fa-rotate"></i> {{ __("common/home.updated_on") }}: {{ $project->updated_at->format('M d, Y') }}</div>--}}
{{--                            <div class="col job-duration"><i class="fa-regular fa-hourglass-half"></i> {{ __("common/home.duration") }}: {{ $project->deadline->format('M d, Y') }}</div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-12 text-end">--}}
{{--                        <a href="{{ route("project.show", $project) ?? __("/project/{$project->slug}") }}" class="btn job-btn-summary">{{ __("common/home.summary") }}</a>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-10">--}}
{{--                        <p class="job-overview">--}}
{{--                            {!! \Illuminate\Support\Str::limit($project->project_description, 200) !!}--}}
{{--                        </p>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-12 job-pills">--}}
{{--                        <div class="row align-items-center">--}}
{{--                            <div class="col-md-3 text-center">--}}
{{--                                <div class="job-pill">{{ __('common/home.apply') }}: <span>{{ today()->format('d/m/y') }}</span></div>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-3 text-center">--}}
{{--                                <div class="job-pill">{{ __("common/home.contract_price") }}: <span>{{ __("common/home.fixed") }}</span></div>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-3 text-center">--}}
{{--                                <div class="job-pill">{{ __("common/home.no_of_interviews") }}: <span>{{ $project->number_of_interviewers ?? __('5') }}</span></div>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-3 text-center">--}}
{{--                                <div class="job-pill">{{ __("common/home.eligibility") }}: <span>{{ __('B.Tech') }}</span></div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    @endforeach
    <div class="paginator">
        {{ $projects->links() }}
    </div>
</div>
