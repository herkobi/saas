<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-sm btn-danger rounded-0 shadow-none']) }} data-bs-toggle="modal" data-bs-target="#deleteModal">
    {{ $slot }}
</button>
