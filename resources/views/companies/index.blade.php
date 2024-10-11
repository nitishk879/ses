@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="container" id="dashboard">
        <div class="row">
            <x-sidebar>
                <form wire:submit.prevent="search" class="input-group mb-3">
                    <button type="submit" class="input-group-text" id="search-keyword"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <input type="text" class="form-control search-input" wire:model.live="search" placeholder="{{ __("common/sidebar.search_with_keyword") }}" aria-label="Username" aria-describedby="search-keyword">
                </form>
            </x-sidebar>
            <div class="col-md-9 col-xl-9">
                <div class="d-flex w-100 justify-content-between my-3">
                    <h2 class="search-keyword">{{ __("talents/index.showing_results_for") }}: <span>"{{ __("talents/index.search_keyword") }}"</span></h2>
                    <div class="search-sort">
                        <div class="input-group mb-3">
                            <label class="input-group-text" for="inputGroupSelect01">{{ __("talents/index.sort_by") }}</label>
                            <select class="form-select" id="inputGroupSelect01">
                                <option selected>{{ __("talents/index.recent_listings") }}</option>
                                <option value="1">Price</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                    </div>
                </div>
                <livewire:companies.index />
            </div>
        </div>
    </div>
@endsection
