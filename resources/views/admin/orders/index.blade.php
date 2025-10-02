@extends('admin.layouts.admin')

@section('content')
    @php
        $datatableLang = __('cms.datatables');
        $deleteTemplate = route('admin.orders.destroy', ['order' => '__ORDER_ID__']);
    @endphp

    <x-admin.page-header :title="__('cms.orders.title')" />

    <x-admin.card noMargin class="mt-6">
        <x-admin.table
            id="orders-table"
            data-orders-table
            data-source="{{ route('admin.orders.data') }}"
            data-language='@json($datatableLang)'
            data-page-length="10"
            :columns="[
                __('cms.orders.id'),
                __('cms.orders.order_date'),
                __('cms.orders.status'),
                __('cms.orders.total_price'),
                __('cms.orders.action'),
            ]"
        ></x-admin.table>
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
