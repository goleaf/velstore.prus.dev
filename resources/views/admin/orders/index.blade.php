@extends('admin.layouts.admin')

@section('content')
    @php
        $deleteTemplate = route('admin.orders.destroy', ['id' => '__ORDER_ID__']);
    @endphp

    <x-admin.page-header :title="__('cms.orders.title')" />

    <x-admin.card noMargin class="mt-6">
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($statusFilters as $value => $label)
                    @php
                        $isActive = $currentStatus === $value;
                        $filterUrl = $value === ''
                            ? route('admin.orders.index')
                            : route('admin.orders.index', ['status' => $value]);
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
            data-column-count="5"
            data-empty-message="{{ __('cms.dashboard.no_data') }}"
            :columns="[
                __('cms.orders.id'),
                __('cms.orders.order_date'),
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
                    <td class="table-cell text-sm text-gray-700">{{ ucfirst($order->status) }}</td>
                    <td class="table-cell text-sm text-gray-900 font-semibold">
                        {{ number_format((float) $order->total_amount, 2) }}
                    </td>
                    <td class="table-cell text-sm text-gray-700">
                        @include('admin.orders.partials.actions', ['order' => $order])
                    </td>
                </tr>
            @empty
                <tr data-orders-empty-row>
                    <td colspan="5" class="table-cell py-6 text-center text-sm text-gray-500">
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
