<x-dashboard>
    <div class="container-fluid p-0">
        <h1 class="h3 my-3"><strong>Members</strong> Member profile</h1>
        <!---- Let's add some breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="{{ route("dashboard") }}" aria-current="page">{{ __("admin/dashboard.dashboard") }}</a></li>
                <li class="breadcrumb-item active"><a href="#">Company Profile</a></li>
                {{--                <li class="breadcrumb-item active">Data</li>--}}
            </ol>
        </nav>
        <!---- Let's add some breadcrumb -->
        <div class="row justify-content-center mb-3">
            <div class="col-md-4 col-xl-3">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Profile Details</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ Auth::user()->company->company_logo_url }}" alt="" class="img-fluid rounded-circle mb-2" width="128">
                        <h5 class="card-title mb-0">{{ Auth::user()->company->company_name ?? Auth::user()->name }}</h5>
                        <div class="text-muted mb-2">{{ Auth::user()->company->company->industry ?? Auth::user()->country }}</div>

{{--                        <div>--}}
{{--                            <a class="btn btn-primary btn-sm" href="#">Follow</a>--}}
{{--                            <a class="btn btn-primary btn-sm" href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg> Message</a>--}}
{{--                        </div>--}}
                    </div>
                    <hr class="my-0">
                    <div class="card-body">
                        @if(Auth::user()->company)
                        <h5 class="h6 card-title">Skills</h5>
                            <a href="#" class="badge bg-primary me-1 my-1">{{ Auth::user()->company->area_of_expertise }}</a>
                        @else
                            <a href="#" class="badge bg-primary me-1 my-1">{{ Auth::user()->company->area_of_expertise }}</a>
                        @endif
                    </div>
                    <hr class="my-0">
                    <div class="card-body">
                        <h5 class="h6 card-title">About</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home feather-sm me-1"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> Location <a href="#">{{ Auth::user()->company->company_location ?? __("") }}</a></li>
                            <li class="mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-briefcase feather-sm me-1"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg> Established in <a href="#">{{ Auth::user()->company->established ?? '' }}</a></li>
                            <li class="mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin feather-sm me-1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> Dispatch Business License <a href="#">{{ Auth::user()->company->dispatch_business_license ?? '' }}</a></li>
                        </ul>
                    </div>
                    <hr class="my-0">
                    <div class="card-body">
                        <h5 class="h6 card-title">Elsewhere</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1"><a href="#">staciehall.co</a></li>
                            <li class="mb-1"><a href="#">Twitter</a></li>
                            <li class="mb-1"><a href="#">Facebook</a></li>
                            <li class="mb-1"><a href="#">Instagram</a></li>
                            <li class="mb-1"><a href="#">LinkedIn</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-xl-9">
                <div class="card">
                    <div class="card-header">

                        <h5 class="card-title mb-0">Activities</h5>
                    </div>
                    <div class="card-body h-100">
                        @foreach(Auth::user()->company->projects as $project)
                            <div class="d-flex align-items-start">
                                <img src="{{ __("https://picsum.photos/32/32?random={$project->id}") }}" width="36" height="36" class="rounded-circle me-2" alt="Vanessa Tucker">
                                <div class="flex-grow-1">
                                    <small class="float-end text-navy">{{ $project->created_at->diffForHumans() }} </small>
                                    <strong>{{ $project->title }}</strong> will start on {{ $project->contract_start_date->format('M d, Y') }} <strong>Christina Mason</strong><br>
                                    <small class="text-muted">Published {{ $project->created_at->diffForHumans() }}</small><br>

                                </div>
                            </div>
                        @endforeach
                        <hr>
                        @foreach(Auth::user()->company->talents as $talent)
                                <div class="d-flex align-items-start">
                                    <img src="{{ __("https://picsum.photos/36/36?random={$talent->id}") }}" width="36" height="36" class="rounded-circle me-2" alt="Charles Hall">
                                    <div class="flex-grow-1">
                                        <small class="float-end text-navy">30m ago</small>
                                        <strong>{{ $talent->user->name }}</strong> posted something on <strong>Christina Mason</strong>'s timeline<br>
                                        <small class="text-muted">Today 7:21 pm</small>
                                        <div class="border text-sm text-muted p-2 mt-1">
                                            ---
                                        </div>
                                        <a href="#" class="btn btn-sm btn-danger mt-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-heart feather-sm"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg> Like</a>
                                    </div>
                                </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mb-3">

        </div>
    </div>

    @push('scripts')
        <script>
            @php
                $projects = \App\Models\Project::selectRaw('COUNT(id) as count, MONTH(created_at) as month')
                ->groupBy('month')
                ->pluck('count', 'month');

                $labels = $projects->keys()->map(function($month) {
                    return date('F', mktime(0, 0, 0, $month, 1));
                });
            @endphp
            document.addEventListener("DOMContentLoaded", function() {
                // Bar chart
                new Chart(document.getElementById("chartjs-dashboard-bar"), {
                    type: "bar",
                    data: {
                        labels: @json($labels),
                        datasets: [{
                            label: "Project published",
                            backgroundColor: window.theme.primary,
                            borderColor: window.theme.primary,
                            hoverBackgroundColor: window.theme.primary,
                            hoverBorderColor: window.theme.primary,
                            data: @json($projects->values()),
                            barPercentage: .75,
                            categoryPercentage: .5
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                gridLines: {
                                    display: false
                                },
                                stacked: false,
                                ticks: {
                                    stepSize: 5
                                }
                            }],
                            xAxes: [{
                                stacked: false,
                                gridLines: {
                                    color: "transparent"
                                }
                            }]
                        }
                    }
                });
            });
        </script>
        <script>
            let chart; // Chart instance
            // Function to initialize or update the chart
            function loadChartData(period) {
                axios.get(`/project-chart/${period}`)
                    .then(function (response) {
                        const chartData = response.data;
                        updateChart(chartData.labels, chartData.data);
                    })
                    .catch(function (error) {
                        console.error('Error fetching chart data:', error);
                    });
            }

            // Function to render or update the chart
            function updateChart(labels, data) {
                const ctx = document.getElementById('chartCanvas').getContext('2d');

                if (chart) {
                    // Update existing chart
                    chart.data.labels = labels;
                    chart.data.datasets[0].data = data;
                    chart.update();
                } else {
                    // Create new chart
                    chart = new Chart(ctx, {
                        type: 'bar', // You can change this to 'line', 'pie', etc.
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Project published',
                                data: data,
                                backgroundColor: 'rgba(0,128,255,0.34)',
                                borderColor: 'rgb(0,128,255)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }

            // Load week data by default when the page loads
            document.addEventListener('DOMContentLoaded', function () {
                loadChartData('week');
            });
        </script>
    @endpush

</x-dashboard>
