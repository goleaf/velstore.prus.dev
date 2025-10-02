@props([
    'title',
    'description' => null,
])

<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mt-4">
    <div>
        <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    <div class="flex items-center gap-2">
        {{ $slot }}
    </div>
</div>
