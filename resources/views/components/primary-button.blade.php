<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[var(--brand,#4f46e5)] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:brightness-90 focus:brightness-90 active:brightness-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand,#4f46e5)] transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
