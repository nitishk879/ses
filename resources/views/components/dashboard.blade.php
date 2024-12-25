<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords" content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <link rel="preconnect" href="https://fonts.gstatic.com">

    <link rel="shortcut icon" href="{{ asset("images/logo.png") }}" />

    <link rel="canonical" href="{{ config("app.url") }}" />

    <title>{{ $title ?? config('app.name') }}</title>

{{--    <link href="{{ asset("build/assets/dashboard-OdKPV8nK.css") }}" rel="stylesheet">--}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    @vite(['resources/sass/dashboard.scss'])
    <!---- Font Awesome ---->
    <script src="https://kit.fontawesome.com/8c6f840b1c.js" crossorigin="anonymous"></script>
</head>

<body>
<div class="wrapper">
    <nav id="sidebar" class="sidebar js-sidebar">
        <div class="sidebar-content js-simplebar">
            <a class="sidebar-brand" href="/">
                <span class="align-middle">
                    <img src="{{ asset("images/logo-dark.png") }}" alt="{{ config("app.name") }}" height="52"/>
                </span>
            </a>

            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-heading">
                    Pages
                </li>

                <li class="nav-item active">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? "active": "" }}" href="{{ route("dashboard") }}">
                        <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">{{ __("admin/sidebar.dashboard") }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="{{ route("messages") }}">
                        <i class="fa-regular fa-message align-middle"></i> <span class="align-middle">{{ __("admin/sidebar.messages") }}</span> <span class="badge bg-primary rounded-5 ms-auto">3</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="{{ route("company-profile") }}">
                        <i class="fa-regular fa-building align-middle"></i> <span class="align-middle">{{ __("admin/sidebar.company_profile") }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="{{ route("job-applicants") }}">
                        <i class="fa-solid fa-users-between-lines align-middle"></i> <span class="align-middle">{{ __("admin/sidebar.all_applicants") }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="{{ route("job-listing") }}">
                        <i class="fa-regular fa-rectangle-list align-middle"></i> <span class="align-middle">{{ __("admin/sidebar.job_listings") }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link " href="">
                        <i class="fa-solid fa-calendar-days align-middle"></i> <span class="align-middle">{{ __("admin/sidebar.my_schedule") }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="sidebar-toggle js-sidebar-toggle">
                <i class="hamburger align-self-center"></i>
            </a>
            @if(Auth::user()->company)
                <a href="" class="d-flex align-items-center gap-2 px-2 company-logo">
                    <img src="{{ Auth::user()->company->company_logo_url ?? asset("images/logo-dark.png") }}" alt="" height="42" />
                    <strong>{{ Auth::user()->company->company_name ?? config('app.name') }}</strong>
                </a>
            @endif
            <div class="navbar-collapse collapse">
                <ul class="navbar-nav navbar-align ms-auto mb-2 pe-2">
                    @php($unread = Auth::user()->notifications)
                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
                            <div class="position-relative">
                                <i class="fa-regular fa-bell align-middle"></i>
                                <span class="indicator">{{ Auth::user()->unreadNotifications->count() }}</span>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
                            <div class="dropdown-menu-header">
                                {{ Auth::user()->unreadNotifications->count() }} New Notifications
                            </div>
                            <div class="list-group">
                                @foreach(Auth::user()->unreadNotifications as $notify)
                                    <a href="{{ $notify->data['url'] }}" class="list-group-item">
                                        <div class="row g-0 align-items-center">
                                            <div class="col-2">
                                                <i class="text-danger" data-feather="alert-circle"></i>
                                            </div>
                                            <div class="col-10">
                                                <div class="text-dark">{{ $notify->data['title'] ?? '' }}</div>
                                                <div class="text-muted small mt-1">{{ $notify->data['message'] }}</div>
                                                <div class="text-muted small mt-1">{{ $notify->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div class="dropdown-menu-footer">
                                <a href="#" class="text-muted">Show all notifications</a>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle" href="#" id="messagesDropdown" data-bs-toggle="dropdown">
                            <div class="position-relative">
                                <i class="align-middle" data-feather="message-square"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="messagesDropdown">
                            <div class="dropdown-menu-header">
                                <div class="position-relative">
                                    4 New Messages
                                </div>
                            </div>
                            <div class="list-group">
                                <a href="#" class="list-group-item">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-2">
                                            <img src="https://picsum.photos/32/32?random=5" class="avatar img-fluid rounded-circle" alt="Vanessa Tucker">
                                        </div>
                                        <div class="col-10 ps-2">
                                            <div class="text-dark">Vanessa Tucker</div>
                                            <div class="text-muted small mt-1">Nam pretium turpis et arcu. Duis arcu tortor.</div>
                                            <div class="text-muted small mt-1">15m ago</div>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="list-group-item">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-2">
                                            <img src="https://picsum.photos/32/32?random=2" class="avatar img-fluid rounded-circle" alt="William Harris">
                                        </div>
                                        <div class="col-10 ps-2">
                                            <div class="text-dark">William Harris</div>
                                            <div class="text-muted small mt-1">Curabitur ligula sapien euismod vitae.</div>
                                            <div class="text-muted small mt-1">2h ago</div>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="list-group-item">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-2">
                                            <img src="https://picsum.photos/32/32?random=4" class="avatar img-fluid rounded-circle" alt="Christina Mason">
                                        </div>
                                        <div class="col-10 ps-2">
                                            <div class="text-dark">Christina Mason</div>
                                            <div class="text-muted small mt-1">Pellentesque auctor neque nec urna.</div>
                                            <div class="text-muted small mt-1">4h ago</div>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="list-group-item">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-2">
                                            <img src="https://picsum.photos/32/32?random=3" class="avatar img-fluid rounded-circle" alt="Sharon Lessman">
                                        </div>
                                        <div class="col-10 ps-2">
                                            <div class="text-dark">Sharon Lessman</div>
                                            <div class="text-muted small mt-1">Aenean tellus metus, bibendum sed, posuere ac, mattis non.</div>
                                            <div class="text-muted small mt-1">5h ago</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="dropdown-menu-footer">
                                <a href="#" class="text-muted">Show all messages</a>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                            <i class="align-middle" data-feather="settings"></i>
                        </a>

                        <a class="nav-link dropdown-toggle d-none d-sm-inline-block text-decoration-none" href="#" data-bs-toggle="dropdown">
                            <img src="{{ __("https://picsum.photos/32/32?random=11")  }}" class="avatar img-fluid rounded me-1" alt="Charles Hall" /> <span class="text-dark">{{ Auth::user()->name }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href=""><i class="align-middle me-1" data-feather="user"></i> Profile</a>
                            <a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="pie-chart"></i> Analytics</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href=""><i class="align-middle me-1" data-feather="settings"></i> Settings & Privacy</a>
                            <a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="help-circle"></i> Help Center</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('common/header.logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="p-3">
            {{ $slot }}
        </main>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row text-muted">
                    <div class="col-6 text-start">
                        <p class="mb-0">
                            <a class="text-muted" href="{{ config("app.url") }}" target="_blank"><strong>{{ config("app.name") }}</strong></a> - <a class="text-muted" href="{{ config("app.url") }}" target="_blank"><strong>{{ config("app.name") }}</strong></a>								&copy;
                        </p>
                    </div>
                    <div class="col-6 text-end">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                <a class="text-muted" href="https://adminkit.io/" target="_blank">Support</a>
                            </li>
                            <li class="list-inline-item">
                                <a class="text-muted" href="https://adminkit.io/" target="_blank">Help Center</a>
                            </li>
                            <li class="list-inline-item">
                                <a class="text-muted" href="https://adminkit.io/" target="_blank">Privacy</a>
                            </li>
                            <li class="list-inline-item">
                                <a class="text-muted" href="https://adminkit.io/" target="_blank">Terms</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="{{ asset("static/js/app.js") }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@stack('scripts')
</body>

</html>
