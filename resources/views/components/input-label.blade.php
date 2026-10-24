@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-enamel-700 mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
