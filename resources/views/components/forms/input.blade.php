@props(['disabled' => false, 'hasError' => false])

@php
    $classes = $hasError ? "form-control is-invalid" : "form-control";
@endphp

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => $classes]) !!} />
