<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

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
    @livewireStyles
</head>
<body>
<div id="app">
    <x-header/>
    <main class="" style="background: #F1F1F1!important;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 mt-3">
                    <!--  Alert goes here  --->
                    @if (session('message'))
                        <x-alert-component :type="session('type')">
                            {{ session('message') }}
                        </x-alert-component>
                    @endif
                    <!--  Alert goes here  --->
                </div>
            </div>
        </div>
        {{ $slot}}
    </main>

    <x-footer />
</div>
@livewireScripts
@stack('scripts')
</body>
</html>
