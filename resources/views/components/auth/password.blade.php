@props(['disabled' => false])

<span class="input-group-text rounded-0 shadow-none">
    <i class="ri ri-key-line"></i>
</span>
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-control login-input rounded-0 shadow-none']) !!}>
