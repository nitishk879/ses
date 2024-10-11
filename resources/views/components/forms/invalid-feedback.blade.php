@props(['name' => ''])

@error($name)
    <div class="invalid-feedback" @if($name) style="display:block;" @endif>{{ $message }}</div>
@enderror
