@php
    $filters = $filters ?? [
        'search' => '',
        'status' => '',
        'parent' => null,
    ];
    $parentOptions = $parentOptions ?? [];
@endphp

<form method="GET" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5 items-end w-full">
    <div class="flex flex-col gap-1 md:col-span-2">
        <label class="form-label" for="search">{{ __('cms.categories.search_label') }}</label>
        <input
            id="search"
            type="search"
            name="search"
            value="{{ $filters['search'] }}"
            placeholder="{{ __('cms.categories.search_placeholder') }}"
            class="form-control"
        >
    </div>

    <div class="flex flex-col gap-1 xl:col-span-1">
        <label class="form-label" for="status">{{ __('cms.categories.status_filter_label') }}</label>
        <select id="status" name="status" class="form-select">
            <option value="">{{ __('cms.categories.status_filter_all') }}</option>
            <option value="active" @selected($filters['status'] === 'active')>
                {{ __('cms.categories.status_filter_active') }}
            </option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>
                {{ __('cms.categories.status_filter_inactive') }}
            </option>
        </select>
    </div>

    <div class="flex flex-col gap-1 xl:col-span-1">
        <label class="form-label" for="parent">{{ __('cms.categories.parent_filter_label') }}</label>
        <select id="parent" name="parent" class="form-select">
            <option value="">{{ __('cms.categories.parent_filter_all') }}</option>
            @foreach ($parentOptions as $option)
                <option value="{{ $option['id'] }}" @selected((string) $filters['parent'] === (string) $option['id'])>
                    {{ $option['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-wrap items-center gap-3 md:col-span-2 xl:col-span-1">
        <button type="submit" class="btn btn-primary">
            {{ __('cms.categories.apply_filters') }}
        </button>
        <x-admin.button-link href="{{ route('admin.categories.index') }}" class="btn-outline">
            {{ __('cms.categories.reset_filters') }}
        </x-admin.button-link>
    </div>
</form>
