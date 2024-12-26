<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __("common/landing.page_title") }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

        @vite(['resources/sass/app.scss', 'resources/js/app.js'])

        <!---- Font Awesome ---->
        <script src="https://kit.fontawesome.com/8c6f840b1c.js" crossorigin="anonymous"></script>

        <!-- Favicons -->
        <link rel="shortcut icon" href="{{ asset("images/logo.png") }}">

        @stack('stylesheets')

    </head>
    <body>
    <div id="main">
        <x-header />
        <main class="mt-6">
            <div class="HomePage w-100 h-100 relative bg-white">
                <!-- Hero Section -->
                <section id="heroSection" class="hero-section">
                    <div class="container">
                        <div class="row pt-5">
                            <div class="col-lg-7">
                                <h1 class="hero-title mb-3">
                                    {!! __("common/landing.hero_title") !!}
                                </h1>
                                <p>{!! __("common/landing.hero_paragraph") !!}</p>
                            </div>
                            <div class="col-lg-5"></div>
                            <div class="col-lg-6 mb-3">
                                <form action="{{ route("project.index") }}" class="search-block input-group mb-3">
                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="text" class="form-control" name="search" placeholder="{{ __("common/landing.search_keywords") }}" aria-label="project title" required>
                                    <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                                    <input type="text" class="form-control" name="location" placeholder="{{ __("common/landing.preferred_location") }}" aria-label="Server" required>
                                    <button class="btn btn-primary" type="submit" id="button-addon2">{{ __("common/home.search") }}</button>
                                </form>
                                <p class="suggestion">
                                    <span>{{ __("common/landing.suggestions") }}:</span>
                                    <span class="darken"> {{ __("common/landing.designing") }},</span>
                                    <span class="darken"> {{ __("common/landing.programming") }},</span>
                                    <span class="highlighted"> {{ __("common/landing.digital_marketing") }},</span>
                                    <span class="darken"> {{ __("common/landing.videos") }},</span>
                                    <span class="darken"> {{ __("common/landing.animation") }}.</span>
                                </p>
                            </div>
                            <div class="col-lg-6"></div>
                            @guest
                                <div class="col-lg-5 get-started-block">
                                    <h4 class="text-zinc-700 text-2xl mb-3">{{ __("common/landing.search_for_talent") }}</h4>
                                    <a href="" class="btn btn-primary">{{ __("common/landing.get_started_now") }}</a>
                                </div>
                            @else
                                <div class="col-lg-5" style="min-height: 8rem;"></div>
                            @endguest
                        </div>
                        <img src="{{ asset("images/welcome-hero-image.png") }}" class="hero-image" alt="">
                    </div>
                </section>
                <!-- Hero Section -->
                <!-- Fun facts --->
                <section id="funFact">
                    <div class="container">
                        <div class="fun-fact row justify-content-center align-items-center">
                            <div class="col-md-6 col-lg-3 d-flex p-3 p-md-5 bg-white rounded-lg gap-3 align-items-center">
                                <div class="icon p-4">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_3069_722)">
                                            <path opacity="0.2" d="M20.0002 23.7505C14.7343 23.7588 9.55979 22.3744 5.00146 19.7378V32.5005C5.00146 32.6647 5.0338 32.8272 5.09661 32.9789C5.15943 33.1305 5.25151 33.2683 5.36758 33.3844C5.48365 33.5005 5.62145 33.5926 5.77311 33.6554C5.92477 33.7182 6.08731 33.7505 6.25146 33.7505H33.7515C33.9156 33.7505 34.0782 33.7182 34.2298 33.6554C34.3815 33.5926 34.5193 33.5005 34.6354 33.3844C34.7514 33.2683 34.8435 33.1305 34.9063 32.9789C34.9691 32.8272 35.0015 32.6647 35.0015 32.5005V19.7363C30.4426 22.3739 25.2672 23.7588 20.0002 23.7505Z" fill="#0080FF"/>
                                            <path d="M33.7515 11.25H6.25146C5.56111 11.25 5.00146 11.8096 5.00146 12.5V32.5C5.00146 33.1904 5.56111 33.75 6.25146 33.75H33.7515C34.4418 33.75 35.0015 33.1904 35.0015 32.5V12.5C35.0015 11.8096 34.4418 11.25 33.7515 11.25Z" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M26.25 11.25V8.75C26.25 8.08696 25.9866 7.45107 25.5178 6.98223C25.0489 6.51339 24.413 6.25 23.75 6.25H16.25C15.587 6.25 14.9511 6.51339 14.4822 6.98223C14.0134 7.45107 13.75 8.08696 13.75 8.75V11.25" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M35.0012 19.7363C30.4423 22.3739 25.2669 23.7588 20 23.7505C14.7339 23.7588 9.55935 22.3744 5.00098 19.7377" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M18.125 18.75H21.875" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_3069_722">
                                                <rect width="40" height="40" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                                <div class="d-block justify-content-start align-items-start">
                                    <h4 class="">{{ \App\Models\Project::count() }}</h4>
                                    <h6 class="">{{ __("common/welcome.job_and_project") }}</h6>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3 d-flex p-3 p-md-5 bg-white rounded-lg gap-3 align-items-center">
                                <div class="icon p-4">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_3069_734)">
                                            <path opacity="0.2" d="M22.499 33.748V6.24805C22.499 5.91653 22.3673 5.59858 22.1329 5.36416C21.8985 5.12974 21.5805 4.99805 21.249 4.99805H6.24902C5.9175 4.99805 5.59956 5.12974 5.36514 5.36416C5.13072 5.59858 4.99902 5.91653 4.99902 6.24805V33.748" fill="#0080FF"/>
                                            <path d="M2.5 33.748H37.5" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M22.499 33.748V6.24805C22.499 5.91653 22.3673 5.59858 22.1329 5.36416C21.8985 5.12974 21.5805 4.99805 21.249 4.99805H6.24902C5.9175 4.99805 5.59956 5.12974 5.36514 5.36416C5.13072 5.59858 4.99902 5.91653 4.99902 6.24805V33.748" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M34.999 33.748V16.248C34.999 15.9165 34.8673 15.5986 34.6329 15.3642C34.3985 15.1297 34.0805 14.998 33.749 14.998H22.499" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9.99902 11.248H14.999" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12.499 21.248H17.499" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9.99902 27.498H14.999" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M27.499 27.498H29.999" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M27.499 21.248H29.999" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_3069_734">
                                                <rect width="40" height="40" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                                <div class="d-block justify-content-start align-items-start">
                                    <h4 class="">{{ \App\Models\Company::count() }}</h4>
                                    <h6 class="">{{ __("common/welcome.companies") }}</h6>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3 d-flex p-3 p-md-5 bg-white rounded-lg gap-3 align-items-center">
                                <div class="icon p-4">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_3069_750)">
                                            <path opacity="0.2" d="M13.75 25C18.2373 25 21.875 21.3623 21.875 16.875C21.875 12.3877 18.2373 8.75 13.75 8.75C9.26269 8.75 5.625 12.3877 5.625 16.875C5.625 21.3623 9.26269 25 13.75 25Z" fill="#0080FF"/>
                                            <path d="M13.75 25C18.2373 25 21.875 21.3623 21.875 16.875C21.875 12.3877 18.2373 8.75 13.75 8.75C9.26269 8.75 5.625 12.3877 5.625 16.875C5.625 21.3623 9.26269 25 13.75 25Z" stroke="#0080FF" stroke-width="2" stroke-miterlimit="10"/>
                                            <path d="M24.2832 9.05256C25.4007 8.7377 26.5728 8.66597 27.7204 8.84221C28.8679 9.01845 29.9644 9.43856 30.936 10.0743C31.9075 10.7099 32.7316 11.5465 33.3526 12.5274C33.9737 13.5084 34.3773 14.6111 34.5363 15.7612C34.6953 16.9113 34.606 18.0821 34.2744 19.1948C33.9427 20.3075 33.3766 21.3362 32.6139 22.2116C31.8513 23.0871 30.9099 23.7889 29.8531 24.2699C28.7964 24.7508 27.6489 24.9998 26.4878 24.9999" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M2.49951 30.8432C3.76847 29.0382 5.45309 27.565 7.41115 26.548C9.36921 25.531 11.5432 25.0001 13.7496 25C15.9561 24.9999 18.1301 25.5307 20.0883 26.5476C22.0464 27.5644 23.7311 29.0375 25.0002 30.8424" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M26.4878 25C28.6944 24.9984 30.8689 25.5285 32.8272 26.5455C34.7855 27.5625 36.4699 29.0364 37.7378 30.8424" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_3069_750">
                                                <rect width="40" height="40" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                                <div class="d-block justify-content-start align-items-start">
                                    <h4 class="">{{ \App\Models\Talent::count() }}</h4>
                                    <h6 class="">{{ __("common/welcome.registered_talents") }}</h6>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3 d-flex p-3 p-md-5 bg-white rounded-lg gap-3 align-items-center">
                                <div class="icon p-4">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_3069_762)">
                                            <path opacity="0.2" d="M20.0002 23.7505C14.7343 23.7588 9.55979 22.3744 5.00146 19.7378V32.5005C5.00146 32.6647 5.0338 32.8272 5.09661 32.9789C5.15943 33.1305 5.25151 33.2683 5.36758 33.3844C5.48365 33.5005 5.62145 33.5926 5.77311 33.6554C5.92477 33.7182 6.08731 33.7505 6.25146 33.7505H33.7515C33.9156 33.7505 34.0782 33.7182 34.2298 33.6554C34.3815 33.5926 34.5193 33.5005 34.6354 33.3844C34.7514 33.2683 34.8435 33.1305 34.9063 32.9789C34.9691 32.8272 35.0015 32.6647 35.0015 32.5005V19.7363C30.4426 22.3739 25.2672 23.7588 20.0002 23.7505Z" fill="#0080FF"/>
                                            <path d="M33.7515 11.25H6.25146C5.56111 11.25 5.00146 11.8096 5.00146 12.5V32.5C5.00146 33.1904 5.56111 33.75 6.25146 33.75H33.7515C34.4418 33.75 35.0015 33.1904 35.0015 32.5V12.5C35.0015 11.8096 34.4418 11.25 33.7515 11.25Z" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M26.25 11.25V8.75C26.25 8.08696 25.9866 7.45107 25.5178 6.98223C25.0489 6.51339 24.413 6.25 23.75 6.25H16.25C15.587 6.25 14.9511 6.51339 14.4822 6.98223C14.0134 7.45107 13.75 8.08696 13.75 8.75V11.25" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M35.0012 19.7363C30.4423 22.3739 25.2669 23.7588 20 23.7505C14.7339 23.7588 9.55935 22.3744 5.00098 19.7377" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M18.125 18.75H21.875" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_3069_762">
                                                <rect width="40" height="40" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                                <div class="d-block justify-content-start align-items-start">
                                    <h4 class="">{{ \App\Models\Project::where('created_at', '>=', today()->subMonth())->count() }}</h4>
                                    <h6 class="">{{ __("common/welcome.new_jobs_and_projects") }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Fun facts --->
                <section id="exploreByCategory">
                    <div class="container bg-slate-100 shadow-inner align-items-start">
                        <div class="row justify-content-between align-items-center mb-3">
                            <div class="col-md-9 col-lg-8 col-xl-6 text-justify">
                                <h3 class="section-title">{{ __("common/welcome.explore_by") }} <span>&nbsp; {{ __("common/welcome.category") }}</span></h3>
                            </div>
{{--                            <div class="col-md-3 col-lg-4 col-xl-3 px-6 py-3 rounded-sm text-end align-items-center">--}}
{{--                                <div class="btn btn-outline-primary">{{ __("common/welcome.view_all") }} <i class="fa-solid fa-arrow-right"></i></div>--}}
{{--                            </div>--}}
                        </div>
                        <div class="row justify-content-center align-items-center">
                            @foreach(\App\Models\Category::all() as $category)
                                <div class="col-md-6 col-lg-3">
                                    <div class="explore-flex d-flex grow shrink basis-0 h-28 p-2 rounded-3 bg-white rounded-xl justify-content-start align-items-center gap-4">
                                        <div class="icon p-3">
                                            <i class="fa-solid fa-pen-nib"></i>
                                        </div>
                                        <div class="d-flex flex-column justify-content-start align-items-start d-inline-flex">
                                            <h4 class="">{{ __("common/category.{$category->slug}") }}</h4>
                                            <h5 class="">{{ $category?->subcategories?->count() ?? random_int(201, 500) }} {{ __("common/welcome.open_positions") }}</h5>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
                <!---- Explore by Location -->
                <section id="exploreByLocation">
                    <div class="container">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md-9 col-lg-8 col-xl-6 text-justify">
                                <h3 class="section-title">{{ __("common/welcome.explore_by") }} <span>&nbsp; {{ __("common/welcome.location") }}</span></h3>
                            </div>
{{--                            <div class="col-md-3 col-lg-4 col-xl-3 px-6 py-3 rounded-sm text-end align-items-center">--}}
{{--                                <a href="" class="btn btn-outline-primary">{{ __("common/welcome.view_all") }} <i class="fa-solid fa-arrow-right"></i></a>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                    <div class="container">
                        <div class="row justify-content-center align-items-center">
                            @foreach(\App\Models\Location::take(8)->get() as $location)
                                <div class="col-md-4 col-lg-3">
                                    <div class="explore-flex d-flex grow shrink basis-0 h-28 p-2 rounded-3 bg-white rounded-xl justify-content-start align-items-center gap-4">
                                        <div class="icon p-3">
                                            <i class="fa-solid fa-street-view"></i>
                                        </div>
                                        <div class="d-flex flex-column justify-content-start align-items-start d-inline-flex">
                                            <h4>{{ $location->title }}</h4>
                                            <h5>{{ $location->projects->count() }} {{ __("common/welcome.open_positions") }}</h5>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="container start-posting my-5">
                        <div class="row align-items-center">
                            <div class="col-md-8 col-lg-6 text-center text-md-start">
                                <div class="StartPostingJobsAndProjectsToday w-100 h-60">
                                    <h1 class="main-heading mb-3">{!! __('common/welcome.start_posting_jobs_and_project') !!}</h1>
                                </div>
                                @auth
                                    <a href="{{ route("project.create") }}" class="btn btn-lg btn-secondary my-3">{{ __("common/header.add_new_project") }}</a>
                                @else
                                    <a href="{{ route("register") }}" class="btn btn-lg btn-secondary my-3">{{ __("common/welcome.sign_up_free") }}</a>
                                @endauth
                            </div>
                            <div class="col-md-4 col-lg-6">
                                <div class="my-circle">
                                    <img class="img-fluid" src="{{ asset("images/laptop.png") }}" alt=""/>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!---- Explore by Location -->
                <!-- How it work -->
                <section id="howItWork">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-5 col-lg-4 text-center">
                                <h3 class="section-title">{!! __("common/welcome.how_it_works") !!}</h3>
                            </div>
                        </div>
                        <div class="row justify-content-center gx-3">
                            <div class="col-lg-2 col-md-4 p-3 rounded-3">
                                <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                    <div class="icon">
                                        <svg width="43" height="43" viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M40.9734 8.04858C40.9734 7.96657 40.95 7.89627 40.9265 7.82597C40.9031 7.75567 40.8679 7.68537 40.8328 7.62678C40.7976 7.5682 40.7625 7.4979 40.7156 7.45103C40.6688 7.39245 40.6102 7.34558 40.5516 7.29872C40.493 7.25185 40.4344 7.2167 40.3758 7.18155C40.3055 7.1464 40.2235 7.12297 40.1532 7.09953C40.1064 7.08782 40.0712 7.05267 40.0126 7.05267C39.9892 7.05267 39.954 7.05267 39.9306 7.05267C39.8955 7.05267 39.872 7.04095 39.8486 7.04095H28.0265C27.4641 3.72513 24.5818 1.18262 21.1019 1.18262C17.6221 1.18262 14.7398 3.72513 14.1774 7.04095H2.34354C2.34354 7.04095 2.28496 7.05267 2.26153 7.05267C2.23809 7.05267 2.20294 7.05267 2.17951 7.05267C2.13264 7.05267 2.08578 7.08782 2.03891 7.09953C1.95689 7.12297 1.88659 7.1464 1.81629 7.18155C1.74599 7.2167 1.69913 7.25185 1.64054 7.29872C1.58196 7.34558 1.52338 7.39245 1.47651 7.45103C1.42964 7.50962 1.39449 7.5682 1.35934 7.62678C1.32419 7.69708 1.28904 7.75567 1.26561 7.82597C1.24218 7.89627 1.23046 7.97828 1.21874 8.04858C1.21874 8.09545 1.18359 8.14232 1.18359 8.2009V31.6342C1.18359 33.5675 2.76534 35.1492 4.69859 35.1492H10.5569C11.2013 35.1492 11.7286 34.622 11.7286 33.9776C11.7286 33.3331 11.2013 32.8059 10.5569 32.8059H4.69859C4.05418 32.8059 3.52693 32.2786 3.52693 31.6342V15.0669C4.54628 16.7423 5.96399 18.2187 7.93239 18.7108C8.02613 18.7342 8.11986 18.7459 8.21359 18.7459H33.9903C34.084 18.7459 34.1777 18.7342 34.2715 18.7108C36.2399 18.2187 37.6576 16.7541 38.6769 15.0669V31.6342C38.6769 32.2786 38.1497 32.8059 37.5053 32.8059H31.6469C31.0025 32.8059 30.4753 33.3331 30.4753 33.9776C30.4753 34.622 31.0025 35.1492 31.6469 35.1492H37.5053C39.4385 35.1492 41.0203 33.5675 41.0203 31.6342V8.2009C41.0203 8.2009 40.9968 8.10717 40.9851 8.04858H40.9734ZM21.0902 3.51423C23.2695 3.51423 25.109 5.01397 25.6246 7.02923H16.5441C17.0714 5.01397 18.8992 3.51423 21.0785 3.51423H21.0902ZM33.8262 16.4026H8.35419C5.77653 15.6527 4.34709 11.8331 3.76126 9.37257H38.4074C37.8216 11.8331 36.3922 15.6527 33.8145 16.4026H33.8262ZM27.6515 35.3601C28.6826 33.9893 29.2919 32.3021 29.2919 30.4626C29.2919 25.9399 25.6128 22.2609 21.0902 22.2609C16.5676 22.2609 12.8885 25.9399 12.8885 30.4626C12.8885 34.9852 16.5676 38.6642 21.0902 38.6642C22.9297 38.6642 24.6286 38.0433 25.9878 37.0239L29.6317 40.6678C29.866 40.9021 30.1589 41.0076 30.4635 41.0076C30.7682 41.0076 31.0611 40.8904 31.2954 40.6678C31.7524 40.2108 31.7524 39.4727 31.2954 39.0157L27.6515 35.3718V35.3601ZM15.2319 30.4626C15.2319 27.2288 17.8564 24.6042 21.0902 24.6042C24.324 24.6042 26.9485 27.2288 26.9485 30.4626C26.9485 32.0795 26.2924 33.544 25.2379 34.5985C24.1717 35.653 22.7188 36.3092 21.1019 36.3092C17.8681 36.3092 15.2436 33.6847 15.2436 30.4508L15.2319 30.4626Z" fill="#0080FF"/>
                                        </svg>
                                    </div>
                                    <div class="arrow">
                                        <svg width="100" height="21" viewBox="0 0 100 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M89 1L98.3386 10.2258L89.0715 19.4516" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="1" y1="9" x2="88" y2="9" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-dasharray="6 6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-block px-3 align-items-center">
                                    <h4 class="title">{{ __("common/welcome.how_it_work.step_1_title") }}</h4>
                                    <p>{{ __("common/welcome.how_it_work.step_1_text") }}</p>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 p-3 rounded-3">
                                <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                    <div class="icon">
                                        <svg width="37" height="37" viewBox="0 0 37 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_3381_1426)">
                                                <mask id="mask0_3381_1426" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="37" height="37">
                                                    <path d="M0 3.8147e-06H37V37H0V3.8147e-06Z" fill="white"/>
                                                </mask>
                                                <g mask="url(#mask0_3381_1426)">
                                                    <path d="M5.41992 8.38281C5.41992 11.5757 8.00826 14.1641 11.2012 14.1641C14.3941 14.1641 16.9824 11.5757 16.9824 8.38281C16.9824 5.1899 14.3941 2.60156 11.2012 2.60156C8.00826 2.60156 5.41992 5.1899 5.41992 8.38281Z" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M24.2812 12.7188C24.2812 15.1134 26.2225 17.0547 28.6172 17.0547C31.0119 17.0547 32.9531 15.1134 32.9531 12.7188C32.9531 10.3241 31.0119 8.38281 28.6172 8.38281C26.2225 8.38281 24.2812 10.3241 24.2812 12.7188Z" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M1.08398 22.1133V17.0547C1.08398 15.4582 2.37812 14.1641 3.97461 14.1641H18.4277C20.0242 14.1641 21.3184 15.4582 21.3184 17.0547V22.1133H23.4863C24.6836 22.1133 25.6543 23.084 25.6543 24.2813C25.6543 25.4785 24.6836 26.4492 23.4863 26.4492H21.3184V34.3984H13.3691V32.2305C13.3691 31.0332 12.3985 30.0625 11.2012 30.0625C10.0039 30.0625 9.0332 31.0332 9.0332 32.2305V34.3984H1.08398V26.4492H3.25195C4.44925 26.4492 5.41992 25.4785 5.41992 24.2813C5.41992 23.084 4.44925 22.1133 3.25195 22.1133H1.08398Z" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M21.3184 22.1133V17.0547H33.748C34.9453 17.0547 35.916 18.0254 35.916 19.2227V31.5078H21.3184V26.4492" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                </g>
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_3381_1426">
                                                    <rect width="37" height="37" fill="white"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <div class="arrow">
                                        <svg width="100" height="21" viewBox="0 0 100 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M89 1L98.3386 10.2258L89.0715 19.4516" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="1" y1="9" x2="88" y2="9" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-dasharray="6 6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-block px-3 align-items-center">
                                    <h4 class="title">{{ __("common/welcome.how_it_work.step_2_title") }}</h4>
                                    <p>{{ __("common/welcome.how_it_work.step_2_text") }}</p>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 p-3 rounded-3">
                                <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                    <div class="icon">
                                        <svg width="47" height="47" viewBox="0 0 47 47" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_3381_1478)">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M20.7293 0.888672C19.2243 0.888672 17.9391 1.42805 17.0662 2.23983C16.1933 3.05162 15.7152 4.05719 15.4354 4.992C15.1571 5.92128 15.0616 6.82211 15.0367 7.49497C11.6734 8.71312 9.53426 10.9512 8.32791 13.1737C6.54413 16.4601 6.56143 19.7247 6.56143 19.7247V33.0674L3.96431 36.9603C3.33242 37.9002 4.00292 39.1664 5.13551 39.1698H13.7187C13.7983 39.9086 13.9546 40.8245 14.314 41.7835C14.7078 42.8344 15.3445 43.9615 16.4072 44.8292C17.4698 45.6968 18.9452 46.2523 20.7293 46.2523C22.5113 46.2523 23.988 45.7016 25.0513 44.8402C26.1146 43.9789 26.7605 42.8617 27.1556 41.8167C27.5231 40.8449 27.6819 39.9111 27.7592 39.1698H36.3313C37.4639 39.1664 38.1372 37.9002 37.5053 36.9603L34.9082 33.0674V29.1247C39.7054 28.4339 43.4166 24.3008 43.4166 19.3176C43.4166 13.854 38.9598 9.39712 33.4961 9.39712C32.4572 9.39712 31.4544 9.55817 30.5113 9.85674C29.4235 8.89294 28.0639 8.0697 26.4163 7.4839C26.389 6.81374 26.294 5.91929 26.0149 4.992C25.7337 4.05794 25.2549 3.05052 24.3813 2.23983C23.5077 1.42915 22.234 0.888672 20.7293 0.888672ZM20.7293 3.7239C21.6257 3.7239 22.0811 3.96203 22.457 4.31088C22.8329 4.65972 23.12 5.19404 23.3042 5.80602C23.4016 6.12956 23.4635 6.45947 23.5063 6.77232C22.639 6.64441 21.7292 6.56189 20.7403 6.5619C19.7469 6.5619 18.8238 6.63855 17.9549 6.76955C17.9977 6.4576 18.0577 6.12876 18.1543 5.80602C18.3377 5.19345 18.6266 4.65962 19.0015 4.31088C19.3765 3.96214 19.8331 3.7239 20.7293 3.7239ZM20.7403 9.39712C23.9529 9.39712 26.1493 10.1641 27.7343 11.2577C25.2176 13.0606 23.5645 16.0004 23.5645 19.3176C23.5645 24.2969 27.2796 28.4263 32.0729 29.1219V33.4966C32.074 33.7762 32.1579 34.048 32.3138 34.2801L33.6844 36.3318H20.7403H7.78523L9.15577 34.2801C9.31167 34.048 9.39561 33.7762 9.39666 33.4966V19.7247C9.39666 19.7247 9.41397 17.115 10.8198 14.5249C12.2257 11.9348 14.7962 9.39712 20.7403 9.39712ZM33.4961 12.2323C37.428 12.2323 40.5814 15.3857 40.5814 19.3176C40.5814 23.2496 37.428 26.4002 33.4961 26.4002C29.5642 26.4002 26.3997 23.2496 26.3997 19.3176C26.3997 15.3857 29.5642 12.2323 33.4961 12.2323ZM33.4739 13.625C33.2871 13.6272 33.1024 13.6662 32.9307 13.74C32.759 13.8137 32.6035 13.9207 32.4733 14.0548C32.3431 14.1889 32.2407 14.3474 32.172 14.5212C32.1033 14.695 32.0696 14.8807 32.0729 15.0676V19.3176C32.0812 19.6882 32.2341 20.0408 32.4991 20.2999C32.764 20.559 33.1199 20.7042 33.4906 20.7042C33.8612 20.7042 34.2171 20.559 34.482 20.2999C34.747 20.0408 34.8999 19.6882 34.9082 19.3176V15.0676C34.9116 14.8779 34.8768 14.6895 34.8061 14.5135C34.7353 14.3375 34.6299 14.1775 34.4962 14.043C34.3624 13.9084 34.203 13.8021 34.0274 13.7303C33.8518 13.6586 33.6636 13.6227 33.4739 13.625ZM33.4933 22.1529C33.3072 22.1529 33.1228 22.1895 32.9508 22.2608C32.7788 22.332 32.6226 22.4364 32.4909 22.5681C32.3593 22.6997 32.2549 22.856 32.1836 23.028C32.1124 23.2 32.0757 23.3843 32.0757 23.5705C32.0757 23.7567 32.1124 23.941 32.1836 24.113C32.2549 24.285 32.3593 24.4413 32.4909 24.5729C32.6226 24.7045 32.7788 24.809 32.9508 24.8802C33.1228 24.9514 33.3072 24.9881 33.4933 24.9881C33.6795 24.9881 33.8638 24.9514 34.0358 24.8802C34.2078 24.809 34.3641 24.7045 34.4957 24.5729C34.6274 24.4413 34.7318 24.285 34.803 24.113C34.8743 23.941 34.9109 23.7567 34.9109 23.5705C34.9109 23.3843 34.8743 23.2 34.803 23.028C34.7318 22.856 34.6274 22.6997 34.4957 22.5681C34.3641 22.4364 34.2078 22.332 34.0358 22.2608C33.8638 22.1895 33.6795 22.1529 33.4933 22.1529ZM16.5789 39.1698H20.7403H24.8963C24.8301 39.6688 24.7238 40.2353 24.5059 40.8117C24.236 41.5255 23.8361 42.1757 23.271 42.6335C22.7059 43.0913 21.9596 43.4171 20.7293 43.4171C19.5009 43.4171 18.7608 43.0949 18.1958 42.6335C17.6308 42.1722 17.2312 41.5107 16.961 40.7895C16.7483 40.222 16.646 39.6658 16.5789 39.1698Z" fill="#0080FF"/>
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_3381_1478">
                                                    <rect width="45.2923" height="45.2923" fill="white" transform="translate(0.888672 0.888672)"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <div class="arrow">
                                        <svg width="100" height="21" viewBox="0 0 100 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M89 1L98.3386 10.2258L89.0715 19.4516" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="1" y1="9" x2="88" y2="9" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-dasharray="6 6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-block px-3 align-items-center">
                                    <h4 class="title">{{ __("common/welcome.how_it_work.step_3_title") }}</h4>
                                    <p>{{ __("common/welcome.how_it_work.step_3_text") }}</p>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 p-3 rounded-3">
                                <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                    <div class="icon">
                                        <svg width="47" height="47" viewBox="0 0 47 47" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_3381_1481)">
                                                <path d="M44.8541 22.2072H44.0489V22.1128C44.0489 18.7993 42.1867 16.1372 39.7606 14.7719C41.081 13.616 41.9172 11.9204 41.9172 10.032C41.9172 6.55744 39.0902 3.73047 35.6157 3.73047C32.1412 3.73047 29.3142 6.55744 29.3142 10.032C29.3142 11.9204 30.1504 13.616 31.4704 14.7719C29.0388 16.1396 27.1825 18.8083 27.1825 22.1128V22.2072H15.4416C14.2229 21.3146 12.7218 20.7859 11.0987 20.7859C7.0364 20.7859 3.73119 24.0908 3.73119 28.1535C3.73119 30.5464 4.87877 32.6761 6.65215 34.0227C3.23359 35.7739 0.888672 39.3345 0.888672 43.4321V44.8533C0.888672 45.5863 1.48268 46.1803 2.2156 46.1803H20.6923C21.4252 46.1803 22.0192 45.5863 22.0192 44.8533V43.4321C22.0192 42.4134 21.8738 41.4279 21.6035 40.4952H43.5271V42.0108C43.5271 42.7434 44.1211 43.3377 44.8541 43.3377C45.587 43.3377 46.181 42.7434 46.181 42.0108V23.5341C46.181 22.8012 45.587 22.2072 44.8541 22.2072ZM31.968 10.032C31.968 8.02085 33.6042 6.38432 35.6157 6.38432C37.6268 6.38432 39.2634 8.02085 39.2634 10.032C39.2634 12.0431 37.6272 13.6796 35.6157 13.6796C33.6046 13.6796 31.968 12.0431 31.968 10.032ZM29.8363 22.1128C29.8363 18.8705 32.476 16.3335 35.6157 16.3335C38.7464 16.3335 41.3951 18.8612 41.3951 22.1128V22.2072H29.8363V22.1128ZM11.0987 23.4398C13.6976 23.4398 15.8121 25.5542 15.8121 28.1535C15.8121 30.7524 13.6976 32.8668 11.0987 32.8668C8.49947 32.8668 6.38504 30.7524 6.38504 28.1535C6.38504 25.5542 8.49947 23.4398 11.0987 23.4398ZM19.3654 43.5264H3.54252V43.4321C3.54252 39.0698 7.09169 35.5206 11.454 35.5206C15.8162 35.5206 19.3654 39.0698 19.3654 43.4321V43.5264ZM15.8138 33.8088C17.4334 32.4563 18.4659 30.4234 18.4659 28.1535C18.4659 26.9703 18.1843 25.8528 17.6867 24.861H43.5271V37.8414H20.4148C19.3149 36.0853 17.7143 34.6733 15.8138 33.8088Z" fill="#0080FF"/>
                                                <path d="M5.76892 18.4659H24.9561C26.0135 18.4659 26.6476 17.2841 26.0601 16.403L23.4405 12.4737V5.76892C23.4405 3.09399 21.2825 0.888672 18.5602 0.888672H5.76892C3.06116 0.888672 0.888672 3.08051 0.888672 5.76892V13.586C0.888672 16.2682 3.05322 18.4659 5.76892 18.4659ZM3.54252 5.76892C3.54252 4.5543 4.51974 3.54252 5.76892 3.54252H18.5602C19.797 3.54252 20.7866 4.53771 20.7866 5.76892V12.8752C20.7866 13.1371 20.864 13.3932 21.0095 13.6112L22.4767 15.8121H5.76892C4.53218 15.8121 3.54252 14.8169 3.54252 13.5857V5.76892Z" fill="#0080FF"/>
                                                <path d="M7.90065 8.5181H16.4286C17.1611 8.5181 17.7555 7.92375 17.7555 7.19118C17.7555 6.45826 17.1611 5.86426 16.4286 5.86426H7.90065C7.16808 5.86426 6.57373 6.45826 6.57373 7.19118C6.57373 7.92375 7.16774 8.5181 7.90065 8.5181Z" fill="#0080FF"/>
                                                <path d="M7.90065 13.4917H12.1648C12.8973 13.4917 13.4917 12.8974 13.4917 12.1648C13.4917 11.4319 12.8973 10.8379 12.1648 10.8379H7.90065C7.16808 10.8379 6.57373 11.4319 6.57373 12.1648C6.57373 12.8974 7.16774 13.4917 7.90065 13.4917Z" fill="#0080FF"/>
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_3381_1481">
                                                    <rect width="45.2923" height="45.2923" fill="white" transform="translate(0.888672 0.888672)"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <div class="arrow">
                                        <svg width="100" height="21" viewBox="0 0 100 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M89 1L98.3386 10.2258L89.0715 19.4516" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="1" y1="9" x2="88" y2="9" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-dasharray="6 6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-block px-3 align-items-center">
                                    <h4 class="title">{{ __("common/welcome.how_it_work.step_4_title") }}</h4>
                                    <p>{{ __("common/welcome.how_it_work.step_4_text") }}</p>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 p-3 rounded-3">
                                <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                    <div class="icon">
                                        <svg width="47" height="47" viewBox="0 0 47 47" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_3381_1532)">
                                                <mask id="mask0_3381_1532" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="47" height="47">
                                                    <path d="M0.888672 0.888676H46.8887V46.8887H0.888672V0.888676Z" fill="white"/>
                                                </mask>
                                                <g mask="url(#mask0_3381_1532)">
                                                    <path d="M45.541 36.0176H2.23633V3.67383H45.541V36.0176Z" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linejoin="round"/>
                                                    <path d="M40.0605 25.2363H34.6699C34.6699 19.2796 29.8454 14.4551 23.8887 14.4551C17.9319 14.4551 13.1074 19.2796 13.1074 25.2363H7.7168C7.7168 16.3148 14.9672 9.06445 23.8887 9.06445C32.8102 9.06445 40.0605 16.3148 40.0605 25.2363Z" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linejoin="round"/>
                                                    <path d="M23.8887 9.06445V14.4551" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linejoin="round"/>
                                                    <path d="M12.4536 13.8015L16.2654 17.6133" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linejoin="round"/>
                                                    <path d="M31.5122 17.6133L35.324 13.8015" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linejoin="round"/>
                                                    <path d="M23.8887 25.2363L29.2793 19.8457" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M9.06445 30.627H14.4551" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M17.1504 30.627H22.541" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M25.2363 30.627H30.627" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M33.3223 30.627H38.7129" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M17.1504 25.2363H30.627" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M18.498 36.0176V38.7129C18.498 41.69 16.0846 44.1035 13.1074 44.1035" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linejoin="round"/>
                                                    <path d="M29.2793 36.0176V38.7129C29.2793 41.69 31.6928 44.1035 34.6699 44.1035" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linejoin="round"/>
                                                    <path d="M13.1074 44.1035H34.6699" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                </g>
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_3381_1532">
                                                    <rect width="46" height="46" fill="white" transform="translate(0.888672 0.888672)"/>
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <div class="arrow">
                                        <svg width="100" height="21" viewBox="0 0 100 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M89 1L98.3386 10.2258L89.0715 19.4516" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="1" y1="9" x2="88" y2="9" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-dasharray="6 6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-block px-3 align-items-center">
                                    <h4 class="title">{{ __("common/welcome.how_it_work.step_5_title") }}</h4>
                                    <p>{{ __("common/welcome.how_it_work.step_5_text") }}</p>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 p-3 rounded-3">
                                <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                    <div class="icon">
                                        <svg width="47" height="47" viewBox="0 0 47 47" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M40.1047 23.6293L34.4432 20.7986V8.43754C34.4407 7.99976 34.3517 7.56679 34.1813 7.16352C34.0109 6.76025 33.7625 6.39464 33.4503 6.08772C33.1382 5.7808 32.7684 5.53862 32.3623 5.37511C31.9562 5.21161 31.5217 5.13 31.084 5.13498H8.43782C7.56345 5.13993 6.72631 5.48947 6.10803 6.10775C5.48975 6.72603 5.14021 7.56318 5.13525 8.43754V38.6324C5.14021 39.5068 5.48975 40.3439 6.10803 40.9622C6.72631 41.5805 7.56345 41.93 8.43782 41.935H31.084C31.5226 41.9292 31.9545 41.826 32.3484 41.633C32.5556 41.6515 32.764 41.6515 32.9712 41.633C33.4815 41.6351 33.9853 41.5188 34.4432 41.2933C36.7084 40.1817 38.6165 38.4571 39.9509 36.3155C41.2852 34.1738 41.9923 31.701 41.9919 29.1776V26.5922C41.9902 25.9703 41.8119 25.3617 41.4778 24.8372C41.1437 24.3127 40.6675 23.8938 40.1047 23.6293ZM8.43782 39.1042C8.31269 39.1042 8.19269 39.0545 8.10421 38.966C8.01573 38.8775 7.96602 38.7575 7.96602 38.6324V8.43754C7.96602 8.31242 8.01573 8.19241 8.10421 8.10394C8.19269 8.01546 8.31269 7.96575 8.43782 7.96575H31.084C31.2091 7.96575 31.3291 8.01546 31.4176 8.10394C31.5061 8.19241 31.5558 8.31242 31.5558 8.43754V20.7797L25.8942 23.6105C25.7016 23.7208 25.5185 23.8471 25.3469 23.9879H12.2122C11.8368 23.9879 11.4768 24.137 11.2113 24.4025C10.9459 24.6679 10.7968 25.0279 10.7968 25.4033C10.7968 25.7787 10.9459 26.1387 11.2113 26.4041C11.4768 26.6696 11.8368 26.8187 12.2122 26.8187H24.007V29.1965C24.0005 31.0438 24.3759 32.8726 25.1099 34.5678C25.8439 36.2631 26.9205 37.7883 28.2721 39.0476L8.43782 39.1042ZM39.1045 29.1965C39.1109 31.1885 38.5591 33.1424 37.5118 34.8369C36.4644 36.5313 34.9634 37.8985 33.1787 38.7834C33.1131 38.8114 33.0425 38.8259 32.9712 38.8259C32.8998 38.8259 32.8292 38.8114 32.7636 38.7834C30.9789 37.8985 29.4779 36.5313 28.4305 34.8369C27.3832 33.1424 26.8314 31.1885 26.8378 29.1965V26.5922C26.8358 26.5022 26.8597 26.4135 26.9065 26.3366C26.9533 26.2597 27.0212 26.1978 27.102 26.1582L32.7636 23.3274C32.8292 23.2994 32.8998 23.2849 32.9712 23.2849C33.0425 23.2849 33.1131 23.2994 33.1787 23.3274L38.8403 26.1582C38.9211 26.1978 38.989 26.2597 39.0358 26.3366C39.0826 26.4135 39.1065 26.5022 39.1045 26.5922V29.1965ZM36.7455 28.1963C37.0106 28.4617 37.1594 28.8214 37.1594 29.1965C37.1594 29.5716 37.0106 29.9313 36.7455 30.1967L32.9712 33.9711C32.7058 34.2361 32.346 34.385 31.9709 34.385C31.5959 34.385 31.2361 34.2361 30.9707 33.9711L29.0836 32.0839C28.9445 31.9543 28.833 31.7981 28.7556 31.6245C28.6782 31.4508 28.6367 31.2634 28.6333 31.0734C28.6299 30.8833 28.6649 30.6945 28.7361 30.5183C28.8073 30.3421 28.9132 30.182 29.0476 30.0476C29.182 29.9132 29.3421 29.8072 29.5184 29.736C29.6946 29.6648 29.8834 29.6299 30.0734 29.6332C30.2635 29.6366 30.4509 29.6782 30.6245 29.7555C30.7981 29.8329 30.9544 29.9444 31.084 30.0835L31.9709 30.9705L34.8583 28.1963C35.1178 27.9642 35.4538 27.8359 35.8019 27.8359C36.1501 27.8359 36.486 27.9642 36.7455 28.1963ZM10.7968 19.7606C10.8017 19.3868 10.9524 19.0296 11.2168 18.7652C11.4811 18.5008 11.8383 18.3501 12.2122 18.3452H27.3096C27.685 18.3452 28.045 18.4944 28.3104 18.7598C28.5759 19.0252 28.725 19.3852 28.725 19.7606C28.725 20.136 28.5759 20.496 28.3104 20.7614C28.045 21.0269 27.685 21.176 27.3096 21.176H12.2122C11.8383 21.1711 11.4811 21.0204 11.2168 20.756C10.9524 20.4917 10.8017 20.1345 10.7968 19.7606ZM10.7968 14.0991C10.8017 13.7252 10.9524 13.368 11.2168 13.1037C11.4811 12.8393 11.8383 12.6886 12.2122 12.6837H27.3096C27.685 12.6837 28.045 12.8328 28.3104 13.0983C28.5759 13.3637 28.725 13.7237 28.725 14.0991C28.725 14.4745 28.5759 14.8345 28.3104 15.0999C28.045 15.3653 27.685 15.5145 27.3096 15.5145H12.2122C11.8383 15.5096 11.4811 15.3589 11.2168 15.0945C10.9524 14.8301 10.8017 14.4729 10.7968 14.0991ZM19.2891 32.9709C19.2842 33.3447 19.1335 33.7019 18.8691 33.9663C18.6048 34.2307 18.2476 34.3814 17.8737 34.3863H12.2122C11.8368 34.3863 11.4768 34.2371 11.2113 33.9717C10.9459 33.7063 10.7968 33.3463 10.7968 32.9709C10.7968 32.5955 10.9459 32.2355 11.2113 31.9701C11.4768 31.7046 11.8368 31.5555 12.2122 31.5555H17.8737C18.2476 31.5604 18.6048 31.7111 18.8691 31.9755C19.1335 32.2398 19.2842 32.597 19.2891 32.9709Z" fill="#0080FF"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-block px-3 align-items-center">
                                    <h4 class="title">{{ __("common/welcome.how_it_work.step_6_title") }}</h4>
                                    <p>{{ __("common/welcome.how_it_work.step_6_text") }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- How it work -->
                <section id="featuredSection">
                    <div class="container mb-5">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md-9 col-lg-8 col-xl-6">
                                <h3 class="section-title">{!! __("common/welcome.featured_jobs_and_projects") !!}</h3>
                            </div>
                            <div class="col-md-3 col-lg-4 col-xl-4 text-end">
                                <a href="{{ route("project.index") }}" class="btn btn-outline-primary">{{ __("common/welcome.view_all") }} <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        @foreach(\App\Models\Project::limit(5)->get() as $project)
                            <div class="row justify-content-between bg-white rounded-3 align-items-center list-job">
                                <div class="col-md-9 col-lg-6 justify-content-start align-items-start py-3 d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="{{ __("https://picsum.photos/42/42?random={$project->id}") }}" alt=""/>
                                    </div>
                                    <div class="flex-grow-1 ms-3 flex-column justify-content-start align-items-start gap-3.5 d-md-inline-flex">
                                        <div class="Heading justify-content-start align-items-center gap-2 d-md-inline-flex mb-2">
                                            <div class="main-heading">{{ $project->title ?? ''  }}</div>
                                            <div class="badge">
                                                <div class="main-pill text-sky-500 text-sm font-medium leading-tight">{{ __("common/welcome.contract_base") }}</div>
                                            </div>
                                        </div>
                                        <div class="Info justify-content-start align-items-center gap-4 d-md-inline-flex">
                                            <div class="justify-content-start align-items-center gap-1 d-flex">
                                                <i class="fa-solid fa-map-pin fs-6"></i>
                                                <p class="mb-0 text-gray-500 text-sm font-medium leading-tight">{{ $project->locations->first()->title ?? __("common/home.japan_tokyo") }}</p>
                                            </div>
                                            <div class="justify-content-start align-items-center gap-1 d-flex">
                                                <i class="fa-solid fa-yen-sign fs-6"></i>
                                                <p class="mb-0 text-gray-500 text-sm font-medium leading-tight">
                                                    {{ $project->salary_range }}</p>
                                            </div>
                                            <div class="justify-content-start align-items-center gap-1 d-flex">
                                                <i class="fa-regular fa-calendar-days fs-6"></i>
                                                <p class="mb-0 text-gray-500 text-sm font-medium leading-tight">{{ $project->deadline->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-lg-6 justify-content-center justify-content-md-end align-items-center gap-3 d-flex">
                                    <a href="" class="btn-save"><i class="fa-regular fa-bookmark"></i></a>
                                    <a href="{{ route("project.show", $project) }}" class="btn btn-apply">{{ __("common/welcome.apply_now") }} <i class="fa-solid fa-arrow-right"></i></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
                <!---   Featured Companies --->
{{--                <section id="featuredCompanies">--}}
{{--                    <div class="container my-4">--}}
{{--                        <div class="row justify-content-between align-items-center">--}}
{{--                            <div class="col-md-6 text-center">--}}
{{--                                <h3 class="section-title">{!! __("common/welcome.top_companies_and_employer") !!}</h3>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-5 justify-content-end align-items-center">--}}
{{--                                <div class="IconButton p-3 origin-top-left rotate-180 bg-indigo-50 rounded justify-content-start align-items-start gap-2.5 flex">--}}
{{--                                    <div class="FiArrowRight w-6 h-6 relative"></div>--}}
{{--                                </div>--}}
{{--                                <div class="IconButton p-3 bg-indigo-50 rounded justify-content-start align-items-start gap-2.5 flex">--}}
{{--                                    <div class="FiArrowRight w-6 h-6 relative"></div>--}}
{{--                                </div>--}}
{{--                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">--}}
{{--                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>--}}
{{--                                    <span class="visually-hidden">{{ __("common/landing.previous") }}</span>--}}
{{--                                </button>--}}
{{--                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">--}}
{{--                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>--}}
{{--                                    <span class="visually-hidden">{{ __("common/landing.next") }}</span>--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="container">--}}
{{--                        <div class="row justify-content-evenly" id="multi-item-carousel">--}}
{{--                            <div id="carouselCompany" class="carousel slide" data-bs-ride="carousel">--}}
{{--                                <div class="carousel-inner" role="listbox">--}}
{{--                                    @foreach(\App\Models\Company::limit(12)->get() as $company)--}}
{{--                                        <div class="carousel-item {{ $loop->first ? 'active': '' }}">--}}
{{--                                            <div class="col-md-4 col-lg-3 text-center p-4 bg-white rounded-3">--}}
{{--                                                <div class="company-card">--}}
{{--                                                    <div class="company justify-content-start align-items-start gap-4 d-inline-flex mb-2">--}}
{{--                                                        <div class="company-logo">--}}
{{--                                                            <img src="{{ $company->company_logo_url ?? asset("images/logo.png") }}" alt="{{ $company->company_name }}" class="img-fluid"/>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="flex-column justify-content-start align-items-start gap-1.5 d-inline-flex align-content-stretch">--}}
{{--                                                            <div class="d-flex justify-content-center align-items-center gap-2" style="min-height:84px;">--}}
{{--                                                                <div class="company-name">{{ $company->company_name }}</div>--}}
{{--                                                                @if(!$company->dispatch_business_license)--}}
{{--                                                                    <div class="company-featured">--}}
{{--                                                                        <div class="px-2 py-1">{{ __("common/landing.featured") }}</div>--}}
{{--                                                                    </div>--}}
{{--                                                                @endif--}}
{{--                                                            </div>--}}
{{--                                                            <div class="d-flex justify-content-start align-items-center gap-1.5 d-inline-flex">--}}
{{--                                                                <i class="fa-solid fa-location-dot"></i>--}}
{{--                                                                <div class="company-area ms-1">{{ $company->industry }}</div>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="d-grid gap-2">--}}
{{--                                                        <a href="" class="btn btn-lg btn-outline-primary">{{ __("common/landing.see_all_open_positions") }}</a>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    @endforeach--}}
{{--                                </div>--}}
{{--                                <a class="carousel-control-prev bg-transparent w-aut" href="#carouselCompany" role="button" data-bs-slide="prev">--}}
{{--                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>--}}
{{--                                </a>--}}
{{--                                <a class="carousel-control-next bg-transparent w-aut" href="#carouselCompany" role="button" data-bs-slide="next">--}}
{{--                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </section>--}}
                <!---   Featured Companies --->
                <!------    Testimonial     --------->
                <div id="testimonialSection">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-9 col-lg-5 text-center my-3">
                                <h3 class="section-title">{!! __("common/landing.hear_from_our_client") !!}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-center gx-3" id="multi-item-carousel2">
                            <div id="carouselTestimonial" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#carouselTestimonial" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#carouselTestimonial" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#carouselTestimonial" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                </div>
                                <div class="carousel-inner" role="listbox">
                                    @foreach(\App\Models\Testimonial::latest()->limit(5)->get() as $testimonial)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <div class="col-md-6 col-lg-4 px-3">
                                                <div class="testimonial-card w-100 p-3 bg-white rounded-3 shadow justify-content-center align-items-center">
                                                    <div class="d-flex self-stretch flex-column justify-content-start align-items-start gap-4 d-inline-flex">
                                                        <div class="rating justify-content-start align-items-start gap-0.5 d-inline-flex">
                                                            @for($i=1; $i <= random_int(3, 5); $i++)
                                                                <i class="fa-solid fa-star text-warning"></i>
                                                            @endfor
                                                        </div>
                                                        <p class="w-100 text-gray-600 text-base font-medium leading-normal">“{{ \Illuminate\Support\Str::limit($testimonial->content, 80) }}”</p>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center w-100">
                                                        <div class="d-flex justify-content-start align-items-center gap-3">
                                                            <div class="flex-shrink-0">
                                                                <img class="img-fluid w-12 h-12 rounded-5" src="{{$testimonial->user->profile_photo_url ?? "https://picsum.photos/48/48?random={$testimonial->id}"}}" alt=""/>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="user-name">{{ $testimonial->user->name }}</div>
                                                                <div class="user-profession">{{ $testimonial->user->roles->first()?->title ?? 'UI/UX Designer' }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="text-end"><i class="fa-solid fa-quote-left"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselTestimonial" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">{{ __("common/landing.previous") }}</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselTestimonial" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">{{ __("common/landing.next") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!------    Testimonial     --------->
                <!------    Search register     --------->
                <section id="searchCardSection">
                    <div class="container py-4">
                        <div class="row justify-content-center align-items-center">
                            <div class="col-md-6">
                                <div class="search-card rounded-3">
                                    <img class="search-card-img" src="{{ asset("images/job-search-card.png") }}" alt=""/>
                                    <div class="search-card-title">{{ __("common/landing.search_for_a_project") }}</div>
                                    <p class="w-50 mt-3">{{ __("common/landing.dummy_text") }}</p>
                                    <div class="">
                                        @auth
                                            <a href="{{ route("project.index") }}" class="btn btn-primary">{{ __("common/header.find_work") }}</a>
                                        @else
                                            <a href="{{ route("talent.registration") }}" class="btn btn-primary">{{ __("common/landing.register_now") }} <i class="fa-solid fa-arrow-right"></i></a>
                                        @endauth

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="search-card bg-blue rounded-3">
                                    <div class="search-card-title">{{ __("common/landing.search_for_a_talent") }}</div>
                                    <p class="w-50 mt-3">{{ __("common/landing.dummy_text") }}</p>
                                    <div class="">
                                        @auth
                                            <a href="{{ route("talents.index") }}" class="btn btn-light text-primary">{{ __("common/header.find_talent") }}</a>
                                        @else
                                            <a href="{{ route("members.registration") }}" class="btn btn-light text-primary">{{ __("common/landing.register_now") }} <i class="fa-solid fa-arrow-right"></i></a>
                                        @endauth
                                    </div>
                                    <img class="search-card-img" src="{{ asset("images/talent-search-card.png") }}" alt=""/>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!------    Search register     --------->
                <x-footer />
            </div>
        </main>
    </div>
    <script>
        let item4perCarousel = document.querySelectorAll('#carouselCompany .carousel-item')
        item4perCarousel.forEach((el) => {
            const minPerSlide = 4
            let next = el.nextElementSibling
            for (let i=1; i<minPerSlide; i++) {
                if (!next) {
                    next = item4perCarousel[0]
                }
                let cloneChild = next.cloneNode(true)
                el.appendChild(cloneChild.children[0])
                next = next.nextElementSibling
            }
        })
        const testimonialItem = document.querySelectorAll('#carouselTestimonial .carousel-item')
        testimonialItem.forEach((el) => {
            const minPerSlide = 3
            let next = el.nextElementSibling
            for (let i=1; i<minPerSlide; i++) {
                if (!next) {
                    next = testimonialItem[0]
                }
                let cloneChild = next.cloneNode(true)
                el.appendChild(cloneChild.children[0])
                next = next.nextElementSibling
            }
        })
    </script>
    </body>
</html>
