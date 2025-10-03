@extends('admin.layouts.admin')

@section('content')
<x-admin.page-header
    :title="__('cms.categories.title_manage')"
    :description="__('cms.categories.index_description')"
>
    <x-admin.button-link href="{{ route('admin.categories.create') }}" class="btn-primary">
        {{ __('cms.categories.add_new') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card>
    <div class="grid gap-3 md:grid-cols-3">
        <div class="p-4 rounded-lg bg-primary-50 border border-primary-100">
            <p class="text-xs uppercase tracking-wide text-primary-600 mb-1">{{ __('cms.categories.total_categories') }}</p>
            <p class="text-xl font-semibold text-primary-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-success-50 border border-success-100">
            <p class="text-xs uppercase tracking-wide text-success-600 mb-1">{{ __('cms.categories.active_categories') }}</p>
            <p class="text-xl font-semibold text-success-900">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-warning-50 border border-warning-100">
            <p class="text-xs uppercase tracking-wide text-warning-600 mb-1">{{ __('cms.categories.inactive_categories') }}</p>
            <p class="text-xl font-semibold text-warning-900">{{ number_format($stats['inactive']) }}</p>
        </div>
    </div>
</x-admin.card>

<x-admin.card class="mt-6">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex flex-col gap-1 min-w-[200px]">
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
        <div class="flex flex-col gap-1 min-w-[180px]">
            <label class="form-label" for="status">{{ __('cms.categories.status_filter_label') }}</label>
            <select id="status" name="status" class="form-select">
                <option value="">{{ __('cms.categories.status_filter_all') }}</option>
                <option value="active" @selected($filters['status'] === 'active')>{{ __('cms.categories.status_filter_active') }}</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>{{ __('cms.categories.status_filter_inactive') }}</option>
            </select>
        </div>
        <div class="flex flex-col gap-1 min-w-[200px]">
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
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn btn-primary">
                {{ __('cms.categories.apply_filters') }}
            </button>
            <x-admin.button-link href="{{ route('admin.categories.index') }}" class="btn-outline">
                {{ __('cms.categories.reset_filters') }}
            </x-admin.button-link>
        </div>
    </form>
</x-admin.card>

<x-admin.card class="mt-6">
    <x-admin.table :columns="[
        __('cms.categories.id'),
        __('cms.categories.name'),
        __('cms.categories.subcategories'),
        __('cms.categories.products_count'),
        __('cms.categories.status'),
        __('cms.categories.action'),
    ]">
        @forelse ($categories as $node)
            @php
                $category = $node['category'];
                $isActive = (bool) $category->status;
                $depth = $node['depth'];
                $padding = $depth * 1.5;
            @endphp
            <tr>
                <td class="table-cell align-top text-sm text-gray-500">#{{ $category->id }}</td>
                <td class="table-cell align-top">
                    <div class="flex flex-col" style="padding-left: {{ $padding }}rem;">
                        <span class="font-medium text-gray-900">{{ $node['name'] }}</span>
                        <span class="text-xs text-gray-500">{{ $category->slug }}</span>
                    </div>
                </td>
                <td class="table-cell align-top text-sm text-gray-700">{{ number_format($node['children_count']) }}</td>
                <td class="table-cell align-top text-sm text-gray-700">{{ number_format($category->products_count ?? 0) }}</td>
                <td class="table-cell align-top">
                    <span class="badge {{ $isActive ? 'badge-success' : 'badge-danger' }}">
                        {{ $isActive ? __('cms.products.status_active') : __('cms.products.status_inactive') }}
                    </span>
                </td>
                <td class="table-cell align-top">
                    <div class="flex items-center gap-2">
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
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="table-cell text-center text-gray-500 py-6">
                    {{ __('cms.categories.empty_state') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
</x-admin.card>
@endsection
