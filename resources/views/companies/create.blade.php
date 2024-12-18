@extends('layouts.app')

@section('title', __("company/register.heading_title"))

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="page-heading">{{ __('company/register.heading_title') }}</h1>
            </div>
        </div>
        <div class="row justify-content-center align-items-center">
            @php($user = Auth::user())
            @livewire('company.register', ['user' => $user, 'company' => $user?->company])
        </div>
    </div>
@endsection

@section('editor', true)
@section('modals', true)

@push('scripts')
    @vite('resources/js/main.js')
@endpush
