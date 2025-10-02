@extends('admin.layouts.admin')

@php
    $customer = $review->customer;
    $product = $review->product;
    $customerNameParts = array_filter([$customer?->first_name, $customer?->last_name]);
    $customerName = trim(implode(' ', $customerNameParts));

    if ($customerName === '') {
        $customerName = $customer?->email ?? __('cms.product_reviews.missing_customer');
    }

    $productName = $product?->title ?? __('cms.product_reviews.missing_product');
    $statusKey = $review->is_approved ? 'approved' : 'pending';
    $statusClass = $review->is_approved ? 'badge badge-success' : 'badge badge-warning';
@endphp

@section('content')
    <x-admin.page-header
        :title="__('cms.product_reviews.show_title', ['id' => $review->id])"
    >
        <x-admin.button-link href="{{ route('admin.reviews.index') }}" class="btn-outline">
            {{ __('cms.product_reviews.back_to_list') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <x-admin.card :title="__('cms.product_reviews.details_title')">
        <dl class="grid gap-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.customer_name') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $customerName }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.product_name') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $productName }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.rating') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ number_format($review->rating, 1) }}
                    <span class="text-gray-500">/ 5</span>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.status') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <span class="{{ $statusClass }}">{{ __('cms.product_reviews.' . $statusKey) }}</span>
                </dd>
            </div>
        </dl>

        <div class="mt-8">
            <h3 class="text-sm font-medium text-gray-500">{{ __('cms.product_reviews.review') }}</h3>
            <p class="mt-2 whitespace-pre-line text-sm text-gray-900">
                {{ $review->review ?: __('cms.product_reviews.no_review_provided') }}
            </p>
        </div>
    </x-admin.card>
@endsection
