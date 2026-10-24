@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-[44px] rounded-xl border-enamel-200 bg-white text-ink focus:border-brand-600 focus:ring-4 focus:ring-brand-100 transition-colors']) }}>
