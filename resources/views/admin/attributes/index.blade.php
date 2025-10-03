@extends('admin.layouts.admin')

@section('title', __('cms.attributes.title_manage'))

@section('content')
<x-admin.page-header :title="__('cms.attributes.title_manage')" :description="__('cms.attributes.index_description')">
    <x-admin.button-link href="{{ route('admin.attributes.create') }}" class="btn-primary">
        {{ __('cms.attributes.title_create') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card>
    <div class="grid gap-3 md:grid-cols-4" data-page-stats="attributes">
        <div class="p-4 rounded-lg bg-primary-50 border border-primary-100">
            <p class="text-xs uppercase tracking-wide text-primary-600 mb-1">{{ __('cms.attributes.total_attributes') }}</p>
            <p class="text-xl font-semibold text-primary-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-success-50 border border-success-100">
            <p class="text-xs uppercase tracking-wide text-success-600 mb-1">{{ __('cms.attributes.total_values') }}</p>
            <p class="text-xl font-semibold text-success-900">{{ number_format($stats['values']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-warning-50 border border-warning-100">
            <p class="text-xs uppercase tracking-wide text-warning-600 mb-1">{{ __('cms.attributes.average_values') }}</p>
            <p class="text-xl font-semibold text-warning-900">{{ number_format($stats['average_per_attribute'], 1) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-info-50 border border-info-100">
            <p class="text-xs uppercase tracking-wide text-info-600 mb-1">{{ __('cms.attributes.top_attribute') }}</p>
            @if (! empty($stats['top_attribute']))
                <p class="text-sm font-medium text-info-900">{{ $stats['top_attribute']['name'] }}</p>
                <p class="text-xs text-info-700">{{ trans_choice('cms.attributes.values_with_count', $stats['top_attribute']['values_count'], ['count' => number_format($stats['top_attribute']['values_count'])]) }}</p>
            @else
                <p class="text-sm text-info-700">{{ __('cms.attributes.top_attribute_empty') }}</p>
            @endif
        </div>
    </div>
</x-admin.card>

<x-admin.card class="mt-6">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex flex-col flex-1 min-w-[220px]">
            <label class="form-label" for="search">{{ __('cms.attributes.search_label') }}</label>
            <input
                id="search"
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="{{ __('cms.attributes.search_placeholder') }}"
                class="form-control"
            >
        </div>
        <div class="flex flex-col w-40 min-w-[160px]">
            <label class="form-label" for="min_values">{{ __('cms.attributes.min_values_label') }}</label>
            <input
                id="min_values"
                type="number"
                min="0"
                name="min_values"
                value="{{ $filters['min_values'] }}"
                class="form-control"
            >
        </div>
        <div class="flex flex-col w-40 min-w-[160px]">
            <label class="form-label" for="max_values">{{ __('cms.attributes.max_values_label') }}</label>
            <input
                id="max_values"
                type="number"
                min="0"
                name="max_values"
                value="{{ $filters['max_values'] }}"
                class="form-control"
            >
        </div>
        <div class="flex flex-col w-48 min-w-[180px]">
            <label class="form-label" for="sort">{{ __('cms.attributes.sort_label') }}</label>
            <select id="sort" name="sort" class="form-select">
                <option value="latest" @selected($filters['sort'] === 'latest')>{{ __('cms.attributes.sort_latest') }}</option>
                <option value="oldest" @selected($filters['sort'] === 'oldest')>{{ __('cms.attributes.sort_oldest') }}</option>
                <option value="values_desc" @selected($filters['sort'] === 'values_desc')>{{ __('cms.attributes.sort_values_desc') }}</option>
                <option value="values_asc" @selected($filters['sort'] === 'values_asc')>{{ __('cms.attributes.sort_values_asc') }}</option>
            </select>
        </div>
        <div class="flex flex-col w-32 min-w-[140px]">
            <label class="form-label" for="per_page">{{ __('cms.attributes.per_page_label') }}</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach ([10, 15, 25, 50] as $size)
                    <option value="{{ $size }}" @selected($filters['per_page'] === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">
                {{ __('cms.attributes.apply_filters') }}
            </button>
            <x-admin.button-link href="{{ route('admin.attributes.index') }}" class="btn-outline">
                {{ __('cms.attributes.reset_filters') }}
            </x-admin.button-link>
        </div>
    </form>
</x-admin.card>

<x-admin.card class="mt-6">
    <x-admin.table :columns="[
        __('cms.attributes.id'),
        __('cms.attributes.name'),
        __('cms.attributes.values_count'),
        __('cms.attributes.values'),
        __('cms.attributes.products_usage'),
        __('cms.attributes.action'),
    ]">
        @forelse ($attributes as $attribute)
            @php
                $valuesPreview = $attribute->values->take(4);
                $remaining = max(0, (int) $attribute->values_count - $valuesPreview->count());
                $createdAt = $attribute->created_at ? $attribute->created_at->format('M d, Y') : null;
            @endphp
            <tr>
                <td class="table-cell align-top text-sm text-gray-500">#{{ $attribute->id }}</td>
                <td class="table-cell align-top">
                    <div class="flex flex-col">
                        <span class="font-medium text-gray-900">{{ $attribute->name }}</span>
                        @if ($createdAt)
                            <span class="text-xs text-gray-500">{{ __('cms.attributes.created_at', ['date' => $createdAt]) }}</span>
                        @endif
                    </div>
                </td>
                <td class="table-cell align-top">
                    <span class="badge badge-primary">{{ number_format($attribute->values_count) }}</span>
                </td>
                <td class="table-cell align-top">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($valuesPreview as $value)
                            <span class="badge badge-light">{{ $value->value }}</span>
                        @endforeach
                        @if ($remaining > 0)
                            <span class="badge badge-outline">{{ __('cms.attributes.more_values', ['count' => $remaining]) }}</span>
                        @endif
                    </div>
                </td>
                <td class="table-cell align-top">
                    <span class="badge badge-secondary">{{ number_format($attribute->products_count ?? 0) }}</span>
                </td>
                <td class="table-cell align-top">
                    <div class="flex flex-col gap-2 md:flex-row">
                        <x-admin.button-link href="{{ route('admin.attributes.edit', $attribute) }}" class="btn-outline btn-sm">
                            {{ __('cms.attributes.title_edit') }}
                        </x-admin.button-link>
                        <form
                            method="POST"
                            action="{{ route('admin.attributes.destroy', $attribute) }}"
                            onsubmit="return confirm('{{ __('cms.attributes.delete_confirmation') }}');"
                            class="inline-flex"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                {{ __('cms.attributes.delete') }}
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="table-cell text-center text-gray-500 py-6">
                    {{ __('cms.attributes.empty_state') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>

    <div class="mt-4">
=======
<x-admin.card class="mt-6">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th scope="col" class="w-16">{{ __('cms.attributes.id') }}</th>
                    <th scope="col">{{ __('cms.attributes.name') }}</th>
                    <th scope="col" class="w-1/2">{{ __('cms.attributes.values') }}</th>
                    <th scope="col" class="text-end">{{ __('cms.attributes.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attributes as $attribute)
                    <tr>
                        <td>{{ $attribute->id }}</td>
                        <td class="fw-semibold">{{ $attribute->name }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse ($attribute->values as $value)
                                    <span class="badge bg-primary">{{ $value->value }}</span>
                                @empty
                                    <span class="text-muted">{{ __('cms.attributes.no_values') }}</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-outline-primary btn-sm">
                                    {{ __('cms.attributes.title_edit') }}
                                </a>
                                <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('{{ __('cms.attributes.confirm_delete') }}');">
                                        {{ __('cms.attributes.delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            {{ __('cms.attributes.empty_state') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
>>>>>>> origin/codex/refactor-admin-attributes-creation-and-seeds
        {{ $attributes->links() }}
    </div>
</x-admin.card>
@endsection
