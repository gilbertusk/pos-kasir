<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 bg-rose-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-[0.15em] hover:bg-rose-700 hover:shadow-lg hover:shadow-rose-200 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition ease-in-out duration-150 transform active:scale-95']) }}>
    {{ $slot }}
</button>

