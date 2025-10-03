@props([
    'icon' => null,
    'label' => '',
    'value' => 0,
    'theme' => 'primary',
    'metricKey' => null,
])

@php
    $themeClasses = [
        'primary' => 'bg-primary-50 text-primary-600',
        'success' => 'bg-green-50 text-green-600',
        'warning' => 'bg-amber-50 text-amber-600',
        'danger' => 'bg-red-50 text-red-600',
    ];

    $badgeClasses = $themeClasses[$theme] ?? $themeClasses['primary'];
@endphp

<div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900" data-metric="{{ $metricKey ?? \Illuminate\Support\Str::slug($label, '-') }}">{{ $value }}</p>
        </div>
        @if ($icon)
            <span class="flex h-12 w-12 items-center justify-center rounded-full {{ $badgeClasses }}">
                <i class="{{ $icon }} text-lg"></i>
            </span>
        @endif
    </div>
</div>

