@props(['name', 'label', 'options', 'value' => null, 'placeholder' => null, 'required' => false])

<div class="mb-3">
  <label for="{{ $name }}" class="form-label-custom">
    {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
  </label>
  <select id="{{ $name }}" name="{{ $name }}" @required($required)
    {{ $attributes->class(['form-select-custom', 'is-invalid-custom' => $errors->has($name)]) }}>
    @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
    @foreach ($options as $key => $text)
      <option value="{{ $key }}" @selected((string) old($name, $value) === (string) $key)>{{ $text }}</option>
    @endforeach
  </select>
  @error($name)
    <div class="form-feedback-custom invalid-custom"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
  @enderror
</div>
