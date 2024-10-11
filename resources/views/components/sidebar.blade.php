<div class="col-md-3 col-xl-3 mb-5" id="sidebar">
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-light text-muted">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-muted text-decoration-none">
            <svg class="bi pe-none me-2" width="40" height="32"><use xlink:href="#bootstrap"></use></svg>
            <span class="fs-4 mb-3">{{ __("common/sidebar.search") }}</span>
        </a>
        <div class="input-group mb-3">
            <span class="input-group-text" id="search-keyword"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" class="form-control search-input" placeholder="{{ __("common/sidebar.search_with_keyword") }}" aria-label="Username" aria-describedby="search-keyword">
        </div>
        <div class="participation">
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            {{ __("common/sidebar.categories") }}
                        </button>
                    </h2>
                    <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @foreach(\App\Models\Category::all() as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $category->id }}" id="{{ $category->id }}">
                                    <label class="form-check-label" for="{{ $category->id }}">
                                        {{ $category->title ?? __('something') }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                            {{ __("common/sidebar.monthly_price") }}
                        </button>
                    </h2>
                    <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="{{ __("common/sidebar.monthly_price_placeholder", ["currency" => 'Jpy']) }}" aria-label="budget" aria-describedby="search-budget">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                            {{ __("common/sidebar.work_location") }}
                        </button>
                    </h2>
                    <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="e.g. Tokyo" aria-label="budget" aria-describedby="search-location">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="from_home">
                                <label class="form-check-label" for="from_home">
                                    {{ __('common/sidebar.work_from_home') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="2" id="from_home">
                                <label class="form-check-label" for="from_home">
                                    {{ __('common/sidebar.part_time') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
                            {{ __("common/sidebar.contract_period") }}
                        </button>
                    </h2>
                    <div id="flush-collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="input-group mb-3">
                                <input type="date" class="form-control" placeholder="{{ __("common/sidebar.start_date") }}" aria-label="budget" aria-describedby="search-starting">
                            </div>
                            <div class="input-group mb-3">
                                <input type="date" class="form-control" placeholder="{{ __("common/sidebar.end_date") }}" aria-label="budget" aria-describedby="search-ending">
                            </div>

                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
                            {{ __("common/sidebar.project_status") }}
                        </button>
                    </h2>
                    <div id="flush-collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="1">
                                <label class="form-check-label" for="1">
                                    {{ __('common/sidebar.confirmed') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="2" id="2">
                                <label class="form-check-label" for="2">
                                    {{ __('common/sidebar.before_confirm') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSix" aria-expanded="false" aria-controls="flush-collapseSix">
                            {{ __("common/sidebar.project_flow") }}
                        </button>
                    </h2>
                    <div id="flush-collapseSix" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="1">
                                <label class="form-check-label" for="1">
                                    {{ __('common/sidebar.end') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="2" id="2">
                                <label class="form-check-label" for="2">
                                    {{ __('common/sidebar.prime_contractor') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="3" id="3">
                                <label class="form-check-label" for="3">
                                    {{ __('common/sidebar.secondary_claim') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="4" id="4">
                                <label class="form-check-label" for="4">
                                    {{ __('common/sidebar.third_subsequent_client') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--        <div class="participation">--}}
{{--            <h3 class="title">Possible Participation Period</h3>--}}
{{--        </div>--}}
{{--        <ul class="nav nav-pills flex-column mb-auto">--}}
{{--            <li class="nav-item">--}}
{{--                <a href="#" class="nav-link active" aria-current="page">--}}
{{--                    <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#home"></use></svg>--}}
{{--                    Home--}}
{{--                </a>--}}
{{--            </li>--}}
{{--            <li>--}}
{{--                <a href="#" class="nav-link text-muted">--}}
{{--                    <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#speedometer2"></use></svg>--}}
{{--                    Dashboard--}}
{{--                </a>--}}
{{--            </li>--}}
{{--            <li>--}}
{{--                <a href="#" class="nav-link text-muted">--}}
{{--                    <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#table"></use></svg>--}}
{{--                    Orders--}}
{{--                </a>--}}
{{--            </li>--}}
{{--            <li>--}}
{{--                <a href="#" class="nav-link text-muted">--}}
{{--                    <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#grid"></use></svg>--}}
{{--                    Products--}}
{{--                </a>--}}
{{--            </li>--}}
{{--            <li>--}}
{{--                <a href="#" class="nav-link text-muted">--}}
{{--                    <svg class="bi pe-none me-2" width="16" height="16"><use xlink:href="#people-circle"></use></svg>--}}
{{--                    Customers--}}
{{--                </a>--}}
{{--            </li>--}}
{{--        </ul>--}}
{{--        <hr>--}}
{{--        <div class="dropdown">--}}
{{--            <a href="#" class="d-flex align-items-center text-muted text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">--}}
{{--                <img src="https://github.com/mdo.png" alt="" class="rounded-circle me-2" width="32" height="32">--}}
{{--                <strong>{{ Auth::user()->username }}</strong>--}}
{{--            </a>--}}
{{--            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">--}}
{{--                <li><a class="dropdown-item" href="#">New project...</a></li>--}}
{{--                <li><a class="dropdown-item" href="#">Settings</a></li>--}}
{{--                <li><a class="dropdown-item" href="#">Profile</a></li>--}}
{{--                <li><hr class="dropdown-divider"></li>--}}
{{--                <li><a class="dropdown-item" href="#">Sign out</a></li>--}}
{{--            </ul>--}}
{{--        </div>--}}
    </div>
{{--    <div class="card">--}}
{{--        <div class="card-header">{{ __('Dashboard') }}</div>--}}

{{--        <div class="card-body">--}}
{{--            @if (session('status'))--}}
{{--                <div class="alert alert-success" role="alert">--}}
{{--                    {{ session('status') }}--}}
{{--                </div>--}}
{{--            @endif--}}

{{--            {{ Auth::user()->username ?? __('You are logged in!') }}--}}
{{--        </div>--}}
{{--    </div>--}}
</div>
