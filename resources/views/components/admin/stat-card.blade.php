@props([
    'label',
    'value' => 0,
    'variant' => 'slate',
    'percentage' => null,
])

@php
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

    $styles = $variants[$variant] ?? $variants['slate'];
@endphp

<div class="p-4 rounded-lg {{ $styles['wrapper'] }}">
    <p class="text-xs uppercase tracking-wide {{ $styles['label'] }} mb-1">{{ $label }}</p>
    <p class="text-xl font-semibold leading-tight">{{ $value }}</p>

    @if(! is_null($percentage))
        <div class="mt-3" role="presentation">
            <div class="h-2 rounded-full bg-white/60 overflow-hidden" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentage }}">
                <div class="h-full {{ $styles['progress'] }}" style="width: {{ max(min($percentage, 100), 0) }}%;"></div>
            </div>
            <p class="mt-2 text-[11px] font-medium {{ $styles['label'] }}" title="{{ $percentage }}% {{ __('cms.vendors.total_vendors') }}">
                {{ $percentage }}%
                <span class="sr-only">{{ __('cms.vendors.total_vendors') }}</span>
            </p>
        </div>
    @endif
</div>
