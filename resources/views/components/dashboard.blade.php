<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/admin.css')
</head>
<body class="m-0 bg-slate-50 font-['Inter'] text-slate-950 antialiased">
@php
    $company = Auth::user()->company;
    $unreadCount = Auth::user()->unreadNotifications->count();
    $navItems = [
        ['route' => 'dashboard', 'label' => __('admin/sidebar.dashboard'), 'icon' => '▦'],
        ['route' => 'messages', 'label' => __('admin/sidebar.messages'), 'icon' => '◌'],
        ['route' => 'company-profile', 'label' => __('admin/sidebar.company_profile'), 'icon' => '⌂'],
        ['route' => 'job-applicants', 'label' => __('admin/sidebar.all_applicants'), 'icon' => '♙'],
        ['route' => 'job-listing', 'label' => __('admin/sidebar.job_listings'), 'icon' => '□'],
    ];
@endphp
<div class="min-h-screen lg:grid lg:grid-cols-[16rem_1fr]">
    <aside class="hidden border-r border-slate-200 bg-white px-4 py-6 lg:block">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 text-lg font-bold tracking-tight text-slate-950 no-underline"><img src="{{ asset('images/logo-dark.png') }}" alt="{{ config('app.name') }}" class="h-9 max-w-28 object-contain"><span>{{ config('app.name') }}</span></a>
        <p class="mt-10 px-3 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Workspace</p>
        <nav class="mt-3 space-y-1" aria-label="Admin navigation">
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}" @class(['flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium no-underline transition', 'bg-sky-600 text-white shadow-sm' => request()->routeIs($item['route']), 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' => !request()->routeIs($item['route'])])><span class="w-4 text-center text-base" aria-hidden="true">{{ $item['icon'] }}</span>{{ $item['label'] }}@if($item['route'] === 'messages' && $unreadCount)<span class="ml-auto rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ $unreadCount }}</span>@endif</a>
            @endforeach
        </nav>
        <div class="mt-10 rounded-xl bg-slate-50 p-4"><p class="m-0 text-xs font-medium text-slate-500">Signed in as</p><p class="mb-0 mt-1 truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p></div>
    </aside>
    <div class="min-w-0">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm font-semibold text-slate-900 no-underline lg:hidden"><img src="{{ asset('images/logo-dark.png') }}" alt="" class="h-8 w-8 object-contain">{{ config('app.name') }}</a>
                <div class="hidden items-center gap-3 lg:flex"><img src="{{ $company?->company_logo_url ?? asset('images/logo-dark.png') }}" alt="" class="h-8 w-8 rounded-md object-cover"><span class="text-sm font-medium text-slate-700">{{ $company?->company_name ?? config('app.name') }}</span></div>
                <div class="flex items-center gap-3">
                    <details class="relative"><summary class="flex h-9 min-w-9 cursor-pointer list-none items-center justify-center rounded-md border border-slate-200 bg-white px-2 text-sm text-slate-600 hover:bg-slate-50">◌@if($unreadCount)<span class="ml-1 text-xs font-bold text-sky-600">{{ $unreadCount }}</span>@endif</summary><div class="absolute right-0 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"><p class="m-0 border-b border-slate-100 px-4 py-3 text-sm font-semibold">Notifications</p>@forelse(Auth::user()->unreadNotifications->take(4) as $notification)<a href="{{ $notification->data['url'] ?? '#' }}" class="block border-b border-slate-100 px-4 py-3 text-sm no-underline hover:bg-slate-50"><p class="m-0 font-medium text-slate-800">{{ $notification->data['title'] ?? 'Notification' }}</p><p class="mb-0 mt-1 text-xs text-slate-500">{{ $notification->data['message'] ?? '' }}</p></a>@empty<p class="p-4 text-sm text-slate-500">You are all caught up.</p>@endforelse</div></details>
                    <details class="relative"><summary class="flex cursor-pointer list-none items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-slate-50"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-100 font-semibold text-sky-700">{{ str(Auth::user()->name)->substr(0, 1)->upper() }}</span><span class="hidden font-medium text-slate-700 sm:block">{{ Auth::user()->name }}</span></summary><div class="absolute right-0 mt-2 w-44 rounded-xl border border-slate-200 bg-white p-1 shadow-lg"><form action="{{ route('logout') }}" method="POST">@csrf<button class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-600 hover:bg-slate-100">{{ __('common/header.logout') }}</button></form></div></details>
                </div>
            </div>
            <details class="mx-auto mt-3 max-w-7xl lg:hidden"><summary class="cursor-pointer text-sm font-medium text-slate-600">Menu</summary><nav class="mt-2 grid grid-cols-2 gap-1">@foreach($navItems as $item)<a href="{{ route($item['route']) }}" @class(['rounded-md px-3 py-2 text-sm no-underline', 'bg-sky-600 text-white' => request()->routeIs($item['route']), 'text-slate-600 hover:bg-slate-100' => !request()->routeIs($item['route'])])>{{ $item['label'] }}</a>@endforeach</nav></details>
        </header>
        <main class="mx-auto w-full max-w-7xl px-4 py-7 sm:px-6 lg:px-8">{{ $slot }}</main>
        <footer class="border-t border-slate-200 px-4 py-6 text-center text-sm text-slate-500 sm:px-6 lg:px-8">© {{ now()->year }} {{ config('app.name') }}</footer>
    </div>
</div>
<script src="{{ asset('static/js/app.js') }}"></script><script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@stack('scripts')
</body>
</html>
