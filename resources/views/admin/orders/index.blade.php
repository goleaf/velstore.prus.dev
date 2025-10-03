@extends('admin.layouts.admin')

@section('content')
    @php
        $deleteTemplate = route('admin.orders.destroy', ['order' => '__ORDER_ID__']);
        $orderMetrics = [
            'total_orders' => $metrics['total_orders'] ?? 0,
            'total_revenue' => $metrics['total_revenue'] ?? 0,
            'average_order_value' => $metrics['average_order_value'] ?? 0,
        ];
        $statusCounts = $metrics['status_counts'] ?? [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'canceled' => 0,
        ];
    @endphp

    <x-admin.page-header :title="__('cms.orders.title')">
        <x-admin.button-link href="{{ route('admin.orders.create') }}" class="btn-primary">
            {{ __('cms.orders.create_button') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <div class="mt-6 space-y-6">
        <div>
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {{ __('cms.orders.overview_title') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('cms.orders.overview_description') }}
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    {{ __('cms.orders.total_orders') }}
                </p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                    {{ number_format($orderMetrics['total_orders']) }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    {{ __('cms.orders.total_revenue') }}
                </p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                    {{ number_format($orderMetrics['total_revenue'], 2) }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    {{ __('cms.orders.average_order_value') }}
                </p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                    {{ number_format($orderMetrics['average_order_value'], 2) }}
                </p>
            </div>
        </div>

        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {{ __('cms.orders.status_breakdown') }}
            </h3>
            <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        {{ __('cms.dashboard.pending_orders') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">
                        {{ number_format($statusCounts['pending'] ?? 0) }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        {{ __('cms.dashboard.processing_orders') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">
                        {{ number_format($statusCounts['processing'] ?? 0) }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        {{ __('cms.dashboard.completed_orders') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">
                        {{ number_format($statusCounts['completed'] ?? 0) }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        {{ __('cms.dashboard.cancelled_orders') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">
                        {{ number_format($statusCounts['canceled'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <x-admin.card noMargin class="mt-6">
        <form
            method="GET"
            action="{{ route('admin.orders.index') }}"
            class="mb-4 grid gap-3 md:grid-cols-[minmax(0,260px)_minmax(0,200px)_auto] md:items-end"
        >
            @if (! empty($currentStatus))
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif

            <div class="grid gap-1">
                <label for="orders-search" class="form-label">{{ __('cms.orders.filters.search_label') }}</label>
                <input
                    id="orders-search"
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    class="form-control"
                    placeholder="{{ __('cms.orders.filters.search_placeholder') }}"
                >
            </div>

            <div class="grid gap-1">
                <label for="orders-shop" class="form-label">{{ __('cms.orders.shop') }}</label>
                <select id="orders-shop" name="shop" class="form-select">
                    <option value="0">{{ __('cms.orders.filters.shop_all') }}</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}" @selected(($filters['shop'] ?? 0) === $shop->id)>
                            {{ $shop->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-2 md:justify-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    {{ __('cms.orders.filters.apply') }}
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">
                    {{ __('cms.orders.filters.reset') }}
                </a>
            </div>
        </form>

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($statusFilters as $value => $label)
                    @php
                        $isActive = $currentStatus === $value;
                        $query = [];
                        if ($value !== '') {
                            $query['status'] = $value;
                        }
                        if (! empty($filters['search'])) {
                            $query['search'] = $filters['search'];
                        }
                        if (! empty($filters['shop'])) {
                            $query['shop'] = $filters['shop'];
                        }
                        $filterUrl = route('admin.orders.index', $query);
                    @endphp
                    <a
                        href="{{ $filterUrl }}"
                        class="btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <x-admin.table
            id="orders-table"
            data-orders-table
            data-column-count="6"
            data-empty-message="{{ __('cms.dashboard.no_data') }}"
            :columns="[
                __('cms.orders.id'),
                __('cms.orders.order_date'),
                __('cms.orders.shop'),
                __('cms.orders.status'),
                __('cms.orders.total_price'),
                __('cms.orders.action'),
            ]"
        >
            @forelse ($orders as $order)
                <tr class="table-row" data-order-row="{{ $order->id }}">
                    <td class="table-cell text-sm font-semibold text-gray-900">#{{ $order->id }}</td>
                    <td class="table-cell text-sm text-gray-700">
                        {{ optional($order->created_at)->format('Y-m-d H:i') ?? '—' }}
                    </td>
                    <td class="table-cell text-sm text-gray-700">{{ $order->shop?->name ?? __('cms.orders.shop_unassigned') }}</td>
                    <td class="table-cell text-sm text-gray-700">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</td>
                    <td class="table-cell text-sm text-gray-900 font-semibold">
                        {{ number_format((float) $order->total_amount, 2) }}
                    </td>
                    <td class="table-cell text-sm text-gray-700">
                        @include('admin.orders.partials.actions', ['order' => $order])
                    </td>
                </tr>
            @empty
                <tr data-orders-empty-row>
                    <td colspan="6" class="table-cell py-6 text-center text-sm text-gray-500">
                        {{ __('cms.dashboard.no_data') }}
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        @if ($orders->hasPages())
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </x-admin.card>

    <div
        data-orders-delete-modal
        data-delete-url="{{ $deleteTemplate }}"
        data-success-title="{{ __('cms.notifications.success') }}"
        data-success-message="{{ __('cms.orders.deleted_success') }}"
        data-error-title="{{ __('cms.notifications.error') }}"
        data-error-message="{{ __('cms.orders.deleted_error') }}"
        class="fixed inset-0 z-50 hidden"
    >
        <div class="absolute inset-0 bg-gray-900/50" data-dismiss-modal></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('cms.orders.delete_confirm_title') }}</h2>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-600">{{ __('cms.orders.delete_confirm_message') }}</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900" data-order-label></p>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <button type="button" class="btn btn-outline" data-dismiss-modal>
                        {{ __('cms.orders.delete_cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" data-confirm-delete>
                        {{ __('cms.orders.delete_button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
