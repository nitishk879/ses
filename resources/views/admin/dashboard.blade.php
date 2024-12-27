<x-dashboard>
    <div class="container-fluid p-0">
        <!---- Let's add some breadcrumb -->
{{--        <nav aria-label="breadcrumb">--}}
{{--            <ol class="breadcrumb">--}}
{{--                <li class="breadcrumb-item active"><a href="{{ route("dashboard") }}" aria-current="page">{{ __("admin/dashboard.dashboard") }}</a></li>--}}
{{--                <li class="breadcrumb-item"><a href="#">Library</a></li>--}}
{{--                <li class="breadcrumb-item active" >Data</li>--}}
{{--            </ol>--}}
{{--        </nav>--}}
        <h1 class="h3 my-3"><strong>{{ __("Analytics") }}</strong> {{ __("Dashboard") }}</h1>
        <!---- Let's add some breadcrumb -->
        <div class="row justify-content-center mb-3">
            <div class="col-lg-4 mb-2">
                <div class="d-flex align-items-center dash-card">
                    <div class="flex-shrink-0">
                        <svg class="dash-card-icon primary" width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.5523 11.8778H22.7961C24.2799 11.8778 25.4871 10.6706 25.4871 9.18777V8.08204C25.4871 8.07866 25.4852 8.07588 25.4852 8.07249C25.4852 8.06911 25.4871 8.06632 25.4871 8.06294V7.58401C25.4871 6.91175 24.9406 6.36523 24.2683 6.36523H23.2865H16.0619H15.0811C14.4088 6.36523 13.8613 6.91175 13.8613 7.58401V8.05242C13.8616 8.06088 13.8662 8.0679 13.8662 8.07636C13.8662 8.08482 13.8616 8.09184 13.8613 8.1003V9.18777C13.8613 10.6706 15.0685 11.8778 16.5523 11.8778Z" fill="#0080FF"/>
                            <path d="M26.8446 38.6958H8.43882C8.24149 38.6958 8.0519 38.6175 7.91261 38.4772C7.77236 38.337 7.69497 38.1464 7.69594 37.9491C7.72472 32.5429 11.3277 27.8999 16.4181 26.4838C14.9462 25.4525 13.9794 23.7457 13.9794 21.8148C13.9794 18.676 16.5331 16.1223 19.6709 16.1223C22.8127 16.1223 25.3692 18.676 25.3692 21.8148C25.3692 23.7467 24.4003 25.4547 22.9252 26.4856C25.6082 27.233 27.9321 28.8968 29.5217 31.1994C31.1066 29.6017 33.2005 28.6406 35.4589 28.4583V11.9978C35.4589 10.2451 34.0322 8.81836 32.2795 8.81836H26.9729V9.19076C26.9729 11.4929 25.0993 13.3665 22.7962 13.3665H16.5524C14.2493 13.3665 12.3757 11.4929 12.3757 9.19076V8.82223H7.07204C5.31932 8.82223 3.89258 10.249 3.89258 12.0017V42.5592C3.89258 44.3119 5.31932 45.7386 7.07204 45.7386H31.1506C28.7445 44.1927 27.1138 41.5949 26.8446 38.6958Z" fill="#0080FF"/>
                            <path d="M20.5594 27.5481C20.2632 27.5221 19.9665 27.5081 19.6707 27.5081C19.6693 27.5081 19.6677 27.5078 19.6663 27.5078C19.3715 27.5078 19.0781 27.5226 18.7871 27.548L19.4209 28.9967C19.4761 29.1244 19.4954 29.2656 19.4751 29.403L18.8038 33.9521L19.6714 34.8507L20.5439 33.9521L19.8639 29.4049C19.8436 29.2656 19.863 29.1234 19.92 28.9948L20.5594 27.5481Z" fill="#0080FF"/>
                            <path d="M19.6708 2.26562C18.1744 2.26562 16.9411 3.41766 16.8125 4.88116H22.5282C22.3995 3.41766 21.1672 2.26562 19.6708 2.26562Z" fill="#0080FF"/>
                            <path d="M36.2061 29.8965C33.7686 29.9052 31.509 30.9915 30.001 32.8767C29.7186 33.2326 29.4695 33.6065 29.2545 33.9951C28.8441 34.7373 28.5678 35.5353 28.414 36.3642C28.3257 36.8406 28.2715 37.3257 28.2715 37.8205C28.2879 37.8804 28.2812 37.8775 28.2821 37.9395C28.3257 41.4062 30.6936 44.4889 34.0404 45.4359C34.7281 45.6342 35.4555 45.7348 36.2061 45.7348H36.3348C40.6227 45.671 44.1195 42.121 44.1195 37.8205C44.1195 33.4513 40.5695 29.8965 36.2061 29.8965ZM39.7009 36.6771L35.8966 40.0549C35.7554 40.1797 35.5793 40.2426 35.4033 40.2426C35.2272 40.2426 35.0512 40.1807 34.91 40.0559L32.69 38.0865C32.3834 37.8137 32.3554 37.3446 32.6272 37.037C32.8999 36.7284 33.3681 36.7033 33.6767 36.9741L35.4033 38.5063L38.7143 35.5667C39.0199 35.2949 39.492 35.322 39.7628 35.6286C40.0356 35.9352 40.0075 36.4053 39.7009 36.6771Z" fill="#0080FF"/>
                            <path d="M23.8773 21.8122C23.8773 19.4926 21.9872 17.6055 19.6647 17.6055C17.3462 17.6055 15.459 19.4926 15.459 21.8122C15.459 24.1346 17.3462 26.0247 19.6647 26.0247C21.9872 26.0247 23.8773 24.1346 23.8773 21.8122Z" fill="#0080FF"/>
                            <path d="M9.21094 37.211H26.8107C26.8121 37.1886 26.818 37.167 26.8196 37.1447C26.8465 36.7656 26.8982 36.392 26.9695 36.0231C26.9842 35.9474 27.001 35.8727 27.0175 35.7976C27.0997 35.423 27.198 35.053 27.3255 34.6924C27.3337 34.669 27.3444 34.6467 27.3529 34.6233C27.4781 34.278 27.628 33.9414 27.7941 33.6114C27.8261 33.5477 27.8559 33.4835 27.8894 33.4205C28.0659 33.0888 28.2636 32.7667 28.4811 32.4539C28.4996 32.4273 28.5127 32.3982 28.5315 32.3717C27.0561 30.0505 24.7409 28.4281 22.0694 27.8098L21.3649 29.4041L22.0681 34.1012C22.1019 34.3305 22.0275 34.5626 21.8659 34.729L20.2022 36.4421C20.0619 36.5862 19.8704 36.6674 19.6692 36.6674H19.6683C19.4671 36.6674 19.2746 36.5852 19.1353 36.4401L17.4812 34.7271C17.3207 34.5617 17.2462 34.3314 17.28 34.1032L17.9736 29.4031L17.274 27.8047C12.8148 28.8351 9.55517 32.6443 9.21094 37.211Z" fill="#0080FF"/>
                        </svg>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h3 class="dash-card-title">{{ \App\Models\Talent::where('created_at', '>=', today()->subDays(7))->count() }}</h3>
                            <p class="dash-card-content">{{ __("admin/dashboard.new_candidates_to_review") }} <a href=""><i class="fa-solid fa-chevron-right"></i></a> </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="d-flex align-items-center dash-card">
                    <div class="flex-shrink-0">
                        <svg class="dash-card-icon indigo" width="37" height="37" viewBox="0 0 37 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_3180_26897)">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.62891 0C2.0746 0 0.00390625 2.07797 0.00390625 4.64126V26.3005C0.00390625 28.8639 2.0746 30.9417 4.62891 30.9417H8.9241C9.27999 30.9417 9.62491 31.0654 9.90034 31.2915L15.5752 35.9509C17.2789 37.3496 19.7289 37.3496 21.4326 35.9509L27.1075 31.2915C27.383 31.0654 27.7278 30.9417 28.0837 30.9417H32.3789C34.9333 30.9417 37.0039 28.8639 37.0039 26.3005V4.64126C37.0039 2.07797 34.9333 0 32.3789 0L4.62891 0ZM7.71224 12.3767C7.71224 11.5223 8.40247 10.8296 9.25391 10.8296H15.4206C16.272 10.8296 16.9622 11.5223 16.9622 12.3767C16.9622 13.2312 16.272 13.9238 15.4206 13.9238H9.25391C8.40247 13.9238 7.71224 13.2312 7.71224 12.3767ZM9.25391 17.018C8.40247 17.018 7.71224 17.7107 7.71224 18.5651C7.71224 19.4194 8.40247 20.1121 9.25391 20.1121H27.7539C28.6054 20.1121 29.2956 19.4194 29.2956 18.5651C29.2956 17.7107 28.6054 17.018 27.7539 17.018H9.25391Z" fill="#7B61FF"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_3180_26897">
                                    <rect width="37" height="37" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h3 class="dash-card-title">24</h3>
                            <p class="dash-card-content">{{ __("admin/dashboard.message_received") }} <a href=""><i class="fa-solid fa-chevron-right"></i></a> </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="d-flex align-items-center dash-card">
                    <div class="flex-shrink-0">
                        <svg class="dash-card-icon lime" width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_3180_26902)">
                                <path d="M9.62455 19.25C13.0021 19.25 15.7496 16.5025 15.7496 13.125C15.7496 9.7475 13.0021 7 9.62455 7C6.24705 7 3.49955 9.7475 3.49955 13.125C3.49955 16.5025 6.24705 19.25 9.62455 19.25ZM27.1245 12.25C30.5021 12.25 33.2495 9.5025 33.2495 6.125C33.2495 2.7475 30.5021 0 27.1245 0C23.7471 0 20.9995 2.7475 20.9995 6.125C20.9995 9.5025 23.7471 12.25 27.1245 12.25ZM31.4995 21C25.7001 21 20.9995 25.7005 20.9995 31.5C20.9995 37.2995 25.7001 42 31.4995 42C37.299 42 41.9995 37.2995 41.9995 31.5C41.9995 25.7005 37.299 21 31.4995 21ZM37.9448 30.996L33.2146 35.5705C32.4235 36.351 31.3736 36.7448 30.3218 36.7448C29.2701 36.7448 28.2166 36.351 27.4116 35.5653L25.0421 33.2342C24.3508 32.557 24.3421 31.4492 25.0175 30.7598C25.6931 30.0685 26.8008 30.058 27.4921 30.7352L29.8615 33.0645C30.1136 33.313 30.5266 33.3078 30.7751 33.061L35.5228 28.469C36.2176 27.8022 37.327 27.8215 37.9973 28.5198C38.6658 29.2163 38.6431 30.324 37.9466 30.9943L37.9448 30.996ZM18.378 26.6105C17.8111 28.133 17.4995 29.7798 17.4995 31.5C17.4838 31.5 17.4681 31.5 17.4523 31.5H1.7968C1.2998 31.5 0.823801 31.2882 0.493051 30.9172C0.162301 30.5462 0.00305142 30.0527 0.0573014 29.5575C0.598051 24.6802 4.71055 21 9.62455 21C13.4851 21 16.8503 23.2715 18.378 26.6105ZM20.3451 23.0405C19.6696 22.0868 18.8716 21.2275 17.9738 20.489C19.281 16.6758 22.8878 14 27.0773 14C30.2343 14 33.0588 15.519 34.8211 17.8973C33.7571 17.6383 32.6441 17.5 31.5013 17.5C26.9478 17.5 22.9018 19.6735 20.3451 23.0405Z" fill="#00B706"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_3180_26902">
                                    <rect width="42" height="42" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h3 class="dash-card-title">16</h3>
                            <p class="dash-card-content">{{ __("admin/dashboard.profile_matching", ["profile" => '>90%']) }} <a href=""><i class="fa-solid fa-chevron-right"></i></a> </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mb-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-md-flex justify-content-between w-100">
                            <div class="d-flex flex-column">
                                <h3 class="chart-title">{{ __("admin/dashboard.job_statistics") }}</h3>
                                <h5 class="chart-sub-title">{{ __("admin/dashboard.showing_job_statistics") }}</h5>
                            </div>
                            <ul class="nav nav-tabs" id="chartTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="week-tab" data-bs-toggle="tab" data-bs-target="#week" type="button" role="tab" aria-controls="week" aria-selected="true" onclick="loadChartData('week')">
                                        {{ __("admin/dashboard.week") }}</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="month-tab" data-bs-toggle="tab" data-bs-target="#month" type="button" role="tab" aria-controls="month" aria-selected="false" onclick="loadChartData('month')">{{ __("admin/dashboard.month") }}</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="year-tab" data-bs-toggle="tab" data-bs-target="#year" type="button" role="tab" aria-controls="year" aria-selected="false" onclick="loadChartData('year')">{{ __("admin/dashboard.year") }}</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content mt-4">
                            <!-- Chart Container -->
                            <canvas id="chartCanvas" style="height: 232px!important;"></canvas>
                        </div>
{{--                        <div class="align-self-center">--}}
{{--                            <h4>My project chart</h4>--}}
{{--                            <x-admin.project-chart />--}}
{{--                        </div>--}}
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="">{{ __("admin/dashboard.job_openings") }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="counts">12 <p>{{ __("admin/dashboard.job_opened") }}</p></div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="">{{ __("admin/dashboard.application_summary") }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="counts">{{ \App\Models\Talent::count() }} <p>{{ __("admin/dashboard.applicants") }}</p></div>
                    </div>
                </div>
            </div>
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
