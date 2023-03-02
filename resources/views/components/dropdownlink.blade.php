@props(['active'])

@php
$classes = ($active ?? false)
            ? 'dropdown-item small py-2 d-flex align-items-center active'
            : 'dropdown-item small py-2 d-flex align-items-center';
@endphp

<li><a {{ $attributes->merge(['class' => $classes]) }}">{{ $slot }}</a></li>
