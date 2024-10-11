@props(['id' => '', 'confirmingOenModal' => false, 'modal-size' => ''])
@php
    $id = $id ?? md5(now());
@endphp
<div>
    <div class="modal fade {{ $confirmingOenModal ? 'show' : '' }}" id="{{$id}}" tabindex="-1" aria-modal="true" role="dialog" style="display: block;">
        {{ $slot }}
    </div>

    <div class="modal-backdrop fade {{ $confirmingOenModal ? 'show' : '' }}"></div>
</div>
