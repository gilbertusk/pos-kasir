<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 bg-primary-600 border border-transparent rounded-xl font-black text-xs text-white uppercase tracking-[0.15em] hover:bg-primary-700 hover:shadow-lg hover:shadow-primary-200 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150 transform active:scale-95']) }}>
    {{ $slot }}
</button>

