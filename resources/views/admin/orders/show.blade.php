@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header :title="__('cms.orders.details_title') . ' #' . $order->id">
        <x-admin.button-link href="{{ route('admin.orders.index') }}" class="btn-outline btn-sm">
            {{ __('cms.orders.back_to_orders') }}
        </x-admin.button-link>
    </x-admin.page-header>

    @php
        $statusClasses = [
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'completed' => 'badge-success',
            'canceled' => 'badge-danger',
        ];
        $statusClass = $statusClasses[$order->status] ?? 'badge-gray';
        $itemsTotal = $order->details->reduce(fn ($carry, $detail) => $carry + ($detail->quantity * (float) $detail->price), 0);
        $statusLabel = __('cms.orders.status_labels.' . $order->status);
    @endphp

    <x-admin.card class="mt-6" :title="__('cms.orders.summary')">
        <dl class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.orders.placed_at') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ optional($order->created_at)->format('Y-m-d H:i') ?? __('cms.orders.not_available') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.orders.shop') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $order->shop?->name ?? __('cms.orders.shop_unassigned') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.orders.status') }}</dt>
                <dd class="mt-1">
                    <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.orders.total_amount') }}</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">
                    {{ number_format((float) $order->total_amount, 2) }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.orders.items_total') }}</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">
                    {{ number_format($itemsTotal, 2) }}
                </dd>
            </div>
        </dl>
    </x-admin.card>

    <div class="grid gap-6 mt-6 lg:grid-cols-2">
        <x-admin.card noMargin :title="__('cms.orders.customer_info')">
            <div class="space-y-3 text-sm text-gray-700">
                @if ($order->customer)
                    <div>
                        <p class="font-medium text-gray-900">{{ $order->customer->name }}</p>
                        <p class="text-gray-500">{{ $order->customer->email }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('cms.orders.customer_phone') }}:</span>
                        <span class="ml-1 font-medium text-gray-900">{{ $order->customer->phone ?? __('cms.orders.not_available') }}</span>
                    </div>
                @else
                    <p class="text-gray-500">{{ __('cms.orders.customer_guest') }}</p>
                    <p class="font-medium text-gray-900">{{ $order->guest_email ?? __('cms.orders.not_available') }}</p>
                @endif
            </div>
        </x-admin.card>

        <x-admin.card noMargin :title="__('cms.orders.shipping')">
            <div class="space-y-3 text-sm text-gray-700">
                @if ($order->shippingAddress)
                    <div>
                        <span class="text-gray-500">{{ __('cms.orders.customer_name') }}:</span>
                        <span class="ml-1 font-medium text-gray-900">{{ $order->shippingAddress->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('cms.orders.customer_phone') }}:</span>
                        <span class="ml-1 font-medium text-gray-900">{{ $order->shippingAddress->phone }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('cms.orders.address') }}:</span>
                        <span class="ml-1 font-medium text-gray-900">{{ $order->shippingAddress->address }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('cms.orders.city') }}:</span>
                        <span class="ml-1 font-medium text-gray-900">{{ $order->shippingAddress->city }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('cms.orders.postal_code') }}:</span>
                        <span class="ml-1 font-medium text-gray-900">{{ $order->shippingAddress->postal_code }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('cms.orders.country') }}:</span>
                        <span class="ml-1 font-medium text-gray-900">{{ $order->shippingAddress->country }}</span>
                    </div>
                @else
                    <p class="text-gray-500">{{ __('cms.orders.shipping_none') }}</p>
                @endif
            </div>
        </x-admin.card>
    </div>

    <x-admin.card class="mt-6" :title="__('cms.orders.items')">
        <x-admin.table
            :columns="[
                __('cms.orders.product'),
                __('cms.orders.sku'),
                __('cms.orders.quantity'),
                __('cms.orders.unit_price'),
                __('cms.orders.subtotal'),
            ]"
        >
            @forelse ($order->details as $detail)
                @php
                    $product = $detail->product;
                    $productName = $product?->translation?->name ?? $product?->slug ?? __('cms.orders.product_missing');
                    $brandName = optional($product?->brand?->translation)->name ?? $product?->brand?->slug;
                    $unitPrice = (float) $detail->price;
                    $lineTotal = $unitPrice * $detail->quantity;
                @endphp
                <tr class="table-row">
                    <td class="table-cell align-top">
                        <p class="font-medium text-gray-900">{{ $productName }}</p>
                        @if ($brandName)
                            <p class="text-xs text-gray-500">{{ $brandName }}</p>
                        @endif
                    </td>
                    <td class="table-cell align-top text-sm text-gray-600">{{ $product?->SKU ?? '—' }}</td>
                    <td class="table-cell align-top text-center text-sm text-gray-700">{{ $detail->quantity }}</td>
                    <td class="table-cell align-top text-right text-sm text-gray-700">{{ number_format($unitPrice, 2) }}</td>
                    <td class="table-cell align-top text-right text-sm text-gray-900 font-semibold">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="table-cell text-center text-gray-500 py-6">{{ __('cms.orders.items_empty') }}</td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-4 flex justify-end text-sm font-semibold text-gray-900">
            <span>{{ __('cms.orders.items_total') }}: {{ number_format($itemsTotal, 2) }}</span>
        </div>
    </x-admin.card>

    <x-admin.card class="mt-6" :title="__('cms.orders.payments')">
        <x-admin.table
            :columns="[
                __('cms.orders.payment_gateway'),
                __('cms.orders.payment_status'),
                __('cms.orders.payment_amount'),
                __('cms.orders.payment_transaction'),
                __('cms.orders.payment_date'),
                __('cms.orders.refunds'),
            ]"
        >
            @forelse ($order->payments as $payment)
                <tr class="table-row">
                    <td class="table-cell text-sm text-gray-700">{{ $payment->gateway->name ?? __('cms.orders.not_available') }}</td>
                    <td class="table-cell text-sm text-gray-700">{{ ucfirst($payment->status) }}</td>
                    <td class="table-cell text-sm text-gray-700">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                    <td class="table-cell text-sm text-gray-700">{{ $payment->transaction_id ?? '—' }}</td>
                    <td class="table-cell text-sm text-gray-700">{{ optional($payment->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="table-cell text-sm text-gray-700">
                        @if ($payment->refunds->isNotEmpty())
                            <ul class="space-y-2">
                                @foreach ($payment->refunds as $refund)
                                    <li>
                                        <p class="font-medium text-gray-900">
                                            {{ number_format((float) $refund->amount, 2) }} {{ $refund->currency }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ ucfirst($refund->status) }} · {{ optional($refund->created_at)->format('Y-m-d H:i') }}
                                        </p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-gray-500">{{ __('cms.orders.refunds_none') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="table-cell text-center text-gray-500 py-6">{{ __('cms.orders.payments_none') }}</td>
                </tr>
            @endforelse
        </x-admin.table>
    </x-admin.card>
@endsection
