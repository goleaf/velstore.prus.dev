@extends('admin.layouts.admin')

@php
    $deleteTemplate = route('admin.coupons.destroy', ['coupon' => '__COUPON_ID__']);
    $couponStats = [
        'total' => $stats['total'] ?? 0,
        'active' => $stats['active'] ?? 0,
        'expired' => $stats['expired'] ?? 0,
    ];
@endphp

@section('content')
    <x-admin.page-header
        :title="__('cms.coupons.heading')"
        :description="__('cms.coupons.list_description')"
    >
        <x-admin.button-link href="{{ route('admin.coupons.create') }}" class="btn-primary btn-sm">
            {{ __('cms.coupons.add_new') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('cms.coupons.stats.total') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900" data-coupons-stat="total">
                {{ number_format($couponStats['total']) }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('cms.coupons.stats.active') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900" data-coupons-stat="active">
                {{ number_format($couponStats['active']) }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('cms.coupons.stats.expired') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900" data-coupons-stat="expired">
                {{ number_format($couponStats['expired']) }}
            </p>
        </div>
    </div>

    <x-admin.card class="mt-6" :title="__('cms.coupons.table_title')">
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @foreach ($statusFilters as $value => $label)
                @php
                    $isActive = $currentStatus === $value;
                    $filterUrl = $value === ''
                        ? route('admin.coupons.index')
                        : route('admin.coupons.index', ['status' => $value]);
                @endphp
                <a
                    href="{{ $filterUrl }}"
                    class="btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <x-admin.table
            data-coupons-table
            data-column-count="8"
            data-empty-message="{{ __('cms.coupons.empty') }}"
            :columns="[
                __('cms.coupons.column_coupon'),
                __('cms.coupons.discount'),
                __('cms.coupons.minimum_spend'),
                __('cms.coupons.usage_column'),
                __('cms.coupons.status'),
                __('cms.coupons.expires_at'),
                __('cms.coupons.created_at'),
                __('cms.coupons.action'),
            ]"
        >
            @forelse ($coupons as $coupon)
                @php
                    $isExpired = $coupon->isExpired();
                    $discountValue = rtrim(rtrim(number_format($coupon->discount, 2), '0'), '.');
                    $formattedDiscount = $coupon->type === 'percentage'
                        ? $discountValue.'%'
                        : $discountValue;
                @endphp
                <tr class="table-row" data-coupon-row="{{ $coupon->id }}">
                    <td class="table-cell">
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-gray-900">{{ $coupon->code }}</span>
                            <span class="text-xs text-gray-500">#{{ $coupon->id }}</span>
                        </div>
                    </td>
                    <td class="table-cell">
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-gray-900">{{ $formattedDiscount }}</span>
                            <span class="text-xs text-gray-500">{{ __('cms.coupons.type_labels.'.$coupon->type) }}</span>
                        </div>
                    </td>
                    <td class="table-cell">
                        @if ($coupon->minimum_spend)
                            <span class="text-sm font-semibold text-gray-900">{{ currency_format($coupon->minimum_spend) }}</span>
                        @else
                            <span class="text-sm text-gray-500">{{ __('cms.coupons.no_minimum_spend') }}</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        @if ($coupon->usage_limit)
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-900">{{ $coupon->usage_count }} / {{ $coupon->usage_limit }}</span>
                                <span class="text-xs text-gray-500">{{ __('cms.coupons.usage_progress_hint') }}</span>
                            </div>
                        @else
                            <span class="text-sm text-gray-500">{{ __('cms.coupons.unlimited_usage') }}</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        <span class="badge {{ $isExpired ? 'badge-danger' : 'badge-success' }}">
                            {{ $isExpired ? __('cms.coupons.status_labels.expired') : __('cms.coupons.status_labels.active') }}
                        </span>
                    </td>
                    <td class="table-cell">
                        @if ($coupon->expires_at)
                            <div class="flex flex-col">
                                <span class="text-sm text-gray-900">{{ $coupon->expires_at->format('M d, Y H:i') }}</span>
                                <span class="text-xs text-gray-500">{{ $coupon->expires_at->diffForHumans() }}</span>
                            </div>
                        @else
                            <span class="text-sm text-gray-500">{{ __('cms.coupons.no_expiry') }}</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-900">{{ optional($coupon->created_at)->format('M d, Y') ?? '—' }}</span>
                            @if ($coupon->created_at)
                                <span class="text-xs text-gray-500">{{ $coupon->created_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="table-cell">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-admin.button-link
                                href="{{ route('admin.coupons.edit', $coupon) }}"
                                class="btn-outline btn-sm"
                            >
                                {{ __('cms.coupons.edit_title') }}
                            </x-admin.button-link>
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm"
                                data-coupon-delete="{{ $coupon->id }}"
                                data-coupon-label="{{ __('cms.coupons.modal_coupon_label', ['code' => $coupon->code]) }}"
                            >
                                {{ __('cms.coupons.delete_button') }}
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr data-coupons-empty-row>
                    <td colspan="8" class="table-cell py-6 text-center text-sm text-gray-500">
                        {{ __('cms.coupons.empty') }}
                    </td>
                </tr>
            @endforelse
        </x-admin.table>

        @if ($coupons->hasPages())
            <div class="mt-4">
                {{ $coupons->onEachSide(1)->links() }}
            </div>
        @endif
    </x-admin.card>

    <div
        data-coupons-delete-modal
        data-delete-url="{{ $deleteTemplate }}"
        data-success-title="{{ __('cms.notifications.success') }}"
        data-success-message="{{ __('cms.coupons.deleted') }}"
        data-error-title="{{ __('cms.notifications.error') }}"
        data-error-message="{{ __('cms.coupons.errors.delete_failed') }}"
        class="fixed inset-0 z-50 hidden"
    >
        <div class="absolute inset-0 bg-gray-900/50" data-dismiss-modal></div>
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('cms.coupons.delete_confirm_title') }}</h2>
                </div>
                <div class="space-y-2 px-6 py-5">
                    <p class="text-sm text-gray-600">{{ __('cms.coupons.delete_confirm_message') }}</p>
                    <p class="text-sm font-semibold text-gray-900" data-coupon-label></p>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <button type="button" class="btn btn-outline" data-dismiss-modal>
                        {{ __('cms.coupons.delete_cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" data-confirm-delete>
                        {{ __('cms.coupons.delete_button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
