<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

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
                            <div class="col-md-7">
                                <h1 class="hero-title mb-3">
                                    {!! __("common/landing.hero_title") !!}
                                </h1>
                                <p>{!! __("common/landing.hero_paragraph") !!}</p>
                            </div>
                            <div class="col-md-5"></div>
                            <div class="col-md-6 mb-3">
                                <form action="{{ route("project.index") }}" class="search-block input-group mb-3">
                                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="text" class="form-control" name="search" placeholder="{{ __("common/landing.search_keywords") }}" aria-label="Username">
                                    <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                                    <input type="text" class="form-control" name="location" placeholder="{{ __("common/landing.preferred_location") }}" aria-label="Server">
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
                            <div class="col-md-6"></div>
                            <div class="col-md-5 get-started-block">
                                <h4 class="text-zinc-700 text-2xl mb-3">{{ __("common/landing.search_for_talent") }}</h4>
                                <a href="" class="btn btn-primary">{{ __("common/landing.get_started_now") }}</a>
                            </div>
                        </div>
                        <img src="{{ asset("images/welcome-hero-image.png") }}" class="hero-image" alt="">
                    </div>
                </section>
                <!-- Hero Section -->
                <!-- Fun facts --->
                <section id="funFact">
                    <div class="container">
                        <div class="fun-fact row justify-content-center align-items-center">
                            <div class="col-md-3 d-flex p-3 p-md-5 bg-white rounded-lg gap-3 align-items-center">
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
                            <div class="col-md-3 d-flex p-3 p-md-5 bg-white rounded-lg gap-3 align-items-center">
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
                            <div class="col-md-3 d-flex p-3 p-md-5 bg-white rounded-lg gap-3 align-items-center">
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
                            <div class="col-md-3 d-flex p-3 p-md-5 bg-white rounded-lg gap-3 align-items-center">
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
                            <div class="col-md-5 col-xl-4 text-justify">
                                <h3 class="section-title">{{ __("common/welcome.explore_by") }} <span>&nbsp; {{ __("common/welcome.category") }}</span></h3>
                            </div>
                            <div class="col-md-4 col-xl-3 px-6 py-3 rounded-sm text-end align-items-center">
                                <div class="btn btn-outline-primary">{{ __("common/welcome.view_all") }} <i class="fa-solid fa-arrow-right"></i></div>
                            </div>
                        </div>
                        <div class="row justify-content-center align-items-center">
                            @foreach(\App\Models\Category::all() as $category)
                                <div class="col-md-3">
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
                            <div class="col-md-5 col-xl-4 text-justify">
                                <h3 class="section-title">{{ __("common/welcome.explore_by") }} <span>&nbsp; {{ __("common/welcome.location") }}</span></h3>
                            </div>
                            <div class="col-md-4 col-xl-3 px-6 py-3 rounded-sm text-end align-items-center">
                                <a href="" class="btn btn-outline-primary">{{ __("common/welcome.view_all") }} <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row justify-content-center align-items-center">
                            @foreach(\App\Models\Location::take(8)->get() as $location)
                                <div class="col-md-3">
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
                            <div class="col-md-6">
                                <div class="StartPostingJobsAndProjectsToday w-100 h-60 left-[130px] top-[68px] ">
                                    <h1 class="main-heading mb-3">{!! __('common/welcome.start_posting_jobs_and_project') !!}</h1>
                                </div>
                                <a href="{{ route("register") }}" class="btn btn-lg btn-secondary">{{ __("common/welcome.sign_up_free") }}</a>
                            </div>
                            <div class="col-md-6">
                                <div class="my-circle">
                                    <img class="img-fluid" src="{{ asset("images/laptop.png") }}" alt=""/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container how-it-work">
                        <div class="row justify-content-center">
                            <div class="col-md-5 col-lg-4 text-center">
                                <h3 class="section-title">{!! __("common/welcome.how_it_works") !!}</h3>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row justify-content-center align-items-center gx-3">
                                <div class="col-md-4 p-3 rounded-3">
                                    <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                        <div class="icon">
                                            <svg width="43" height="43" viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M40.9734 8.04858C40.9734 7.96657 40.95 7.89627 40.9265 7.82597C40.9031 7.75567 40.8679 7.68537 40.8328 7.62678C40.7976 7.5682 40.7625 7.4979 40.7156 7.45103C40.6688 7.39245 40.6102 7.34558 40.5516 7.29872C40.493 7.25185 40.4344 7.2167 40.3758 7.18155C40.3055 7.1464 40.2235 7.12297 40.1532 7.09953C40.1064 7.08782 40.0712 7.05267 40.0126 7.05267C39.9892 7.05267 39.954 7.05267 39.9306 7.05267C39.8955 7.05267 39.872 7.04095 39.8486 7.04095H28.0265C27.4641 3.72513 24.5818 1.18262 21.1019 1.18262C17.6221 1.18262 14.7398 3.72513 14.1774 7.04095H2.34354C2.34354 7.04095 2.28496 7.05267 2.26153 7.05267C2.23809 7.05267 2.20294 7.05267 2.17951 7.05267C2.13264 7.05267 2.08578 7.08782 2.03891 7.09953C1.95689 7.12297 1.88659 7.1464 1.81629 7.18155C1.74599 7.2167 1.69913 7.25185 1.64054 7.29872C1.58196 7.34558 1.52338 7.39245 1.47651 7.45103C1.42964 7.50962 1.39449 7.5682 1.35934 7.62678C1.32419 7.69708 1.28904 7.75567 1.26561 7.82597C1.24218 7.89627 1.23046 7.97828 1.21874 8.04858C1.21874 8.09545 1.18359 8.14232 1.18359 8.2009V31.6342C1.18359 33.5675 2.76534 35.1492 4.69859 35.1492H10.5569C11.2013 35.1492 11.7286 34.622 11.7286 33.9776C11.7286 33.3331 11.2013 32.8059 10.5569 32.8059H4.69859C4.05418 32.8059 3.52693 32.2786 3.52693 31.6342V15.0669C4.54628 16.7423 5.96399 18.2187 7.93239 18.7108C8.02613 18.7342 8.11986 18.7459 8.21359 18.7459H33.9903C34.084 18.7459 34.1777 18.7342 34.2715 18.7108C36.2399 18.2187 37.6576 16.7541 38.6769 15.0669V31.6342C38.6769 32.2786 38.1497 32.8059 37.5053 32.8059H31.6469C31.0025 32.8059 30.4753 33.3331 30.4753 33.9776C30.4753 34.622 31.0025 35.1492 31.6469 35.1492H37.5053C39.4385 35.1492 41.0203 33.5675 41.0203 31.6342V8.2009C41.0203 8.2009 40.9968 8.10717 40.9851 8.04858H40.9734ZM21.0902 3.51423C23.2695 3.51423 25.109 5.01397 25.6246 7.02923H16.5441C17.0714 5.01397 18.8992 3.51423 21.0785 3.51423H21.0902ZM33.8262 16.4026H8.35419C5.77653 15.6527 4.34709 11.8331 3.76126 9.37257H38.4074C37.8216 11.8331 36.3922 15.6527 33.8145 16.4026H33.8262ZM27.6515 35.3601C28.6826 33.9893 29.2919 32.3021 29.2919 30.4626C29.2919 25.9399 25.6128 22.2609 21.0902 22.2609C16.5676 22.2609 12.8885 25.9399 12.8885 30.4626C12.8885 34.9852 16.5676 38.6642 21.0902 38.6642C22.9297 38.6642 24.6286 38.0433 25.9878 37.0239L29.6317 40.6678C29.866 40.9021 30.1589 41.0076 30.4635 41.0076C30.7682 41.0076 31.0611 40.8904 31.2954 40.6678C31.7524 40.2108 31.7524 39.4727 31.2954 39.0157L27.6515 35.3718V35.3601ZM15.2319 30.4626C15.2319 27.2288 17.8564 24.6042 21.0902 24.6042C24.324 24.6042 26.9485 27.2288 26.9485 30.4626C26.9485 32.0795 26.2924 33.544 25.2379 34.5985C24.1717 35.653 22.7188 36.3092 21.1019 36.3092C17.8681 36.3092 15.2436 33.6847 15.2436 30.4508L15.2319 30.4626Z" fill="#0080FF"/>
                                            </svg>
                                        </div>
                                        <div class="arrow">
                                            <svg width="150" height="21" viewBox="0 0 150 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M139 1L148.339 10.2258L139.071 19.4516" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <line x1="1.00683" y1="9.00687" x2="147.007" y2="10.0069" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-dasharray="10 10"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="d-block px-3 align-items-center">
                                        <h4 class="title">{{ __("common/welcome.post_a_job_or_search_for_opportunities") }}</h4>
                                        <p>{{ __("common/landing.post_paragraph") }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4 p-3 rounded-3">
                                    <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                        <div class="icon">
                                            <svg width="37" height="37" viewBox="0 0 37 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3066_40138)">
                                                    <mask id="mask0_3066_40138" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="37" height="37">
                                                        <path d="M0 3.8147e-06H37V37H0V3.8147e-06Z" fill="white"/>
                                                    </mask>
                                                    <g mask="url(#mask0_3066_40138)">
                                                        <path d="M5.41992 8.38281C5.41992 11.5757 8.00826 14.1641 11.2012 14.1641C14.3941 14.1641 16.9824 11.5757 16.9824 8.38281C16.9824 5.1899 14.3941 2.60156 11.2012 2.60156C8.00826 2.60156 5.41992 5.1899 5.41992 8.38281Z" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M24.2812 12.7188C24.2812 15.1134 26.2225 17.0547 28.6172 17.0547C31.0119 17.0547 32.9531 15.1134 32.9531 12.7188C32.9531 10.3241 31.0119 8.38281 28.6172 8.38281C26.2225 8.38281 24.2812 10.3241 24.2812 12.7188Z" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M1.08398 22.1133V17.0547C1.08398 15.4582 2.37812 14.1641 3.97461 14.1641H18.4277C20.0242 14.1641 21.3184 15.4582 21.3184 17.0547V22.1133H23.4863C24.6836 22.1133 25.6543 23.084 25.6543 24.2813C25.6543 25.4785 24.6836 26.4492 23.4863 26.4492H21.3184V34.3984H13.3691V32.2305C13.3691 31.0332 12.3985 30.0625 11.2012 30.0625C10.0039 30.0625 9.0332 31.0332 9.0332 32.2305V34.3984H1.08398V26.4492H3.25195C4.44925 26.4492 5.41992 25.4785 5.41992 24.2813C5.41992 23.084 4.44925 22.1133 3.25195 22.1133H1.08398Z" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M21.3184 22.1133V17.0547H33.748C34.9453 17.0547 35.916 18.0254 35.916 19.2227V31.5078H21.3184V26.4492" stroke="#0080FF" stroke-width="2.16797" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </g>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_3066_40138">
                                                        <rect width="37" height="37" fill="white"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                        <div class="arrow">
                                            <svg width="150" height="21" viewBox="0 0 150 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M139 1L148.339 10.2258L139.071 19.4516" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <line x1="1.00683" y1="9.00687" x2="147.007" y2="10.0069" stroke="#0080FF" stroke-width="2" stroke-linecap="round" stroke-dasharray="10 10"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="d-block px-3 align-items-center">
                                        <h4 class="title">{{ __("common/welcome.find_the_perfect_match") }}</h4>
                                        <p>{{ __("common/landing.perfect_match_paragraph") }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4 p-3 rounded-3">
                                    <div class="d-flex w-100 justify-content-between align-items-center gap-3 mb-3">
                                        <div class="icon">
                                            <svg width="39" height="38" viewBox="0 0 39 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_3066_40154)">
                                                    <path d="M26.6741 3.89271C26.6741 1.74393 28.418 0 30.5668 0C32.7156 0 34.4595 1.74393 34.4595 3.89271C34.4595 6.04148 32.7156 7.78542 30.5668 7.78542C28.418 7.78542 26.6741 6.04148 26.6741 3.89271ZM38.3522 14.0137C38.3522 11.4446 36.2501 9.3425 33.681 9.3425H27.4526C26.8298 9.3425 26.2381 9.46707 25.6931 9.68506L28.4647 12.4567H33.681C34.5373 12.4567 35.238 13.1574 35.238 14.0137V23.3562H30.0218L25.8955 27.4825V37.37H29.0097V26.4704H32.1239V37.37H35.238V26.4704H38.3522V14.0137ZM8.5652 7.78542C10.714 7.78542 12.4579 6.04148 12.4579 3.89271C12.4579 1.74393 10.714 0 8.5652 0C6.41643 0 4.67249 1.74393 4.67249 3.89271C4.67249 6.04148 6.41643 7.78542 8.5652 7.78542ZM27.9042 16.4895L23.3575 11.9428L21.162 14.1383L24.1516 17.1123H14.8091L17.7831 14.1383L15.5876 11.9428L11.041 16.4895C9.84201 17.6885 9.84201 19.666 11.041 20.8649L15.5721 25.396L17.7676 23.2005L14.7935 20.2421H24.136L21.1776 23.2005L23.3731 25.396L27.9042 20.8649C29.1031 19.6504 29.1031 17.6885 27.9042 16.4895ZM3.89395 23.3562V14.0137C3.89395 13.1574 4.59464 12.4567 5.45104 12.4567H10.6673L13.4389 9.68506C12.8939 9.46707 12.3022 9.3425 11.6794 9.3425H5.45104C2.88185 9.3425 0.779785 11.4446 0.779785 14.0137V26.4704H3.89395V37.37H7.00812V26.4704H10.1223V37.37H13.2365V27.4825L9.11018 23.3562H3.89395Z" fill="#0080FF"/>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_3066_40154">
                                                        <rect width="37.37" height="37.37" fill="white" transform="translate(0.779785)"/>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="d-block px-3 align-items-center">
                                        <h4 class="title">{{ __("common/welcome.start_collaborating_jobs") }}</h4>
                                        <p>{{ __("common/landing.start_collab_paragraph") }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!---- Explore by Location -->

                <section id="featuredSection">
                    <div class="container mb-5">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md-5">
                                <h3 class="section-title">{!! __("common/welcome.featured_jobs_and_projects") !!}</h3>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route("project.index") }}" class="btn btn-outline-primary">{{ __("common/welcome.view_all") }} <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        @foreach(\App\Models\Project::limit(5)->get() as $project)
                            <div class="row justify-content-between bg-white rounded-3 align-items-center list-job">
                                <div class="col-md-6 justify-content-start align-items-start py-3 d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="{{ __("https://picsum.photos/42/42?random={$project->id}") }}" alt=""/>
                                    </div>
                                    <div class="flex-grow-1 ms-3 flex-column justify-content-start align-items-start gap-3.5 d-inline-flex">
                                        <div class="Heading justify-content-start align-items-center gap-2 d-inline-flex mb-2">
                                            <div class="main-heading">{{ $project->title ?? ''  }}</div>
                                            <div class="badge">
                                                <div class="main-pill text-sky-500 text-sm font-medium leading-tight">{{ __("common/welcome.contract_base") }}</div>
                                            </div>
                                        </div>
                                        <div class="Info justify-content-start align-items-center gap-4 d-inline-flex">
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
                                <div class="col-md-6 justify-content-end align-items-center gap-3 d-flex">
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
                            <div class="col-md-5 text-center my-3">
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
                                            <div class="col-md-4 px-3">
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
                                        <div class="btn btn-primary">{{ __("common/landing.register_now") }} <i class="fa-solid fa-arrow-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="search-card bg-blue rounded-3">
                                    <div class="search-card-title">{{ __("common/landing.search_for_a_talent") }}</div>
                                    <p class="w-50 mt-3">{{ __("common/landing.dummy_text") }}</p>
                                    <div class="">
                                        <div class="btn btn-light text-primary">{{ __("common/landing.register_now") }} <i class="fa-solid fa-arrow-right"></i></div>
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
