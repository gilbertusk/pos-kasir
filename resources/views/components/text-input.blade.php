@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-200 bg-slate-50/50 focus:bg-white focus:border-primary-500 focus:ring-primary-500 rounded-xl shadow-sm transition-all duration-200']) }}>

