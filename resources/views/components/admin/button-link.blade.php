@props([
    'href',
    'type' => 'button',
])

@php
    $classes = trim('btn ' . ($attributes->get('class') ?? ''));
@endphp

<button type="{{ $type }}"
        {{ $attributes->except(['class']) }}
        class="{{ $classes }}"
        data-navigate="{{ $href }}"
        onclick="window.location.href=this.dataset.navigate">
    {{ $slot }}
</button>
