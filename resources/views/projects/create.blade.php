@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="page-heading">{{ __('projects/form.project_registration') }}</h1>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <form class="row justify-content-center" action="{{ route("project.store") }}" method="post">
                    @csrf
                    {{-- Fields live in projects/_form so create and edit cannot drift. --}}
                    @include('projects._form', ['project' => null])
                </form>
            </div>
{{--            <livewire:projects.new-project />--}}
        </div>
    </div>
    @include('projects._form-scripts')
@endsection
