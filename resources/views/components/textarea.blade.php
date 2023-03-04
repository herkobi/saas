<textarea {{ $attributes->merge(['class' => 'form-control rounded-0 shadow-none', 'rows' => '3', 'cols' => '30']) }}>
    {{ $slot }}
</textarea>
