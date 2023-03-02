@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'fw-semibold small']) }}>
        {{ $status }}
    </div>
@endif
