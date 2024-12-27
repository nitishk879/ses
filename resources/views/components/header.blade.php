<heder>
    <nav class="navbar navbar-expand-md navbar-light bg- shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo-dark.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="img-fluid" style="max-height: 52px!important;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ url("/home") }}">{{ __("common/header.home") }}</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __("common/header.talents") }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route("talents.index") }}">{{ __("common/header.find_talent") }}</a></li>
                            @can('create', \App\Models\Talent::class)
                                <li><a class="dropdown-item" href="{{ route("talents.create") }}">{{ __("common/header.add_new") }}</a></li>
                            @endcan
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">{{ __("common/header.bookmarks") }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __("common/header.projects") }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route("project.index") }}">{{ __("common/header.find_work") }}</a></li>
                            @can('create', \App\Models\Project::class)
                                <li><a class="dropdown-item" href="{{ route("project.create") }}">{{ __("common/header.add_new_project") }}</a></li>
                            @endcan
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">{{ __("common/header.bookmarks") }}</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __("common/header.explore") }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route("companies.index") }}">{{ __("common/header.members") }}</a></li>
                            @can('create', \App\Models\Company::class)
                                <li><a class="dropdown-item" href="{{ route("companies.create") }}">{{ __("common/header.add_new") }}</a></li>
                            @endcan
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">{{ __("common/header.bookmarks") }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pricing">{{ __("common/header.pricing_plan") }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">{{ __("common/header.customer_support") }}</a>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    <!----- Set language ---->
                    <li class="nav-item dropdown lang">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __("common/header.language") }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('language', 'en') }}"><img src="{{ asset("images/united-states.png") }}" alt="" class="me-3" height="16" width="16"/> {{ __("common/header.english") }}</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('language', 'jp') }}"><img src="{{ asset("images/japan.png") }}" alt="" class="me-3" height="16" width="16"/>{{ __("common/header.japanese") }}</a></li>
                        </ul>
                    </li>
                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('common/header.login') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('common/header.register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle d-inline-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <div class="d-flex align-items-center w-100 gap-2 text-wrap">
                                    <img src="{{ Auth::user()->profile_photo_url ?? Auth::user()->avatar ?? 'https://picsum.photos/32/32' }}" alt="" class="rounded-5" height="32" width="32"/>
                                    {{ \Illuminate\Support\Str::limit(Auth::user()->name, 14) }}
                                </div>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route("profile.show") }}">{{ __("common/header.profile") }}</a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('common/header.logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                                @if(Auth::user()->company)
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                                        {{ __('common/header.my_dashboard') }}
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
</heder>
