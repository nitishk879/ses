@props(['value', 'required' => false])

@php
    $classes = $required ? "form-label required": "form-label";
@endphp

<label {{ $attributes->merge(['class' => $classes]) }}>
    {{ $value ?? $slot }}
</label>
