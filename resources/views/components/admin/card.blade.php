@props([
    'title' => null,
    'noMargin' => false,
    'actions' => null,
])

<div {{ $attributes->class(['card', 'mt-4' => ! $noMargin]) }}>
    @if ($title || isset($actions))
        <div class="card-header flex items-center justify-between">
            @if ($title)
                <h2 class="text-sm font-semibold text-gray-800 mb-0">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div class="flex items-center gap-2">{!! $actions !!}</div>
            @endisset
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
