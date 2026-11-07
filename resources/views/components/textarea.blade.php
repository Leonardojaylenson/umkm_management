@props(['name', 'label', 'value' => null, 'rows' => 3])

<div class="mb-3">
  <label for="{{ $name }}" class="form-label-custom">{{ $label }}</label>
  <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
    {{ $attributes->class(['form-control-custom', 'is-invalid-custom' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
  @error($name)
    <div class="form-feedback-custom invalid-custom"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
  @enderror
</div>
