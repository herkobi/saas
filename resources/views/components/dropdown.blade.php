@props(['align' => 'right'])

@php
switch ($align) {
    case 'none':
        $alignmentClasses = '';
        break;
    case 'left':
        $alignmentClasses = 'dropdown-menu-start';
        break;
    case 'right':
        $alignmentClasses = 'dropdown-menu-end';
        break;
    default:
        $alignmentClasses = '';
        break;
}
@endphp

{{ $trigger }}
<ul class="dropdown-menu {{ $alignmentClasses }} mt-4 py-0 rounded-0 shadow-none">
    {{ $content }}
</ul>
