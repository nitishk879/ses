<x-dashboard>
    <div class="container-fluid p-0" style="min-height: 85vh;">
        <!---- Let's add some breadcrumb -->
        <div class="row justify-content-between">
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active"><a href="{{ route("dashboard") }}" aria-current="page">{{ __("admin/dashboard.dashboard") }}</a></li>
                        <li class="breadcrumb-item active"><a href="#">{{ __("Projects") }}</a></li>
                        {{--                <li class="breadcrumb-item active">Data</li>--}}
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 text-end"><a href="{{ route("project.create") }}" class="btn btn-sm btn-primary">{{ __("Add New") }}</a> </div>
        </div>
        <!---- Let's add some breadcrumb -->
        @php
            $projects = Auth::user()->company->projects;
        @endphp
        @if($projects->count() >=1)
            <div class="row justify-content-center mb-3">
                @foreach($projects as $project)
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card">
                            <div class="card-header px-4 pt-4">
                                <div class="card-actions float-end">
                                    <div class="dropdown position-relative">
                                        <a href="#" data-bs-toggle="dropdown" data-bs-display="static">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal align-middle"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <h5 class="card-title mb-0">{{ $project->title }}</h5>
                                <div class="badge bg-success my-2">{{ $project->project_status }}</div>
                            </div>
                            <div class="card-body px-4 pt-2">
                                <p>{!! \Illuminate\Support\Str::limit($project->project_description, 200) !!}</p>
                                @if($project->interview_schedules)
                                    @foreach($project->interview_schedules as $schedule)
                                        <img src="img/avatars/avatar-3.jpg" class="rounded-circle me-1" alt="{{ $schedule }}" width="28" height="28">
                                    @endforeach
                                @endif
                                <img src="img/avatars/avatar-2.jpg" class="rounded-circle me-1" alt="Avatar" width="28" height="28">
                                <img src="img/avatars/avatar.jpg" class="rounded-circle me-1" alt="Avatar" width="28" height="28">
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-4 pb-4">
                                    <p class="mb-2 font-weight-bold">Progress <span class="float-end">{{ $project->percentage }}%</span></p>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-{{$project->progress}}" role="progressbar" title="{{ $project->project_status }}" aria-valuenow="{{ $project->percentage }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $project->percentage }}%;"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            @php($saved_projects = Auth::user()->savedProjects)
            @if($saved_projects)
                <div class="row justify-content-center my-3">
                    <div class="col-md-12">{{ __("My saved projects") }}</div>
                    @foreach($saved_projects as $project)
                        <div class="col-12 col-md-6 col-lg-3 my-3">
                            <div class="card">
                                <div class="card-header px-4 pt-4">
                                    <div class="card-actions float-end">
                                        <div class="dropdown position-relative">
                                            <a href="#" data-bs-toggle="dropdown" data-bs-display="static">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal align-middle"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#">Action</a>
                                                <a class="dropdown-item" href="#">Another action</a>
                                                <a class="dropdown-item" href="#">Something else here</a>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="card-title mb-0">{{ $project->title }}</h5>
                                    <div class="badge bg-success my-2">{{ $project->project_status }}</div>
                                </div>
                                <div class="card-body px-4 pt-2">
                                    <p>{!! \Illuminate\Support\Str::limit($project->project_description, 200) !!}</p>
                                    @if($project->interview_schedules)
                                        @foreach($project->interview_schedules as $schedule)
                                            <img src="img/avatars/avatar-3.jpg" class="rounded-circle me-1" alt="{{ $schedule }}" width="28" height="28">
                                        @endforeach
                                    @endif
                                    <img src="img/avatars/avatar-2.jpg" class="rounded-circle me-1" alt="Avatar" width="28" height="28">
                                    <img src="img/avatars/avatar.jpg" class="rounded-circle me-1" alt="Avatar" width="28" height="28">
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item px-4 pb-4">
                                        <p class="mb-2 font-weight-bold">Progress <span class="float-end">{{ $project->percentage }}%</span></p>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-{{$project->progress}}" role="progressbar" title="{{ $project->project_status }}" aria-valuenow="{{ $project->percentage }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $project->percentage }}%;"></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="row justify-content-center mb-3">
                <div class="col-md-6"><a href="{{ route("project.create") }}" class="btn btn-primary">Add New Project</a> </div>
            </div>
        @endif
    </div>

    @push('scripts')

    @endpush

</x-dashboard>
