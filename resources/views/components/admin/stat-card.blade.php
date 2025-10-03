@props([
    'icon' => null,
    'label' => '',
    'value' => 0,
    'theme' => 'primary',
    'metricKey' => null,
    'variant' => 'slate',
    'percentage' => null,
])

@php
    $themeClasses = [
        'primary' => 'bg-primary-50 text-primary-600',
        'success' => 'bg-green-50 text-green-600',
        'warning' => 'bg-amber-50 text-amber-600',
        'danger' => 'bg-red-50 text-red-600',
    ];

    $variants = [
        'slate' => [
            'wrapper' => 'bg-slate-50 border border-slate-200 text-slate-900',
            'label' => 'text-slate-600',
            'progress' => 'bg-slate-400',
        ],
        'emerald' => [
            'wrapper' => 'bg-emerald-50 border border-emerald-100 text-emerald-900',
            'label' => 'text-emerald-600',
            'progress' => 'bg-emerald-500',
        ],
        'amber' => [
            'wrapper' => 'bg-amber-50 border border-amber-100 text-amber-900',
            'label' => 'text-amber-600',
            'progress' => 'bg-amber-500',
        ],
        'rose' => [
            'wrapper' => 'bg-rose-50 border border-rose-100 text-rose-900',
            'label' => 'text-rose-600',
            'progress' => 'bg-rose-500',
        ],
    ];

    $badgeClasses = $themeClasses[$theme] ?? $themeClasses['primary'];
    $styles = $variants[$variant] ?? $variants['slate'];
@endphp

<div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900" data-metric="{{ $metricKey ?? \Illuminate\Support\Str::slug($label, '-') }}">{{ $value }}</p>
            
            @if(! is_null($percentage))
                <div class="mt-3" role="presentation">
                    <div class="h-2 rounded-full bg-gray-200 overflow-hidden" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentage }}">
                        <div class="h-full {{ $styles['progress'] }}" style="width: {{ max(min($percentage, 100), 0) }}%;"></div>
                    </div>
                    <p class="mt-2 text-[11px] font-medium text-gray-500" title="{{ $percentage }}% {{ __('cms.vendors.total_vendors') }}">
                        {{ $percentage }}%
                        <span class="sr-only">{{ __('cms.vendors.total_vendors') }}</span>
                    </p>
                </div>
            @endif
        </div>
        @if ($icon)
            <span class="flex h-12 w-12 items-center justify-center rounded-full {{ $badgeClasses }}">
                <i class="{{ $icon }} text-lg"></i>
            </span>
        @endif
    </div>
</div>
