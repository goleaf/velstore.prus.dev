@php
    $category = $category ?? null;
    $isActive = (bool) ($isActive ?? false);
@endphp

@if ($category)
    <div class="flex flex-wrap items-center gap-2">
        <form method="POST" action="{{ route('admin.categories.updateStatus') }}" class="inline-flex">
            @csrf
            <input type="hidden" name="id" value="{{ $category->id }}">
            <input type="hidden" name="status" value="{{ $isActive ? 0 : 1 }}">
            <button
                type="submit"
                class="btn btn-outline-primary btn-sm p-2"
                title="{{ $isActive ? __('cms.products.deactivate_button') : __('cms.products.activate_button') }}"
                aria-label="{{ $isActive ? __('cms.products.deactivate_button') : __('cms.products.activate_button') }}"
            >
                <span class="sr-only">{{ $isActive ? __('cms.products.deactivate_button') : __('cms.products.activate_button') }}</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3v9"></path>
                    <path d="M18 12a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                </svg>
            </button>
        </form>

        <x-admin.button-link
            href="{{ route('admin.categories.edit', $category) }}"
            class="btn-outline btn-sm p-2"
            aria-label="{{ __('cms.products.edit_button') }}"
            title="{{ __('cms.products.edit_button') }}"
        >
            <span class="sr-only">{{ __('cms.products.edit_button') }}</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M16.862 4.487L19.5 7.125"></path>
                <path d="M5.25 18.75l2.651-.884a4.5 4.5 0 001.59-1.04L19.513 6.804a2.25 2.25 0 00-3.182-3.182L6.309 13.643a4.5 4.5 0 00-1.04 1.59l-.884 2.652z"></path>
            </svg>
        </x-admin.button-link>

        <x-admin.button-link
            href="{{ route('admin.categories.create', ['parent' => $category->id]) }}"
            class="btn-outline btn-sm p-2"
            aria-label="{{ __('cms.categories.add_subcategory') }}"
            title="{{ __('cms.categories.add_subcategory') }}"
        >
            <span class="sr-only">{{ __('cms.categories.add_subcategory') }}</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 6v12"></path>
                <path d="M18 12H6"></path>
            </svg>
        </x-admin.button-link>

        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline-flex" onsubmit="return confirm('{{ __('cms.categories.confirm_delete') }}');">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="btn btn-outline-danger btn-sm p-2"
                title="{{ __('cms.categories.delete') }}"
                aria-label="{{ __('cms.categories.delete') }}"
            >
                <span class="sr-only">{{ __('cms.categories.delete') }}</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 7h12"></path>
                    <path d="M10 11v6"></path>
                    <path d="M14 11v6"></path>
                    <path d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"></path>
                    <path d="M19 7l-.867 12.14A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.86L5 7"></path>
                </svg>
            </button>
        </form>
    </div>
@endif
