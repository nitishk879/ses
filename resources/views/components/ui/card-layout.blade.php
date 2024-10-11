@props(['item' => null])

<div class="talent-card" wire:key="{{ $item->id }}">
    <div class="talent-card-header">
        <div class="row justify-content-between">
            {{ $header }}
        </div>
    </div>
    <div class="talent-card-body">
        <div class="row justify-content-between">
            {{ $slot }}
        </div>
    </div>
</div>
