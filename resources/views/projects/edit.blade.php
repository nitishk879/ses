@extends('layouts.app')

@section('title', $project->title)

{{--
    Editing a project.

    This file used to be a truncated copy of the show page: breadcrumbs, a
    similar-projects rail and an "apply for this project" widget, with no form,
    no inputs and no submit. The kebab's Edit action opened it and there was
    nothing to change — and `ProjectController::update()` was an empty method
    behind it, so even a hand-built request would have saved nothing.

    The fields come from projects/_form, the same file the create screen uses,
    so the two screens ask the same questions and a change to one is a change to
    both.
--}}
@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb pt-3">
                        <li class="breadcrumb-item">
                            <a href="{{ route('project.index') }}">{{ __('projects/show.jobs') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('project.show', $project) }}">{{ $project->title }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('projects/index.edit_project') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-12 text-center">
                <h1 class="page-heading">{{ __('projects/index.edit_project') }}</h1>
                <p class="text-muted">{{ $project->title }}</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                {{-- PUT via @method, because `Route::resource` registers the update
                     route as PUT/PATCH and a browser form can only send POST. --}}
                <form class="row justify-content-center"
                      action="{{ route('project.update', $project) }}" method="post">
                    @csrf
                    @method('PUT')
                    @include('projects._form', [
                        'project' => $project,
                        'submitLabel' => __('projects/index.save_changes'),
                    ])
                </form>
            </div>
        </div>
    </div>

    @include('projects._form-scripts')
@endsection
