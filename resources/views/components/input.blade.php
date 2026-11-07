@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null])

<div class="mb-3">
  <label for="{{ $name }}" class="form-label-custom">
    {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
  </label>
  <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
    value="{{ $type === 'password' ? '' : old($name, $value) }}"
    {{ $attributes->class(['form-control-custom', 'is-invalid-custom' => $errors->has($name)]) }}>
  @error($name)
    <div class="form-feedback-custom invalid-custom"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
  @enderror
  @if ($hint)<small class="text-muted">{{ $hint }}</small>@endif
</div>
