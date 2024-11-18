@extends('layouts.app')

@section('title', $project->title)

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12 px-4 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb pt-3">
                        <li class="breadcrumb-item"><a href="#">{{ __("projects/show.home") }}</a></li>
                        <li class="breadcrumb-item"><a href="#">{{ __("projects/show.jobs") }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $project->title }}</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-end align-items-center">
                    <a href="">
                        <svg width="43" height="43" viewBox="0 0 43 43" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32.25 37.625L21.4988 30.9062L10.75 37.625V8.0625C10.75 7.70612 10.8916 7.36433 11.1436 7.11233C11.3956 6.86032 11.7374 6.71875 12.0938 6.71875H30.9062C31.2626 6.71875 31.6044 6.86032 31.8564 7.11233C32.1084 7.36433 32.25 7.70612 32.25 8.0625V37.625Z" stroke="#0080FF" stroke-width="1.78125" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="">
                        <svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_2643_31718)">
                                <path d="M25.9766 0H9.02344C6.63027 0 4.33513 0.950681 2.6429 2.6429C0.950681 4.33513 0 6.63027 0 9.02344L0 25.9766C0 28.3697 0.950681 30.6649 2.6429 32.3571C4.33513 34.0493 6.63027 35 9.02344 35H25.9766C28.3697 35 30.6649 34.0493 32.3571 32.3571C34.0493 30.6649 35 28.3697 35 25.9766V9.02344C35 6.63027 34.0493 4.33513 32.3571 2.6429C30.6649 0.950681 28.3697 0 25.9766 0ZM14.5701 17.5C14.57 17.7366 14.5471 17.9726 14.5018 18.2048L21.3719 21.6398C21.9648 20.9822 22.7761 20.5618 23.6553 20.4565C24.5345 20.3513 25.4221 20.5682 26.1535 21.0673C26.885 21.5663 27.4108 22.3135 27.6335 23.1706C27.8562 24.0276 27.7607 24.9363 27.3647 25.7283C26.9687 26.5203 26.2991 27.1419 25.4798 27.478C24.6606 27.814 23.7473 27.8418 22.9092 27.556C22.0711 27.2702 21.3649 26.6904 20.9216 25.9238C20.4783 25.1573 20.3279 24.2561 20.4982 23.3871L13.6281 19.9521C13.1323 20.502 12.4815 20.8887 11.7615 21.0612C11.0414 21.2337 10.286 21.1839 9.59483 20.9184C8.90367 20.6528 8.30924 20.184 7.88996 19.5737C7.47067 18.9635 7.24623 18.2404 7.24623 17.5C7.24623 16.7596 7.47067 16.0365 7.88996 15.4263C8.30924 14.816 8.90367 14.3472 9.59483 14.0816C10.286 13.8161 11.0414 13.7663 11.7615 13.9388C12.4815 14.1113 13.1323 14.498 13.6281 15.0479L20.4982 11.6129C20.3278 10.7436 20.4782 9.84198 20.9216 9.07509C21.365 8.30821 22.0714 7.72806 22.9098 7.44214C23.7483 7.15621 24.6619 7.1839 25.4815 7.52008C26.3011 7.85625 26.971 8.47812 27.3672 9.27045C27.7634 10.0628 27.8589 10.9719 27.6361 11.8292C27.4133 12.6866 26.8872 13.4341 26.1554 13.9333C25.4236 14.4325 24.5357 14.6495 23.6561 14.5442C22.7766 14.4388 21.965 14.0181 21.3719 13.3602L14.5018 16.7952C14.5471 17.0274 14.57 17.2634 14.5701 17.5Z" fill="#0080FF"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_2643_31718">
                                    <rect width="35" height="35" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="col-md-12 job-banner">
                <div class="row ">
                    <div class="col-md-8">
                        <div class="job-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span>(90)</span>
                        </div>
                        <div class="job-title">
                            <h1>{{ $project->title }}</h1>
                        </div>
                        <div class="d-flex flex-wrap gap-2 my-2">
                            <div class="mb-2 job-timestamp"><i class="fa-solid fa-calendar-days"></i> {{ __("common/home.registered_on") }}: <span>{{ $project->created_at->format('M d, Y') }}</span></div>
                            <div class="mb-2 job-timestamp"><i class="fa-solid fa-rotate"></i> {{ __("common/home.updated_on") }}: <span>{{ $project->updated_at->format('M d, Y') }}</span></div>
                            <div class="mb-2 job-timestamp"><i class="fa-regular fa-hourglass-half"></i> {{ __("common/home.duration") }}: <span>{{ $project->deadline->format('M d, Y') }}</span></div>
                        </div>
                        <div class="d-flex gap-2 user-data">
                            <div class="avatar-view me-3">
                                <img src="{{ $project->company->company_logo_url ?? 'https://picusm.photos/32/32' }}" alt="" class="img-fluid" height="32" width="32"/>
                                <span class="online"></span>
                            </div>
                            <h6 class="mt-3">{{ $project->company->company_name ?? Auth::user()->name }}</h6>
                        </div>
                    </div>
                    <div class="col-md-4 pt-5">
                        <img src="{{ asset("images/project-detail-hero-image.png") }}" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
            <div class="col-md-8 job-detail">
                <div class="my-3 job-highlights">
                    <div class="row justify-content-center align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="icon">
                                        <i class="fa-solid fa-stopwatch-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 align-items-center ms-3">
                                    <h5 class="main-title">{{ __("projects/show.contract_period") }}</h5>
                                    <h4 class="main-sub-title">{{ $project->contract_end_date->diffForHumans() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="icon">
                                        <i class="fa-solid fa-wallet"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 align-items-center ms-3">
                                    <h5 class="main-title">{{ __("projects/show.monthly_salary") }}</h5>
                                    <h4 class="main-sub-title">{{ $project->salary_range }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="icon">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 align-items-center ms-3">
                                    <h5 class="main-title">{{ __("projects/show.location") }}</h5>
                                    <h4 class="main-sub-title">{{ $project->locations->first()->title ?? __("common/home.japan_tokyo") }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="job-description gap-3">
                    <h5>{{ __("projects/show.project_id") }}</h5>
                    <p>{{ $project->project_id }}</p>
                    <h3 class="heading-1">{{ __("projects/show.project_description") }}</h3>
                    <div class="d-block">
                        {!! \Illuminate\Support\Str::limit($project->project_description, 200) ?? '<p>Integer aliquet pretium consequat. Donec et sapien id leo accumsan pellentesque eget maximus tellus. Duis et est ac leo rhoncus tincidunt vitae vehicula augue. Donec in suscipit diam. Pellentesque quis justo sit amet arcu commodo sollicitudin. Integer finibus blandit condimentum. Vivamus sit amet ligula ullamcorper, pulvinar ante id, tristique erat. Quisque sit amet aliquam urna. Maecenas blandit felis id massa sodales finibus. Integer bibendum eu nulla eu sollicitudin. Sed lobortis diam tincidunt accumsan faucibus. Quisque blandit augue quis turpis auctor, dapibus euismod ante ultricies. Ut non felis lacinia turpis feugiat euismod at id magna. Sed ut orci arcu. Suspendisse sollicitudin faucibus aliquet.</p>
                        <p>Nam dapibus consectetur erat in euismod. Cras urna augue, mollis venenatis augue sed, porttitor aliquet nibh. Sed tristique dictum elementum. Nulla imperdiet sit amet quam eget lobortis. Etiam in neque sit amet orci interdum tincidunt.</p>
                        <ul>
                            Responsibilities
                            <li>Quisque semper gravida est et consectetur.</li>
                            <li>Curabitur blandit lorem velit, vitae pretium leo placerat eget.</li>
                            <li>Morbi mattis in ipsum ac tempus.</li>
                            <li>Curabitur eu vehicula libero. Vestibulum sed purus ullamcorper, lobortis lectus nec.</li>
                            <li>lobortis vel lectus. Nulla at risus ut diam.</li>
                        </ul>' !!}
                    </div>
                    <div class="skill-required">
                        <h4>{{ __("projects/show.skill_requirements") }}</h4>
                        <div class="d-flex flex-wrap w-100 gap-3">
                            @foreach($project->subCategories as $subcategory)
                                <a href="#" class="skill">{{ $subcategory->title }}</a>
                            @endforeach
                        </div>
                    </div>
                    @if($project->affiliation)
                        <div class="skill-required">
                            <h4>{{ __("projects/show.eligible_applicants") }}</h4>
                            <div class="d-flex flex-wrap w-100 gap-3">
                                @foreach($project->affiliation as $eligibility)
                                    <a href="#" class="skill">{{ __("projects/form.affiliation_{$eligibility}") }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="d-flex gap-3 sharing-jobs">
                        {{ __("projects/show.share_project") }}:
                        <a href="" class="social-search facebook"><i class="fa-brands fa-facebook-f"></i> {{ __("projects/show.facebook") }}</a>
                        <a href="" class="social-search pinterest"><i class="fa-brands fa-pinterest"></i> {{ __("projects/show.pinterest") }}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="job-right-sidebar">
                    <div class="d-grid gap-2">
                        <a href="#applyToProject" class="btn btn-primary">{{ __("projects/show.apply_to_project") }}</a>
                    </div>
                    <div class="requirements">
                        <h4 class="requirement-heading">{{ __("projects/show.project_requirements") }}</h4>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="requirement-icon">
                                    <svg width="35" height="34" viewBox="0 0 35 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_2643_31910)">
                                            <path d="M5.75645 0C2.96075 0 0.664551 2.3128 0.664551 5.10843V10.2003C0.664551 12.9961 2.96068 15.3054 5.75645 15.3054H10.8483C13.6441 15.3054 15.9568 12.9961 15.9568 10.2003V5.10843C15.9568 2.31273 13.6441 0 10.8483 0H5.75645ZM24.4476 0C21.6519 0 19.3557 2.3128 19.3557 5.10843V10.2003C19.3557 12.9961 21.6518 15.3054 24.4476 15.3054H29.556C32.3518 15.3054 34.648 12.9961 34.648 10.2003V5.10843C34.648 2.31273 32.3518 0 29.556 0H24.4476ZM5.75645 3.40232H10.8483C11.8071 3.40232 12.5578 4.14979 12.5578 5.10843V10.2003C12.5578 11.1591 11.8071 11.9064 10.8483 11.9064H5.75645C4.79768 11.9064 4.06355 11.159 4.06355 10.2003V5.10843C4.06355 4.14979 4.79768 3.40232 5.75645 3.40232ZM24.4476 3.40232H29.556C30.5148 3.40232 31.2489 4.14979 31.2489 5.10843V10.2003C31.2489 11.1591 30.5148 11.9064 29.556 11.9064H24.4476C23.4888 11.9064 22.7547 11.159 22.7547 10.2003V5.10843C22.7548 4.14979 23.4889 3.40232 24.4476 3.40232ZM5.75645 18.7078C2.96075 18.7078 0.664551 21.004 0.664551 23.7997V28.8949C0.664551 31.6906 2.96068 34 5.75645 34H10.8483C13.6441 34 15.9568 31.6905 15.9568 28.8949V23.7997C15.9568 21.004 13.6441 18.7078 10.8483 18.7078H5.75645ZM24.4476 18.7078C21.6519 18.7078 19.3557 21.004 19.3557 23.7997V28.8949C19.3557 31.6906 21.6518 34 24.4476 34H29.556C32.3518 34 34.648 31.6905 34.648 28.8949V23.7997C34.648 21.004 32.3518 18.7078 29.556 18.7078H24.4476ZM5.75645 22.1068H10.8483C11.8071 22.1068 12.5578 22.8409 12.5578 23.7996V28.8948C12.5578 29.8536 11.8071 30.6009 10.8483 30.6009H5.75645C4.79768 30.6009 4.06355 29.8535 4.06355 28.8948V23.7997C4.06355 22.8409 4.79768 22.1068 5.75645 22.1068ZM24.4476 22.1068H29.556C30.5148 22.1068 31.2489 22.8409 31.2489 23.7996V28.8948C31.2489 29.8536 30.5148 30.6009 29.556 30.6009H24.4476C23.4888 30.6009 22.7547 29.8535 22.7547 28.8948V23.7997C22.7548 22.8409 23.4889 22.1068 24.4476 22.1068Z" fill="#0080FF"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_2643_31910">
                                                <rect width="34" height="34" fill="white" transform="translate(0.65625)"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-grow-1 align-items-center ms-3">
                                <h5 class="requirement-title">{{ __("projects/show.project_category") }}</h5>
                                <h4 class="requirement-sub-title">{{ $project->subcategories->first()->category->title ?? '' }}</h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="requirement-icon">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_2643_31922)">
                                            <path d="M30.652 0H9.34758C6.76617 0 4.66602 2.10016 4.66602 4.68156V35.3185C4.66602 37.8999 6.76617 40.0001 9.34758 40.0001H30.652C33.2334 40.0001 35.3335 37.8999 35.3335 35.3185V4.68156C35.3335 2.10016 33.2334 0 30.652 0ZM23.573 3.12102V4.63375C23.573 5.46789 22.8945 6.14648 22.0603 6.14648H17.9391C17.1049 6.14648 16.4263 5.46789 16.4263 4.63375V3.12102H23.573ZM32.2125 35.3184C32.2125 36.1789 31.5125 36.879 30.652 36.879H9.34758C8.48711 36.879 7.78703 36.179 7.78703 35.3184V4.68156C7.78703 3.82109 8.48711 3.12102 9.34758 3.12102H13.3054V4.63375C13.3054 7.18875 15.3841 9.2675 17.9391 9.2675H22.0604C24.6154 9.2675 26.6941 7.18875 26.6941 4.63375V3.12102H30.652C31.5125 3.12102 32.2126 3.82102 32.2126 4.68156V35.3185L32.2125 35.3184Z" fill="#0080FF"/>
                                            <path d="M25.2347 16.6269L17.4322 24.4295L14.7646 21.7619C14.1551 21.1524 13.1671 21.1526 12.5577 21.7619C11.9482 22.3714 11.9482 23.3594 12.5577 23.9688L16.3288 27.7399C16.6335 28.0446 17.0328 28.1969 17.4322 28.1969C17.8316 28.1969 18.231 28.0445 18.5356 27.7399L27.4417 18.834C28.051 18.2245 28.051 17.2365 27.4417 16.627C26.8322 16.0176 25.8442 16.0176 25.2347 16.627V16.6269Z" fill="#0080FF"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_2643_31922">
                                                <rect width="40" height="40" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-grow-1 align-items-center ms-3">
                                <h5 class="requirement-title">{{ __('projects/show.project_status') }}</h5>
                                <h4 class="requirement-sub-title">{{ $project->project_status ?? __("projects/show.confirmed") }}</h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="requirement-icon">
                                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_2643_31932)">
                                            <path d="M23.1045 15.026C23.2935 14.7918 23.3935 14.3719 23.3263 14.0741C22.9218 12.2666 21.6723 10.8633 20.0508 10.2059C20.7983 9.60041 21.2865 8.68688 21.2865 7.65182C21.2865 6.30614 20.4099 5.05492 19.1561 4.57992C18.3847 4.28767 17.5386 4.29423 16.7727 4.59697C15.5376 5.08518 14.6864 6.32371 14.6864 7.65181C14.6864 8.68687 15.1745 9.6004 15.9221 10.2058C14.3006 10.8633 13.0511 12.2666 12.6466 14.0741C12.581 14.3673 12.6766 14.7889 12.8683 15.026C14.1265 16.5849 15.9928 17.4796 17.9864 17.4796C19.9801 17.4796 21.8464 16.5849 23.1045 15.026ZM17.9864 6.60009C18.5661 6.60009 19.0382 7.07216 19.0382 7.65182C19.0382 8.23148 18.5661 8.70355 17.9864 8.70355C17.4068 8.70355 16.9347 8.23148 16.9347 7.65182C16.9347 7.07216 17.4068 6.60009 17.9864 6.60009ZM17.9864 12.0399C19.3084 12.0399 20.4578 12.8271 20.9543 14.0113C20.1521 14.779 19.1076 15.2313 17.9864 15.2313C16.8653 15.2313 15.8208 14.779 15.0186 14.0113C15.5151 12.8271 16.6644 12.0399 17.9864 12.0399Z" fill="#0080FF"/>
                                            <path d="M31.0419 25.0218C30.1147 25.0218 29.2401 25.3216 28.514 25.8381L22.5179 20.8436C26.3578 19.107 28.9022 15.2782 28.9022 10.9158C28.9022 4.89634 24.0059 0 17.9864 0C11.967 0 7.07068 4.89634 7.07068 10.9158C7.07068 15.2782 9.61505 19.107 13.455 20.8436L7.45726 25.8395C6.72829 25.3209 5.85579 25.0218 4.93101 25.0218C2.51139 25.0218 0.542969 26.9902 0.542969 29.4098C0.542969 31.8295 2.51139 33.7979 4.93101 33.7979C7.35063 33.7979 9.31905 31.8295 9.31905 29.4098C9.31905 28.7645 9.16172 28.1392 8.89562 27.5673L16.0214 21.6318C16.3015 21.6832 16.5809 21.7344 16.8623 21.7637V27.362C14.9891 27.8621 13.5984 29.5571 13.5984 31.5858C13.5984 34.0054 15.5668 35.9738 17.9864 35.9738C20.4061 35.9738 22.3745 34.0054 22.3745 31.5858C22.3745 29.5571 20.9839 27.862 19.1106 27.362V21.7637C19.392 21.7344 19.6713 21.6832 19.9514 21.6318L27.0782 27.5681C26.8107 28.142 26.6538 28.7661 26.6538 29.4099C26.6538 31.8295 28.6222 33.7979 31.0419 33.7979C33.4615 33.7979 35.4299 31.8295 35.4299 29.4099C35.4299 26.9902 33.4615 25.0218 31.0419 25.0218ZM9.31905 10.9158C9.31905 6.13688 13.2076 2.24836 17.9864 2.24836C22.7653 2.24836 26.6538 6.13688 26.6538 10.9158C26.6538 14.9481 23.9224 18.4129 20.0108 19.3416C18.6693 19.66 17.3036 19.66 15.962 19.3416C12.0505 18.4129 9.31905 14.9481 9.31905 10.9158ZM4.93101 31.5495C3.75084 31.5495 2.79133 30.59 2.79133 29.4098C2.79133 28.2297 3.75081 27.2702 4.93096 27.2702C6.08137 27.2701 7.07068 28.2644 7.07069 29.4098C7.07068 30.59 6.11117 31.5495 4.93101 31.5495ZM20.1261 31.5858C20.1261 32.7659 19.1666 33.7254 17.9864 33.7254C16.8063 33.7254 15.8468 32.7659 15.8468 31.5858C15.8468 30.4056 16.8063 29.4461 17.9864 29.4461C19.1666 29.4461 20.1261 30.4056 20.1261 31.5858ZM31.0419 31.5495C29.8617 31.5495 28.9022 30.59 28.9022 29.4099C28.9022 28.2497 29.891 27.2702 31.0419 27.2702C32.222 27.2702 33.1815 28.2297 33.1815 29.4098C33.1815 30.59 32.222 31.5495 31.0419 31.5495Z" fill="#0080FF"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_2643_31932">
                                                <rect width="35.9738" height="35.9738" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-grow-1 align-items-center ms-3">
                                <h5 class="requirement-title">{{ __("projects/show.project_flow") }}</h5>
                                <h4 class="requirement-sub-title">{{ __("projects/show.{$project->project_flow}") }}</h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="requirement-icon">
                                    <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.82361 0H30.1765C32.0283 0 33.5294 1.50115 33.5294 3.35291V34.647C33.5294 36.4988 32.0283 37.9999 30.1765 37.9999H7.82361C5.97185 37.9999 4.4707 36.4988 4.4707 34.647V3.35291C4.47063 1.50115 5.97185 0 7.82361 0ZM7.82361 2.23532C7.20633 2.23532 6.70595 2.7357 6.70595 3.35298V34.6471C6.70595 35.2644 7.20633 35.7648 7.82361 35.7648H30.1765C30.7938 35.7648 31.2942 35.2644 31.2942 34.6471V3.35291C31.2942 2.73563 30.7938 2.23525 30.1765 2.23525L7.82361 2.23532ZM11.1765 18.4412C10.5592 18.4412 10.0589 17.9408 10.0589 17.3235C10.0589 16.7063 10.5592 16.2059 11.1765 16.2059H26.8236C27.4409 16.2059 27.9413 16.7063 27.9413 17.3235C27.9413 17.9408 27.4409 18.4412 26.8236 18.4412H11.1765ZM11.1765 13.9706C10.5592 13.9706 10.0589 13.4702 10.0589 12.8529C10.0589 12.2356 10.5592 11.7352 11.1765 11.7352H26.8236C27.4409 11.7352 27.9413 12.2356 27.9413 12.8529C27.9413 13.4702 27.4409 13.9706 26.8236 13.9706H11.1765ZM11.1765 22.9118C10.5592 22.9118 10.0589 22.4114 10.0589 21.7941C10.0589 21.1768 10.5592 20.6765 11.1765 20.6765H19.0001C19.6173 20.6765 20.1177 21.1768 20.1177 21.7941C20.1177 22.4114 19.6173 22.9118 19.0001 22.9118H11.1765ZM16.2059 8.38234C15.5887 8.38234 15.0883 7.88196 15.0883 7.26468C15.0883 6.6474 15.5887 6.14702 16.2059 6.14702H21.7942C22.4115 6.14702 22.9118 6.6474 22.9118 7.26468C22.9118 7.88196 22.4115 8.38234 21.7942 8.38234H16.2059ZM10.8492 30.4079C10.4127 30.8444 9.70505 30.8444 9.26865 30.4079C8.83217 29.9715 8.83217 29.2638 9.26865 28.8274L10.9451 27.1509C11.4438 26.6522 12.2741 26.7344 12.6653 27.3213L13.1631 28.0678L13.9096 27.5701C14.3529 27.2746 14.9432 27.3331 15.3199 27.7098L16.1101 28.5H17.3236C17.9409 28.5 18.4413 29.0004 18.4413 29.6177C18.4413 30.2349 17.9409 30.7353 17.3236 30.7353H15.6472C15.3507 30.7353 15.0665 30.6175 14.8569 30.4079L14.3871 29.9382L13.4731 30.5476C12.9595 30.89 12.2655 30.7512 11.9232 30.2376L11.5617 29.6955L10.8492 30.4079ZM25.1472 32.9706C22.9867 32.9706 21.2354 31.2192 21.2354 29.0588C21.2354 26.8984 22.9867 25.147 25.1472 25.147C27.3076 25.147 29.0589 26.8984 29.0589 29.0588C29.0589 31.2192 27.3075 32.9706 25.1472 32.9706ZM25.1472 30.7353C26.073 30.7353 26.8236 29.9847 26.8236 29.0589C26.8236 28.133 26.073 27.3824 25.1472 27.3824C24.2213 27.3824 23.4707 28.133 23.4707 29.0589C23.4706 29.9847 24.2212 30.7353 25.1472 30.7353Z" fill="#0080FF"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-grow-1 align-items-center ms-3">
                                <h5 class="requirement-title">{{ __("projects/show.project_type") }}</h5>
                                <h4 class="requirement-sub-title">{{ __("talents/index.{$project->contract_type}") }}</h4>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="requirement-icon">
                                    <svg width="30" height="36" viewBox="0 0 30 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_2490_23331)">
                                            <path d="M7.49514 0C8.32296 0 8.99405 0.671084 8.99405 1.49891C8.99405 2.32672 8.32296 2.99782 7.49514 2.99782C5.83949 2.99782 4.49733 4.33998 4.49733 5.99563C4.49733 7.65128 5.83949 8.99345 7.49514 8.99345C8.32296 8.99345 8.99405 9.66454 8.99405 10.4924C8.99405 11.3202 8.32296 11.9913 7.49514 11.9913C4.18385 11.9913 1.49951 9.30693 1.49951 5.99563C1.49951 2.68433 4.18385 0 7.49514 0Z" fill="#0080FF"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.99512 5.99563C8.99512 2.68433 11.6795 0 14.9907 0C18.302 0 20.9864 2.68433 20.9864 5.99563C20.9864 9.30693 18.302 11.9913 14.9907 11.9913C11.6795 11.9913 8.99512 9.30693 8.99512 5.99563ZM14.9907 2.99782C13.3351 2.99782 11.9929 4.33998 11.9929 5.99563C11.9929 7.65128 13.3351 8.99345 14.9907 8.99345C16.6464 8.99345 17.9886 7.65128 17.9886 5.99563C17.9886 4.33998 16.6464 2.99782 14.9907 2.99782Z" fill="#0080FF"/>
                                            <path d="M20.9854 1.49891C20.9854 0.671084 21.6564 0 22.4843 0C25.7955 0 28.4799 2.68433 28.4799 5.99563C28.4799 9.30693 25.7955 11.9913 22.4843 11.9913C21.6564 11.9913 20.9854 11.3202 20.9854 10.4924C20.9854 9.66454 21.6564 8.99345 22.4843 8.99345C24.14 8.99345 25.4821 7.65128 25.4821 5.99563C25.4821 4.33998 24.14 2.99782 22.4843 2.99782C21.6564 2.99782 20.9854 2.32672 20.9854 1.49891Z" fill="#0080FF"/>
                                            <path d="M4.75407 16.4881C4.58835 16.9569 4.49819 17.4614 4.49819 17.987V26.9804C4.49819 27.8083 5.16928 28.4793 5.9971 28.4793C6.82491 28.4793 7.496 27.8083 7.496 26.9804V17.987C7.496 17.1591 8.1671 16.4881 8.99491 16.4881H20.9862C21.814 16.4881 22.4851 17.1591 22.4851 17.987V26.9804C22.4851 27.8083 23.1561 28.4793 23.984 28.4793C24.8118 28.4793 25.4829 27.8083 25.4829 26.9804V17.987C25.4829 17.4614 25.3927 16.9569 25.227 16.4881H25.4829C26.3107 16.4881 26.9818 17.1591 26.9818 17.987V26.9804C26.9818 27.8083 27.6529 28.4793 28.4807 28.4793C29.3086 28.4793 29.9796 27.8083 29.9796 26.9804V17.987C29.9796 15.5034 27.9664 13.4902 25.4829 13.4902H4.49819C2.01472 13.4902 0.00146484 15.5034 0.00146484 17.987V26.9804C0.00146484 27.8083 0.672548 28.4793 1.50037 28.4793C2.32819 28.4793 2.99928 27.8083 2.99928 26.9804V17.987C2.99928 17.1591 3.67037 16.4881 4.49819 16.4881H4.75407Z" fill="#0080FF"/>
                                            <path d="M19.4875 35.9737C20.3153 35.9737 20.9864 35.3026 20.9864 34.4748V25.4813C20.9864 24.6535 20.3153 23.9824 19.4875 23.9824C18.6596 23.9824 17.9886 24.6535 17.9886 25.4813V32.9759H11.9929V25.4813C11.9929 24.6535 11.3219 23.9824 10.494 23.9824C9.66621 23.9824 8.99512 24.6535 8.99512 25.4813V34.4748C8.99512 35.3026 9.66621 35.9737 10.494 35.9737H19.4875Z" fill="#0080FF"/>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_2490_23331">
                                                <rect width="29.9782" height="35.9738" fill="white"/>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-grow-1 align-items-center ms-3">
                                <h5 class="requirement-title">{{ __("common/home.no_of_interviews") }}</h5>
                                <h4 class="requirement-sub-title">{{ __("common/sidebar.interview_{$project->total_interviews}") }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                @if($project->features->count() >=1)
                    <div class="job-right-sidebar-features">
                        <ul class="check-list">
                            @foreach($project->features as $feature)
                                <li>{{ $feature->title }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            @if(Auth::user()->is_employer)
                <div class="col-md-12 my-3" id="applyToProject">
                    <livewire:projects.apply-for-project :project="$project"/>
                </div>
            @endif
        </div>
        <div class="row align-items-center similar-projects">
            <div class="col-md-12">
                <h4 class="you-may-like">{{ __("projects/show.project_you_may_like") }}</h4>
            </div>
            @foreach(\App\Models\Project::where('id', '!=', $project->id)->limit(3)->get() as $similar_project)
                <div class="col-md-4 mb-5">
                    <div class="project-card">
                        <a href="" class="add-to-favourite"><i class="fas fa-star"></i> </a>
                        <div class="project-card-body p-3">
                            <div class="d-md-flex justify-content-between">
                                <div class="d-inline-flex pe-2 project-payout-type">{{ __("common/home.fixed") }}</div>
                                <span class="project-date">{{ $similar_project->created_at->format('M d, Y') }}</span>
                            </div>
                            <h2 class="project-title">{{ $similar_project->title }}</h2>
                            <h6 class="project-budget">{{ __("projects/show.project_budget") }}: <span>{{ __("talents/index.currency_text") }}{{ $project->salary_range }}/{{ __("common/home.month") }}</span></h6>
                            <p class="project-overview">{{ \Illuminate\Support\Str::limit($similar_project->project_description, 180) }}</p>
                            <div class="d-flex gap-3">
                                <h5 class="project-location"><i class="fas fa-location-dot"></i> {{ __("common/home.japan_tokyo") }}</h5>
                                <h6 class="project-location">{{ __("projects/show.remote") }}</h6>
                            </div>
                            <div class="d-flex gap-2 my-2">
                                @foreach($similar_project->subCategories as $subcategory)
                                    <div class="project-skill">{{ $subcategory->title }}</div>
                                    @break($loop->iteration >= 3)
                                @endforeach
                                @if($similar_project->subCategories->count() > 3)
                                    <div class="project-skill more"> {{ $similar_project->subCategories->count() - 3 }}+</div>
                                @endif
                            </div>
                        </div>
                        <div class="project-card-footer">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="{{ $similar_project->company->company_logo_url }}" alt="..." class="img-avatar" height="32" width="32">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div class="avatar">
                                            <span class="text-break">{{ $similar_project->company->company_name ?? __("pro") }}</span>
                                            <i class="fa-regular fa-circle-check"></i>
                                        </div>
                                        <a href="{{ route("project.show", $similar_project) }}" class="btn btn-theme">{{ __("projects/show.project_detail") }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
