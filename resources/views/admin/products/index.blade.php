@extends('admin.layouts.admin')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('content')
<x-admin.page-header
    :title="__('cms.products.title_manage')"
    :description="__('cms.products.index_description')"
>
    <x-admin.button-link href="{{ route('admin.products.create') }}" class="btn-primary">
        {{ __('cms.products.add_new') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card>
    <div class="grid gap-3 md:grid-cols-3">
        <div class="p-4 rounded-lg bg-primary-50 border border-primary-100">
            <p class="text-xs uppercase tracking-wide text-primary-600 mb-1">{{ __('cms.products.total_products') }}</p>
            <p class="text-xl font-semibold text-primary-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-success-50 border border-success-100">
            <p class="text-xs uppercase tracking-wide text-success-600 mb-1">{{ __('cms.products.active_products') }}</p>
            <p class="text-xl font-semibold text-success-900">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-warning-50 border border-warning-100">
            <p class="text-xs uppercase tracking-wide text-warning-600 mb-1">{{ __('cms.products.inactive_products') }}</p>
            <p class="text-xl font-semibold text-warning-900">{{ number_format($stats['inactive']) }}</p>
        </div>
    </div>
</x-admin.card>

<x-admin.card class="mt-6">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex flex-col flex-1 min-w-[220px]">
            <label class="form-label" for="search">{{ __('cms.products.search_label') }}</label>
            <input
                id="search"
                type="search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="{{ __('cms.products.search_placeholder') }}"
                class="form-control"
            >
        </div>
        <div class="flex flex-col w-48 min-w-[180px]">
            <label class="form-label" for="status">{{ __('cms.products.status_filter_label') }}</label>
            <select id="status" name="status" class="form-select">
                <option value="">{{ __('cms.products.status_filter_all') }}</option>
                <option value="active" @selected($filters['status'] === 'active')>{{ __('cms.products.status_filter_active') }}</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>{{ __('cms.products.status_filter_inactive') }}</option>
            </select>
        </div>
        <div class="flex flex-col w-48 min-w-[180px]">
            <label class="form-label" for="sort">{{ __('cms.products.sort_label') }}</label>
            <select id="sort" name="sort" class="form-select">
                <option value="latest" @selected($filters['sort'] === 'latest')>{{ __('cms.products.sort_latest') }}</option>
                <option value="oldest" @selected($filters['sort'] === 'oldest')>{{ __('cms.products.sort_oldest') }}</option>
            </select>
        </div>
        <div class="flex flex-col w-32 min-w-[140px]">
            <label class="form-label" for="per_page">{{ __('cms.products.per_page_label') }}</label>
            <select id="per_page" name="per_page" class="form-select">
                @foreach ([10, 15, 25, 50] as $size)
                    <option value="{{ $size }}" @selected($filters['per_page'] === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">
                {{ __('cms.products.apply_filters') }}
            </button>
            <x-admin.button-link href="{{ route('admin.products.index') }}" class="btn-outline">
                {{ __('cms.products.reset_filters') }}
            </x-admin.button-link>
        </div>
    </form>
</x-admin.card>

<x-admin.card class="mt-6">
    <x-admin.table :columns="[
        __('cms.products.id'),
        __('cms.products.name'),
        __('cms.products.category_column'),
        __('cms.products.price_column'),
        __('cms.products.stock_column'),
        __('cms.products.status_column'),
        __('cms.products.action'),
    ]">
        @forelse ($products as $product)
            @php
                $translation = $product->translation;
                $productName = $translation?->name ?? $product->translations->first()?->name ?? __('cms.products.unnamed_product');
                $categoryName = optional($product->category?->translation)->name ?? __('cms.products.no_category');
                $brandName = optional($product->brand?->translation)->name;
                $isActive = in_array($product->status, [1, '1', true, 'active'], true);
                $price = $product->primaryVariant?->price ?? $product->price;
                $stock = $product->primaryVariant?->stock ?? $product->stock;
                $thumbnail = $product->thumbnail?->image_url;
                $thumbnailUrl = null;

                if ($thumbnail) {
                    if (Str::startsWith($thumbnail, ['http://', 'https://'])) {
                        $thumbnailUrl = $thumbnail;
                    } elseif (Str::startsWith($thumbnail, ['assets/', 'images/', 'storage/'])) {
                        $thumbnailUrl = asset($thumbnail);
                    } elseif (Storage::disk('public')->exists($thumbnail)) {
                        $thumbnailUrl = Storage::url($thumbnail);
                    } else {
                        $thumbnailUrl = asset('storage/'.$thumbnail);
                    }
                }
            @endphp
            <tr>
                <td class="table-cell align-top text-sm text-gray-500">#{{ $product->id }}</td>
                <td class="table-cell align-top">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-md border border-gray-200 overflow-hidden bg-gray-100 flex items-center justify-center">
                            @if ($thumbnailUrl)
                                <img src="{{ $thumbnailUrl }}" alt="{{ $productName }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-xs text-gray-400">{{ __('cms.products.no_image') }}</span>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $productName }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $brandName ?? __('cms.products.no_brand_assigned') }} • SKU: {{ $product->SKU ?? '—' }}
                            </p>
                        </div>
                    </div>
                </td>
                <td class="table-cell align-top text-sm text-gray-700">{{ $categoryName }}</td>
                <td class="table-cell align-top text-sm text-gray-700">
                    {{ $price !== null ? number_format((float) $price, 2) : '—' }}
                </td>
                <td class="table-cell align-top text-sm text-gray-700">{{ $stock !== null ? $stock : '—' }}</td>
                <td class="table-cell align-top">
                    <span class="badge {{ $isActive ? 'badge-success' : 'badge-danger' }}">
                        {{ $isActive ? __('cms.products.status_active') : __('cms.products.status_inactive') }}
                    </span>
                </td>
                <td class="table-cell align-top">
                    <div class="flex flex-col gap-2">
                        <form method="POST" action="{{ route('admin.products.updateStatus') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <input type="hidden" name="status" value="{{ $isActive ? 0 : 1 }}">
                            <button type="submit" class="btn btn-outline-primary btn-sm w-full">
                                {{ $isActive ? __('cms.products.deactivate_button') : __('cms.products.activate_button') }}
                            </button>
                        </form>
                        <x-admin.button-link href="{{ route('admin.products.edit', $product) }}" class="btn-outline btn-sm">
                            {{ __('cms.products.edit_button') }}
                        </x-admin.button-link>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('{{ __('cms.products.delete_confirmation') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-full">
                                {{ __('cms.products.delete') }}
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="table-cell text-center text-gray-500 py-6">
                    {{ __('cms.products.empty_state') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</x-admin.card>
@endsection
