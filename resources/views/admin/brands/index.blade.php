<?php use Illuminate\Support\Facades\Storage; ?>
<?php use Illuminate\Support\Str; ?>

@extends('admin.layouts.admin')

@section('content')
    @php
        $statCards = [
            [
                'label' => __('cms.brands.total_brands'),
                'value' => number_format($stats['total'] ?? 0),
                'wrapper' => 'bg-primary-50 border border-primary-100',
                'accent' => 'text-primary-600',
                'valueColor' => 'text-primary-900',
            ],
            [
                'label' => __('cms.brands.active_brands'),
                'value' => number_format($stats['active'] ?? 0),
                'wrapper' => 'bg-success-50 border border-success-100',
                'accent' => 'text-success-600',
                'valueColor' => 'text-success-900',
            ],
            [
                'label' => __('cms.brands.inactive_brands'),
                'value' => number_format($stats['inactive'] ?? 0),
                'wrapper' => 'bg-warning-50 border border-warning-100',
                'accent' => 'text-warning-600',
                'valueColor' => 'text-warning-900',
            ],
            [
                'label' => __('cms.brands.discontinued_brands'),
                'value' => number_format($stats['discontinued'] ?? 0),
                'wrapper' => 'bg-danger-50 border border-danger-100',
                'accent' => 'text-danger-600',
                'valueColor' => 'text-danger-900',
            ],
        ];

        $statusBadgeClasses = [
            'active' => 'badge badge-success',
            'inactive' => 'badge badge-warning',
            'discontinued' => 'badge badge-danger',
        ];
    @endphp

    <x-admin.page-header
        :title="__('cms.brands.heading')"
        :description="__('cms.brands.page_subtitle')"
    >
        <x-admin.button-link href="{{ route('admin.brands.create') }}" class="btn-primary">
            {{ __('cms.brands.add_new') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <x-admin.card class="mt-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($statCards as $card)
                <div class="p-4 rounded-lg {{ $card['wrapper'] }}">
                    <p class="text-xs uppercase tracking-wide {{ $card['accent'] }} mb-1">{{ $card['label'] }}</p>
                    <p class="text-2xl font-semibold {{ $card['valueColor'] }}">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <form method="GET" class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="form-label" for="search">{{ __('cms.brands.search_label') }}</label>
                <input
                    id="search"
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="{{ __('cms.brands.search_placeholder') }}"
                    class="form-control"
                >
            </div>
            <div>
                <label class="form-label" for="status">{{ __('cms.brands.status_filter_label') }}</label>
                <select id="status" name="status" class="form-select">
                    @foreach ($statusFilters as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="sort">{{ __('cms.brands.sort_label') }}</label>
                <select id="sort" name="sort" class="form-select">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary">
                    {{ __('cms.brands.apply_filters') }}
                </button>
                <x-admin.button-link href="{{ route('admin.brands.index') }}" class="btn-outline">
                    {{ __('cms.brands.reset_filters') }}
                </x-admin.button-link>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mt-6">
        <x-admin.table :columns="[
            __('cms.brands.name'),
            __('cms.brands.table_products'),
            __('cms.brands.table_translations'),
            __('cms.brands.status'),
            __('cms.brands.action'),
        ]">
            @forelse ($brands as $brand)
                @php
                    $locale = app()->getLocale();
                    $fallbackLocale = config('app.fallback_locale');
                    $translation = $brand->translations->firstWhere('locale', $locale);
                    if (! $translation && $fallbackLocale) {
                        $translation = $brand->translations->firstWhere('locale', $fallbackLocale);
                    }

                    $brandName = $translation->name ?? $brand->slug;
                    $logoUrl = null;

                    if ($brand->logo_url) {
                        if (Str::startsWith($brand->logo_url, ['http://', 'https://'])) {
                            $logoUrl = $brand->logo_url;
                        } elseif (Str::startsWith($brand->logo_url, ['assets/', 'images/', 'storage/'])) {
                            $logoUrl = asset($brand->logo_url);
                        } elseif (Storage::disk('public')->exists($brand->logo_url)) {
                            $logoUrl = Storage::url($brand->logo_url);
                        } else {
                            $logoUrl = asset('storage/' . ltrim($brand->logo_url, '/'));
                        }
                    }

                    $statusKey = strtolower((string) $brand->status);
                    $statusLabel = $statusLabels[$statusKey] ?? ($statusKey ? ucfirst($statusKey) : ($statusLabels['inactive'] ?? __('cms.brands.status_inactive')));
                    $badgeClass = $statusBadgeClasses[$statusKey] ?? 'badge badge-secondary';
                    $translationsCount = $brand->translations_count ?? $brand->translations->count();
                @endphp
                <tr>
                    <td class="table-cell align-top">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-md border border-gray-200 overflow-hidden bg-gray-100 flex items-center justify-center">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs text-gray-400">{{ __('cms.brands.no_logo') }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $brandName }}</p>
                                <p class="text-xs text-gray-500">#{{ $brand->id }} • {{ $brand->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="table-cell align-top text-sm text-gray-700">{{ number_format($brand->products_count ?? 0) }}</td>
                    <td class="table-cell align-top text-sm text-gray-700">{{ number_format($translationsCount) }}</td>
                    <td class="table-cell align-top">
                        <div class="flex flex-col gap-2">
                            <span class="{{ $badgeClass }} w-fit">{{ $statusLabel }}</span>
                            <form method="POST" action="{{ route('admin.brands.updateStatus') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $brand->id }}">
                                <label for="brand-status-{{ $brand->id }}" class="sr-only">{{ __('cms.brands.status') }}</label>
                                <select
                                    id="brand-status-{{ $brand->id }}"
                                    name="status"
                                    class="form-select text-sm"
                                    onchange="this.form.submit()"
                                >
                                    @foreach ($statusLabels as $value => $label)
                                        <option value="{{ $value }}" @selected($statusKey === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </td>
                    <td class="table-cell align-top">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <x-admin.button-link href="{{ route('admin.brands.edit', $brand) }}" class="btn-outline btn-sm">
                                {{ __('cms.brands.edit') }}
                            </x-admin.button-link>
                            <form
                                method="POST"
                                action="{{ route('admin.brands.destroy', $brand) }}"
                                class="w-full sm:w-auto"
                                onsubmit="return confirm('{{ __('cms.brands.confirm_delete') }}');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-full">
                                    {{ __('cms.brands.delete') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="table-cell text-center text-gray-500 py-6">
                        {{ __('cms.brands.empty_state') }}
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-6">
            {{ $brands->onEachSide(1)->links() }}
        </div>
    </x-admin.card>
@endsection
