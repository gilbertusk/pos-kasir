<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-6 py-3 bg-white border border-slate-200 rounded-xl font-black text-xs text-slate-600 uppercase tracking-[0.15em] shadow-sm hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 transform active:scale-95']) }}>
    {{ $slot }}
</button>

