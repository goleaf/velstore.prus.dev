@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header
        :title="__('cms.orders.create_title')"
        :description="__('cms.orders.create_description')"
    >
        <x-admin.button-link href="{{ route('admin.orders.index') }}" class="btn-outline">
            {{ __('cms.orders.back_to_orders') }}
        </x-admin.button-link>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4">
            <h3 class="text-sm font-semibold text-red-700">
                {{ __('cms.orders.validation.error_heading') }}
            </h3>
            <ul class="mt-2 list-inside list-disc text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $defaultShopId = old('shop_id');
        if (! $defaultShopId && $shops->count() === 1) {
            $defaultShopId = $shops->first()->id;
        }

        $itemErrorMessages = collect($errors->getMessages())->filter(fn ($messages, $key) => str_starts_with($key, 'items.'));
        $firstItemError = $errors->first('items') ?? $itemErrorMessages->flatten()->first();
    @endphp

    <x-admin.card class="mt-6">
        <form
            method="POST"
            action="{{ route('admin.orders.store') }}"
            class="grid gap-6"
            data-order-form
            data-products='@json($productOptions)'
            data-old-items='@json(old('items', []))'
            data-product-placeholder="{{ __('cms.orders.items_product_placeholder') }}"
            data-initial-shop="{{ $defaultShopId }}"
            data-empty-text="{{ __('cms.orders.items_empty_hint') }}"
            data-select-shop-text="{{ __('cms.orders.items_select_shop_hint') }}"
            data-no-products-text="{{ __('cms.orders.items_no_products_hint') }}"
        >
            @csrf

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                            {{ __('cms.orders.form_section_basics') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('cms.orders.form_section_basics_hint') }}
                        </p>
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label for="shop_id" class="form-label">{{ __('cms.orders.shop') }}</label>
                            <select
                                id="shop_id"
                                name="shop_id"
                                class="form-select @error('shop_id') is-invalid @enderror"
                                required
                                data-order-shop
                            >
                                <option value="">{{ __('cms.orders.shop_select_placeholder') }}</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" @selected((int) $defaultShopId === $shop->id)>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shop_id')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_id" class="form-label">{{ __('cms.orders.customer') }}</label>
                            <select
                                id="customer_id"
                                name="customer_id"
                                class="form-select @error('customer_id') is-invalid @enderror"
                            >
                                <option value="">{{ __('cms.orders.customer_select_placeholder') }}</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected((int) old('customer_id') === $customer->id)>
                                        {{ $customer->name ?? $customer->email }}
                                        @if ($customer->email)
                                            ({{ $customer->email }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="guest_email" class="form-label">{{ __('cms.orders.guest_email') }}</label>
                            <input
                                id="guest_email"
                                type="email"
                                name="guest_email"
                                value="{{ old('guest_email') }}"
                                maxlength="255"
                                class="form-control @error('guest_email') is-invalid @enderror"
                                placeholder="guest@example.com"
                            >
                            <p class="mt-1 text-xs text-gray-500">{{ __('cms.orders.guest_email_hint') }}</p>
                            @error('guest_email')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="form-label">{{ __('cms.orders.status') }}</label>
                            <select
                                id="status"
                                name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required
                            >
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'pending') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                            {{ __('cms.orders.form_section_shipping') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('cms.orders.form_section_shipping_hint') }}
                        </p>
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label for="shipping_name" class="form-label">{{ __('cms.orders.customer_name') }}</label>
                            <input
                                id="shipping_name"
                                type="text"
                                name="shipping[name]"
                                value="{{ old('shipping.name') }}"
                                maxlength="255"
                                class="form-control @error('shipping.name') is-invalid @enderror"
                            >
                            @error('shipping.name')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_phone" class="form-label">{{ __('cms.orders.customer_phone') }}</label>
                            <input
                                id="shipping_phone"
                                type="text"
                                name="shipping[phone]"
                                value="{{ old('shipping.phone') }}"
                                maxlength="50"
                                class="form-control @error('shipping.phone') is-invalid @enderror"
                            >
                            @error('shipping.phone')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_address" class="form-label">{{ __('cms.orders.address') }}</label>
                            <input
                                id="shipping_address"
                                type="text"
                                name="shipping[address]"
                                value="{{ old('shipping.address') }}"
                                maxlength="255"
                                class="form-control @error('shipping.address') is-invalid @enderror"
                            >
                            @error('shipping.address')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label for="shipping_city" class="form-label">{{ __('cms.orders.city') }}</label>
                                <input
                                    id="shipping_city"
                                    type="text"
                                    name="shipping[city]"
                                    value="{{ old('shipping.city') }}"
                                    maxlength="120"
                                    class="form-control @error('shipping.city') is-invalid @enderror"
                                >
                                @error('shipping.city')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="shipping_postal_code" class="form-label">{{ __('cms.orders.postal_code') }}</label>
                                <input
                                    id="shipping_postal_code"
                                    type="text"
                                    name="shipping[postal_code]"
                                    value="{{ old('shipping.postal_code') }}"
                                    maxlength="30"
                                    class="form-control @error('shipping.postal_code') is-invalid @enderror"
                                >
                                @error('shipping.postal_code')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="shipping_country" class="form-label">{{ __('cms.orders.country') }}</label>
                                <input
                                    id="shipping_country"
                                    type="text"
                                    name="shipping[country]"
                                    value="{{ old('shipping.country') }}"
                                    maxlength="120"
                                    class="form-control @error('shipping.country') is-invalid @enderror"
                                >
                                @error('shipping.country')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                            {{ __('cms.orders.items_section_title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('cms.orders.items_section_hint') }}
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" data-add-item disabled>
                        {{ __('cms.orders.items_add') }}
                    </button>
                </div>

                @if ($firstItemError)
                    <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        {{ $firstItemError }}
                    </div>
                @endif

                <div class="space-y-4" data-order-items></div>

                <p class="hidden text-sm text-gray-500" data-order-empty-state>
                    {{ __('cms.orders.items_empty_hint') }}
                </p>

                <template data-order-item-template>
                    <div class="grid gap-4 md:grid-cols-[minmax(0,2fr)_repeat(2,minmax(0,1fr))_minmax(0,1fr)_auto]" data-order-item>
                        <div>
                            <label class="form-label">{{ __('cms.orders.product') }}</label>
                            <select class="form-select" data-item-product data-name-template="items[__INDEX__][product_id]" required>
                                <option value="">{{ __('cms.orders.items_product_placeholder') }}</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500" data-item-sku></p>
                        </div>
                        <div>
                            <label class="form-label">{{ __('cms.orders.quantity') }}</label>
                            <input
                                type="number"
                                min="1"
                                value="1"
                                class="form-control"
                                data-item-quantity
                                data-name-template="items[__INDEX__][quantity]"
                                required
                            >
                        </div>
                        <div>
                            <label class="form-label">{{ __('cms.orders.unit_price') }}</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control"
                                data-item-unit-price
                                data-name-template="items[__INDEX__][unit_price]"
                            >
                        </div>
                        <div class="flex flex-col justify-center">
                            <span class="text-sm font-semibold text-gray-900" data-item-subtotal>0.00</span>
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-remove-item>
                                {{ __('cms.orders.items_remove') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex flex-col items-end gap-3 border-t border-gray-200 pt-4">
                <div class="text-sm">
                    <span class="font-semibold text-gray-900">{{ __('cms.orders.items_total') }}:</span>
                    <span class="ml-2 font-semibold text-gray-900" data-order-total>0.00</span>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn btn-primary">
                        {{ __('cms.orders.save_button') }}
                    </button>
                    <x-admin.button-link href="{{ route('admin.orders.index') }}" class="btn-outline">
                        {{ __('cms.orders.cancel_button') }}
                    </x-admin.button-link>
                </div>
            </div>
        </form>
    </x-admin.card>
@endsection
