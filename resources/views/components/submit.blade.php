<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary rounded-0 shadow-none']) }}>
    {{ $slot }}
</button>
