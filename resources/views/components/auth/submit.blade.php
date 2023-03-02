<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-lg btn-primary rounded-0 shadow-none w-100 text-white']) }}>
    {{ $slot }}
</button>
