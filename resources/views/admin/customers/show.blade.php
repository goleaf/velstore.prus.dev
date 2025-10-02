@extends('admin.layouts.admin')

@php use Illuminate\Support\Str; @endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h6 class="mb-0">{{ __('cms.customers.details_title') }} <span class="text-primary">#{{ $customer->id }}</span></h6>
        <button type="button" class="btn btn-light btn-sm"
                data-url="{{ route('admin.customers.index') }}">{{ __('cms.customers.back_to_list') }}</button>
    </div>

    @php
        $ordersCount = $customer->orders->count();
        $totalSpent = $customer->orders->sum('total_amount');
        $wishlistCount = $customer->wishlists->count();
        $reviewsCount = $customer->reviews->count();
        $statusLabel = $customer->status === 'active' ? __('cms.customers.active') : __('cms.customers.inactive');
        $statusClass = $customer->status === 'active' ? 'badge bg-success' : 'badge bg-danger';
        $customerAddresses = $customer->addresses->sortByDesc('is_default');
        $defaultAddress = $customer->defaultAddress;
        $primaryAddressLine = $customer->primary_address_line;
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
                    @if ($primaryAddressLine)
                        <p class="fw-semibold mb-0">{{ $primaryAddressLine }}</p>
                        @if ($defaultAddress)
                            <p class="text-muted small mb-0">
                                {{ $defaultAddress->name }}
                                @if ($defaultAddress->phone)
                                    <span class="mx-1">&middot;</span>{{ $defaultAddress->phone }}
                                @endif
                            </p>
                        @endif
                    @else
                        <p class="fw-semibold mb-0">{{ __('cms.customers.not_available') }}</p>
                    @endif
                </div>
                <div class="col-md-3">
                    <p class="text-muted small mb-1">{{ __('cms.customers.registered_at') }}</p>
                    <p class="fw-semibold mb-0">
                        {{ optional($customer->created_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}</p>
                </div>
                <div class="col-md-3">
                    <p class="text-muted small mb-1">{{ __('cms.customers.last_updated') }}</p>
                    <p class="fw-semibold mb-0">
                        {{ optional($customer->updated_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('cms.customers.addresses') }}</h6>
        </div>
        <div class="card-body">
            @if ($customerAddresses->isEmpty())
                <p class="text-muted mb-3">{{ __('cms.customers.no_addresses') }}</p>
            @else
                <div class="row g-3">
                    @foreach ($customerAddresses as $addr)
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="fw-semibold mb-1">{{ $addr->name }}</p>
                                        <p class="mb-1 text-muted">{{ $addr->phone ?? __('cms.customers.not_available') }}</p>
                                        <p class="mb-1">{{ $addr->address ?? __('cms.customers.not_available') }}</p>
                                        @php
                                            $location = collect([$addr->city, $addr->postal_code, $addr->country])->filter()->implode(', ');
                                        @endphp
                                        <p class="mb-0 text-muted">{{ $location !== '' ? $location : __('cms.customers.not_available') }}</p>
                                    </div>
                                    @if ($addr->is_default)
                                        <span class="badge bg-primary">{{ __('cms.customers.default') }}</span>
                                    @endif
                                </div>
                                <div class="mt-2 d-flex gap-2">
                                    <form method="POST"
                                          action="{{ route('admin.customers.addresses.default', [$customer, $addr]) }}">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-primary">{{ __('cms.customers.set_default') }}</button>
                                    </form>
                                    <form method="POST"
                                          action="{{ route('admin.customers.addresses.destroy', [$customer, $addr]) }}"
                                          onsubmit="return confirm('{{ __('cms.customers.delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger">{{ __('cms.customers.delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <hr>
            <form method="POST" action="{{ route('admin.customers.addresses.store', $customer) }}" class="row g-2">
                @csrf
                <div class="col-md-4"><input class="form-control" name="name"
                           placeholder="{{ __('cms.customers.address_name') }}" required></div>
                <div class="col-md-4"><input class="form-control" name="phone"
                           placeholder="{{ __('cms.customers.phone') }}" required></div>
                <div class="col-md-4"><input class="form-control" name="postal_code"
                           placeholder="{{ __('cms.customers.postal_code') }}" required></div>
                <div class="col-md-6"><input class="form-control" name="address"
                           placeholder="{{ __('cms.customers.address') }}" required></div>
                <div class="col-md-3"><input class="form-control" name="city"
                           placeholder="{{ __('cms.customers.city') }}" required></div>
                <div class="col-md-3"><input class="form-control" name="country"
                           placeholder="{{ __('cms.customers.country') }}" required></div>
                <div class="col-md-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1"
                               id="is_default_admin">
                        <label class="form-check-label"
                               for="is_default_admin">{{ __('cms.customers.set_as_default') }}</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">{{ __('cms.customers.add_address') }}</button>
                </div>
            </form>
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
                                $paymentStatus = $latestPayment?->status
                                    ? ucfirst($latestPayment->status)
                                    : __('cms.customers.not_available');
                            @endphp
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ ucfirst($order->status) }}</td>
                                <td>{{ number_format((float) $order->total_amount, 2) }}</td>
                                <td>{{ $itemsCount }}</td>
                                <td>{{ optional($order->created_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}
                                </td>
                                <td>{{ $paymentStatus }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                            data-url="{{ route('admin.orders.show', $order) }}">
                                        {{ __('cms.customers.view_order') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    {{ __('cms.customers.no_orders') }}</td>
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
                                <td>{{ $wishlist->product?->translation?->name ?? __('cms.customers.product_missing') }}
                                </td>
                                <td>{{ optional($wishlist->created_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">
                                    {{ __('cms.customers.no_wishlists') }}</td>
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
                                <td>{{ $review->is_approved ? __('cms.customers.review_status_approved') : __('cms.customers.review_status_pending') }}
                                </td>
                                <td>{{ optional($review->created_at)->format('Y-m-d H:i') ?? __('cms.customers.not_available') }}
                                </td>
                                <td>{{ $review->review ? Str::limit($review->review, 120) : __('cms.customers.not_available') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    {{ __('cms.customers.no_reviews') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
