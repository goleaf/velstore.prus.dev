@extends('admin.layouts.admin')

@php use Illuminate\Support\Str; @endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h6 class="mb-0">{{ __('cms.customers.details_title') }} <span class="text-primary">#{{ $customer->id }}</span></h6>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-light btn-sm">{{ __('cms.customers.back_to_list') }}</a>
    </div>

    @php
        $ordersCount = $customer->orders->count();
        $totalSpent = $customer->orders->sum('total_amount');
        $wishlistCount = $customer->wishlists->count();
        $reviewsCount = $customer->reviews->count();
        $statusLabel = $customer->status === 'active' ? __('cms.customers.active') : __('cms.customers.inactive');
        $statusClass = $customer->status === 'active' ? 'badge bg-success' : 'badge bg-danger';
        $shippingAddresses = $customer->shippingAddresses->unique('id');
    @endphp

    <div class="row mt-3 g-3">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-1">{{ __('cms.customers.orders_count') }}</p>
                    <h4 class="mb-0">{{ $ordersCount }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-1">{{ __('cms.customers.total_spent') }}</p>
                    <h4 class="mb-0">{{ number_format((float) $totalSpent, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-1">{{ __('cms.customers.wishlist_count') }}</p>
                    <h4 class="mb-0">{{ $wishlistCount }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-1">{{ __('cms.customers.reviews_count') }}</p>
                    <h4 class="mb-0">{{ $reviewsCount }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('cms.customers.basic_information') }}</h6>
            <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <p class="text-muted small mb-1">{{ __('cms.customers.name') }}</p>
                    <p class="fw-semibold mb-0">{{ $customer->name }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-muted small mb-1">{{ __('cms.customers.email') }}</p>
                    <p class="fw-semibold mb-0">{{ $customer->email }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-muted small mb-1">{{ __('cms.customers.phone') }}</p>
                    <p class="fw-semibold mb-0">{{ $customer->phone ?? __('cms.customers.not_available') }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small mb-1">{{ __('cms.customers.address') }}</p>
                    <p class="fw-semibold mb-0">{{ $customer->address ?? __('cms.customers.not_available') }}</p>
                </div>
                <div class="col-md-3">
                    <p class="text-muted small mb-1">{{ __('cms.customers.registered_at') }}</p>
                    <p class="fw-semibold mb-0">{{ optional($customer->created_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}</p>
                </div>
                <div class="col-md-3">
                    <p class="text-muted small mb-1">{{ __('cms.customers.last_updated') }}</p>
                    <p class="fw-semibold mb-0">{{ optional($customer->updated_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white">
            <h6 class="mb-0">{{ __('cms.customers.shipping_addresses') }}</h6>
        </div>
        <div class="card-body">
            @if ($shippingAddresses->isEmpty())
                <p class="text-muted mb-0">{{ __('cms.customers.no_addresses') }}</p>
            @else
                <div class="row g-3">
                    @foreach ($shippingAddresses as $address)
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <p class="fw-semibold mb-1">{{ $address->name ?? __('cms.customers.not_available') }}</p>
                                <p class="mb-1 text-muted">{{ $address->phone ?? __('cms.customers.not_available') }}</p>
                                <p class="mb-1">{{ $address->address ?? __('cms.customers.not_available') }}</p>
                                @php
                                    $location = collect([$address->city, $address->postal_code, $address->country])->filter()->implode(', ');
                                @endphp
                                <p class="mb-0 text-muted">{{ $location !== '' ? $location : __('cms.customers.not_available') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white">
            <h6 class="mb-0">{{ __('cms.customers.orders') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('cms.customers.orders_table_id') }}</th>
                            <th>{{ __('cms.customers.orders_table_status') }}</th>
                            <th>{{ __('cms.customers.orders_table_total') }}</th>
                            <th>{{ __('cms.customers.orders_table_items') }}</th>
                            <th>{{ __('cms.customers.orders_table_placed_at') }}</th>
                            <th>{{ __('cms.customers.orders_table_payment_status') }}</th>
                            <th>{{ __('cms.customers.orders_table_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customer->orders as $order)
                            @php
                                $itemsCount = $order->details->sum('quantity');
                                $latestPayment = $order->latestPayment;
                                $paymentStatus = $latestPayment?->status ? ucfirst($latestPayment->status) : __('cms.customers.not_available');
                            @endphp
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ ucfirst($order->status) }}</td>
                                <td>{{ number_format((float) $order->total_amount, 2) }}</td>
                                <td>{{ $itemsCount }}</td>
                                <td>{{ optional($order->created_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}</td>
                                <td>{{ $paymentStatus }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                        {{ __('cms.customers.view_order') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('cms.customers.no_orders') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white">
            <h6 class="mb-0">{{ __('cms.customers.wishlist_title') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('cms.customers.wishlist_product') }}</th>
                            <th>{{ __('cms.customers.wishlist_added_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customer->wishlists as $wishlist)
                            <tr>
                                <td>{{ $wishlist->product?->translation?->name ?? __('cms.customers.product_missing') }}</td>
                                <td>{{ optional($wishlist->created_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">{{ __('cms.customers.no_wishlists') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3 mb-5">
        <div class="card-header card-header-bg text-white">
            <h6 class="mb-0">{{ __('cms.customers.reviews_title') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('cms.customers.reviews_product') }}</th>
                            <th>{{ __('cms.customers.reviews_rating') }}</th>
                            <th>{{ __('cms.customers.reviews_status') }}</th>
                            <th>{{ __('cms.customers.reviews_submitted_at') }}</th>
                            <th>{{ __('cms.customers.reviews_content') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customer->reviews as $review)
                            <tr>
                                <td>{{ $review->product?->translation?->name ?? __('cms.customers.product_missing') }}</td>
                                <td>
                                    @if (is_null($review->rating))
                                        {{ __('cms.customers.not_available') }}
                                    @else
                                        {{ number_format((float) $review->rating, 1) }}
                                    @endif
                                </td>
                                <td>{{ $review->is_approved ? __('cms.customers.review_status_approved') : __('cms.customers.review_status_pending') }}</td>
                                <td>{{ optional($review->created_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}</td>
                                <td>{{ $review->review ? Str::limit($review->review, 120) : __('cms.customers.not_available') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('cms.customers.no_reviews') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
