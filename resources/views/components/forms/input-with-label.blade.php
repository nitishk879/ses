@props(['name' => '', 'required' => false, 'id' => '', 'placeholder'])
<div class="d-block">
    <label for="{{ $id }}"
           class="col-form-label {{ $required ? 'required': '' }}">
        {{ $placeholder }}
    </label>
    <input type="text"
           class="form-control @error("{$name}") is-invalid @enderror"
           id="{{ $id }}" name="{{ $name }}"
           value="{{ old("{$name}") ?? '' }}"
           placeholder="{{ $placeholder }}"
        {{ $required ? 'required': '' }} />
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
