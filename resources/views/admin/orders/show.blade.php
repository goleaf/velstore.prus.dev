@extends('admin.layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h6 class="mb-0">{{ __('cms.orders.details_title') }} <span class="text-primary">#{{ $order->id }}</span></h6>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm">{{ __('cms.orders.back_to_orders') }}</a>
    </div>

    @php
        $statusClasses = [
            'pending' => 'badge bg-warning text-dark',
            'processing' => 'badge bg-info text-dark',
            'completed' => 'badge bg-success',
            'canceled' => 'badge bg-danger',
        ];
        $statusClass = $statusClasses[$order->status] ?? 'badge bg-secondary';
        $itemsTotal = $order->details->reduce(function ($carry, $detail) {
            return $carry + ($detail->quantity * (float) $detail->price);
        }, 0);
    @endphp

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white">
            <h6 class="mb-0">{{ __('cms.orders.summary') }}</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <p class="mb-1 text-muted">{{ __('cms.orders.placed_at') }}</p>
                    <p class="fw-semibold">{{ optional($order->created_at)->format('Y-m-d H:i') ?? __('cms.orders.not_available') }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted">{{ __('cms.orders.status') }}</p>
                    <span class="{{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted">{{ __('cms.orders.total_amount') }}</p>
                    <p class="fw-semibold">{{ number_format((float) $order->total_amount, 2) }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-muted">{{ __('cms.orders.items_total') }}</p>
                    <p class="fw-semibold">{{ number_format($itemsTotal, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3 g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header card-header-bg text-white">
                    <h6 class="mb-0">{{ __('cms.orders.customer_info') }}</h6>
                </div>
                <div class="card-body">
                    @if ($order->customer)
                        <p class="mb-2"><span class="text-muted">{{ __('cms.orders.customer_name') }}:</span>
                            <span class="fw-semibold">{{ $order->customer->name }}</span></p>
                        <p class="mb-2"><span class="text-muted">{{ __('cms.orders.customer_email') }}:</span>
                            <span class="fw-semibold">{{ $order->customer->email }}</span></p>
                        <p class="mb-0"><span class="text-muted">{{ __('cms.orders.customer_phone') }}:</span>
                            <span class="fw-semibold">{{ $order->customer->phone ?? __('cms.orders.not_available') }}</span></p>
                    @else
                        <p class="mb-2 text-muted">{{ __('cms.orders.customer_guest') }}</p>
                        <p class="mb-0"><span class="text-muted">{{ __('cms.orders.guest_email') }}:</span>
                            <span class="fw-semibold">{{ $order->guest_email ?? __('cms.orders.not_available') }}</span></p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header card-header-bg text-white">
                    <h6 class="mb-0">{{ __('cms.orders.shipping') }}</h6>
                </div>
                <div class="card-body">
                    @if ($order->shippingAddress)
                        <p class="mb-2"><span class="text-muted">{{ __('cms.orders.customer_name') }}:</span>
                            <span class="fw-semibold">{{ $order->shippingAddress->name }}</span></p>
                        <p class="mb-2"><span class="text-muted">{{ __('cms.orders.customer_phone') }}:</span>
                            <span class="fw-semibold">{{ $order->shippingAddress->phone }}</span></p>
                        <p class="mb-2"><span class="text-muted">{{ __('cms.orders.address') }}:</span>
                            <span class="fw-semibold">{{ $order->shippingAddress->address }}</span></p>
                        <p class="mb-2"><span class="text-muted">{{ __('cms.orders.city') }}:</span>
                            <span class="fw-semibold">{{ $order->shippingAddress->city }}</span></p>
                        <p class="mb-2"><span class="text-muted">{{ __('cms.orders.postal_code') }}:</span>
                            <span class="fw-semibold">{{ $order->shippingAddress->postal_code }}</span></p>
                        <p class="mb-0"><span class="text-muted">{{ __('cms.orders.country') }}:</span>
                            <span class="fw-semibold">{{ $order->shippingAddress->country }}</span></p>
                    @else
                        <p class="mb-0 text-muted">{{ __('cms.orders.shipping_none') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white">
            <h6 class="mb-0">{{ __('cms.orders.items') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('cms.orders.product') }}</th>
                            <th>{{ __('cms.orders.sku') }}</th>
                            <th class="text-center">{{ __('cms.orders.quantity') }}</th>
                            <th class="text-end">{{ __('cms.orders.unit_price') }}</th>
                            <th class="text-end">{{ __('cms.orders.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->details as $detail)
                            @php
                                $product = $detail->product;
                                $productName = $product ? (optional($product->translation)->name ?? $product->slug) : __('cms.orders.product_missing');
                                $unitPrice = (float) $detail->price;
                                $lineTotal = $unitPrice * $detail->quantity;
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $productName }}</span>
                                    @if ($product && $product->brand)
                                        <small class="d-block text-muted">{{ optional($product->brand->translation)->name ?? $product->brand->slug }}</small>
                                    @endif
                                </td>
                                <td>{{ optional($product)->SKU ?? '—' }}</td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-end">{{ number_format($unitPrice, 2) }}</td>
                                <td class="text-end">{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('cms.orders.items_empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">{{ __('cms.orders.items_total') }}</th>
                            <th class="text-end">{{ number_format($itemsTotal, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('cms.orders.payments') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('cms.orders.payment_gateway') }}</th>
                            <th>{{ __('cms.orders.payment_status') }}</th>
                            <th>{{ __('cms.orders.payment_amount') }}</th>
                            <th>{{ __('cms.orders.payment_transaction') }}</th>
                            <th>{{ __('cms.orders.payment_date') }}</th>
                            <th>{{ __('cms.orders.refunds') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->payments as $payment)
                            <tr>
                                <td>{{ $payment->gateway->name ?? __('cms.orders.not_available') }}</td>
                                <td>{{ ucfirst($payment->status) }}</td>
                                <td>{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                                <td>{{ $payment->transaction_id ?? '—' }}</td>
                                <td>{{ optional($payment->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    @if ($payment->refunds->isNotEmpty())
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($payment->refunds as $refund)
                                                <li>
                                                    <span class="fw-semibold">{{ number_format((float) $refund->amount, 2) }} {{ $refund->currency }}</span>
                                                    <small class="text-muted">{{ ucfirst($refund->status) }} • {{ optional($refund->created_at)->format('Y-m-d H:i') }}</small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">{{ __('cms.orders.refunds_none') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">{{ __('cms.orders.payments_none') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
