@extends('admin.layouts.admin')

@php
    $deleteTemplate = route('admin.payments.destroy', ['payment' => '__PAYMENT_ID__']);
@endphp

@section('content')
    <x-admin.page-header
        :title="__('cms.payments.title')"
        :description="__('cms.payments.index_description')"
    />

    <x-admin.card class="mt-6">
        <div class="grid gap-6">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="filter-status" class="form-label">{{ __('cms.payments.status') }}</label>
                    <select id="filter-status" name="status" class="form-select">
                        <option value="">{{ __('cms.payments.filters_all_statuses') }}</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-gateway" class="form-label">{{ __('cms.payment_gateways.title') }}</label>
                    <select id="filter-gateway" name="gateway_id" class="form-select">
                        <option value="">{{ __('cms.payments.filters_all_gateways') }}</option>
                        @foreach ($gateways as $gateway)
                            <option value="{{ $gateway->id }}" @selected($filters['gateway_id'] === (string) $gateway->id)>
                                {{ $gateway->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-shop" class="form-label">{{ __('cms.payments.shops') }}</label>
                    <select id="filter-shop" name="shop_id" class="form-select">
                        <option value="">{{ __('cms.payments.filters_all_shops') }}</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" @selected($filters['shop_id'] === (string) $shop->id)>
                                {{ $shop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-date-from" class="form-label">{{ __('cms.payments.filters_date_from') }}</label>
                    <input
                        id="filter-date-from"
                        type="date"
                        name="date_from"
                        value="{{ $filters['date_from'] }}"
                        class="form-control"
                    >
                </div>

                <div>
                    <label for="filter-date-to" class="form-label">{{ __('cms.payments.filters_date_to') }}</label>
                    <input
                        id="filter-date-to"
                        type="date"
                        name="date_to"
                        value="{{ $filters['date_to'] }}"
                        class="form-control"
                    >
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="btn btn-primary w-full md:w-auto">
                        {{ __('cms.payments.filters_apply') }}
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline w-full md:w-auto">
                        {{ __('cms.payments.filters_reset') }}
                    </a>
                </div>
            </form>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('cms.payments.metric_total') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($metrics['total']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('cms.payments.metric_total_hint') }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('cms.payments.metric_completed') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($metrics['completed']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('cms.payments.metric_completed_hint') }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('cms.payments.metric_failed') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($metrics['failed']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('cms.payments.metric_failed_hint') }}</p>
                </div>
            </div>

            <x-admin.table
                data-payments-table
                data-column-count="10"
                data-empty-message="{{ __('cms.payments.empty_state') }}"
                :columns="[
                    __('cms.payments.id'),
                    __('cms.payments.order'),
                    __('cms.payments.user'),
                    __('cms.payments.shops'),
                    __('cms.payments.gateway'),
                    __('cms.payments.amount'),
                    __('cms.payments.status'),
                    __('cms.payments.transaction'),
                    __('cms.payments.created_at'),
                    __('cms.payments.action'),
                ]"
            >
                @forelse ($payments as $payment)
                    <tr class="table-row" data-payment-row="{{ $payment->id }}">
                        <td class="table-cell font-semibold">#{{ $payment->id }}</td>
                        <td class="table-cell">
                            @if ($payment->order)
                                <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-primary-600 hover:text-primary-700">
                                    #{{ $payment->order->id }}
                                </a>
                            @else
                                <span class="text-gray-500">{{ __('cms.payments.not_available') }}</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            {{ $payment->customer_display_name ?? __('cms.payments.not_available') }}
                        </td>
                        <td class="table-cell">
                            @if (!empty($payment->shop_names))
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($payment->shop_names as $shopName)
                                        <span class="badge badge-gray">{{ $shopName }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-500">{{ __('cms.payments.not_available') }}</span>
                            @endif
                        </td>
                        <td class="table-cell">{{ $payment->gateway->name ?? __('cms.payments.not_available') }}</td>
                        <td class="table-cell font-semibold text-gray-900">
                            {{ number_format((float) $payment->amount, 2) }}
                            @if ($payment->currency)
                                <span class="text-xs text-gray-500">{{ $payment->currency }}</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @php
                                $statusKey = $payment->status ?? 'unknown';
                                $badgeClass = $statusBadges[$statusKey] ?? 'badge badge-gray';
                                $translationKey = 'cms.payments.' . $statusKey;
                                $statusLabel = __($translationKey);

                                if ($statusLabel === $translationKey) {
                                    $statusLabel = ucfirst(str_replace('_', ' ', (string) $statusKey));
                                }
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="table-cell">{{ $payment->transaction_id ?? __('cms.payments.not_available') }}</td>
                        <td class="table-cell">{{ optional($payment->created_at)->format('d M Y, h:i A') ?? __('cms.payments.not_available') }}</td>
                        <td class="table-cell">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline">
                                    {{ __('cms.payments.view_details') }}
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-payment-delete="{{ $payment->id }}"
                                    data-payment-label="#{{ $payment->id }}"
                                >
                                    {{ __('cms.payments.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-payments-empty-row>
                        <td colspan="10" class="table-cell py-6 text-center text-sm text-gray-500">
                            {{ __('cms.payments.empty_state') }}
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>

            @if ($payments->hasPages())
                <div>
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </x-admin.card>

    <div
        data-payment-delete-modal
        data-delete-url="{{ $deleteTemplate }}"
        data-success-title="{{ __('cms.notifications.success') }}"
        data-success-message="{{ __('cms.payments.deleted') }}"
        data-error-title="{{ __('cms.notifications.error') }}"
        data-error-message="{{ __('cms.payments.delete_error') }}"
        class="fixed inset-0 z-50 hidden"
    >
        <div class="absolute inset-0 bg-gray-900/50" data-dismiss-modal></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('cms.payments.delete_confirm_title') }}</h2>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-600">{{ __('cms.payments.delete_confirm_message') }}</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900" data-payment-label></p>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <button type="button" class="btn btn-outline" data-dismiss-modal>
                        {{ __('cms.payments.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" data-confirm-delete>
                        {{ __('cms.payments.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
