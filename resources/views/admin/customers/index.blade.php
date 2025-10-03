@extends('admin.layouts.admin')

@php
    $statusBadges = [
        'active' => 'badge badge-success',
        'inactive' => 'badge badge-danger',
    ];
@endphp

@section('content')
    <x-admin.page-header
        :title="__('cms.customers.index_title')"
        :description="__('cms.customers.index_description')"
    >
        <x-admin.button-link href="{{ route('admin.customers.create') }}" class="btn-primary">
            {{ __('cms.customers.create_button_short') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <x-admin.card class="mt-6">
        <div class="grid gap-6">
            <form method="GET" action="{{ route('admin.customers.index') }}" class="grid gap-4 lg:grid-cols-[2fr,1fr,1fr,auto]">
                <div>
                    <label for="search" class="form-label">{{ __('cms.customers.search_label') }}</label>
                    <input
                        id="search"
                        type="search"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="{{ __('cms.customers.search_placeholder') }}"
                        class="form-control"
                    >
                </div>

                <div>
                    <label for="status" class="form-label">{{ __('cms.customers.filter_status_label') }}</label>
                    <select id="status" name="status" class="form-select">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="shop_id" class="form-label">{{ __('cms.customers.filter_shop_label') }}</label>
                    <select id="shop_id" name="shop_id" class="form-select">
                        <option value="0">{{ __('cms.customers.filter_shop_all') }}</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" @selected($filters['shop_id'] === $shop->id)>
                                {{ $shop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="btn btn-primary">
                        {{ __('cms.customers.apply_filters') }}
                    </button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">
                        {{ __('cms.customers.reset_filters') }}
                    </a>
                </div>
            </form>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('cms.customers.metric_total') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($customers->total()) }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('cms.customers.metric_total_hint') }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('cms.customers.metric_active') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($statusCounts['active']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('cms.customers.metric_active_hint') }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('cms.customers.metric_inactive') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($statusCounts['inactive']) }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('cms.customers.metric_inactive_hint') }}</p>
                </div>
            </div>

            <x-admin.table
                data-customers-table
                data-column-count="7"
                data-empty-message="{{ __('cms.customers.empty_state_message') }}"
                :columns="[
                    __('cms.customers.id'),
                    __('cms.customers.name'),
                    __('cms.customers.email'),
                    __('cms.customers.phone'),
                    __('cms.customers.shops_column'),
                    __('cms.customers.status'),
                    __('cms.customers.actions'),
                ]"
            >
                @forelse ($customers as $customer)
                    <tr class="table-row">
                        <td class="table-cell font-semibold">#{{ $customer->id }}</td>
                        <td class="table-cell">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900">{{ $customer->name }}</span>
                                <span class="text-xs text-gray-500">{{ $customer->primary_address_line ?? __('cms.customers.not_available') }}</span>
                            </div>
                        </td>
                        <td class="table-cell">
                            <a href="mailto:{{ $customer->email }}" class="text-primary-600 hover:text-primary-700">
                                {{ $customer->email }}
                            </a>
                        </td>
                        <td class="table-cell">
                            {{ $customer->phone ?: __('cms.customers.not_available') }}
                        </td>
                        <td class="table-cell">
                            @php
                                $shopNames = $customer->shops->pluck('name');
                            @endphp
                            @if ($shopNames->isEmpty())
                                <span class="text-sm text-gray-500">{{ __('cms.customers.no_shops_assigned') }}</span>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($shopNames->take(3) as $name)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                            {{ $name }}
                                        </span>
                                    @endforeach
                                    @if ($shopNames->count() > 3)
                                        <span class="text-xs text-gray-500">{{ trans_choice('cms.customers.additional_shops_count', $shopNames->count() - 3, ['count' => $shopNames->count() - 3]) }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="table-cell">
                            @php
                                $status = $customer->status;
                                $badgeClass = $statusBadges[$status] ?? 'badge';
                                $statusLabel = $status === 'active'
                                    ? __('cms.customers.active')
                                    : __('cms.customers.inactive');
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="table-cell">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline">
                                    {{ __('cms.customers.view_button') }}
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-customer-delete="{{ $customer->id }}"
                                    data-customer-label="{{ $customer->name }}"
                                >
                                    {{ __('cms.customers.delete_button') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-customers-empty-row>
                        <td colspan="7" class="table-cell py-6 text-center text-sm text-gray-500">
                            {{ __('cms.customers.empty_state_message') }}
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>

            @if ($customers->hasPages())
                <div>
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </x-admin.card>

    <div
        data-customer-delete-modal
        data-delete-url="{{ route('admin.customers.destroy', ['customer' => '__CUSTOMER_ID__']) }}"
        data-success-title="{{ __('cms.customers.success_title') }}"
        data-success-message="{{ __('cms.customers.delete_success_message') }}"
        data-error-title="{{ __('cms.customers.error_title') }}"
        data-error-message="{{ __('cms.customers.delete_error_message') }}"
        class="fixed inset-0 z-40 hidden"
    >
        <div class="absolute inset-0 bg-gray-900/60" data-dismiss-modal></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        {{ __('cms.customers.confirm_delete_title') }}
                    </h2>
                </div>
                <div class="px-6 py-5 space-y-2">
                    <p class="text-sm text-gray-600">{{ __('cms.customers.confirm_delete_message') }}</p>
                    <p class="text-sm font-semibold text-gray-900" data-customer-label></p>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <button type="button" class="btn btn-outline" data-dismiss-modal>
                        {{ __('cms.customers.cancel_button') }}
                    </button>
                    <button type="button" class="btn btn-danger" data-confirm-delete>
                        {{ __('cms.customers.delete_button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
