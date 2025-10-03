@extends('admin.layouts.admin')

@section('content')
<x-admin.page-header
    :title="$vendor->name"
    :description="__('cms.vendors.profile_description')"
>
    <x-admin.button-link href="{{ route('admin.vendors.index') }}" class="btn-outline">
        {{ __('cms.vendors.back_to_index') }}
    </x-admin.button-link>
</x-admin.page-header>

<div class="grid gap-6 mt-6 xl:grid-cols-3">
    <x-admin.card class="xl:col-span-2">
        <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                    {{ __('cms.vendors.profile_overview_heading') }}
                </h3>
                <dl class="space-y-3 text-sm text-gray-700">
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('cms.vendors.vendor_name') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $vendor->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('cms.vendors.vendor_email') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $vendor->email }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('cms.vendors.phone_optional') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $vendor->phone ?: '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                    {{ __('cms.vendors.account_security_heading') }}
                </h3>
                <dl class="space-y-3 text-sm text-gray-700">
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('cms.vendors.status_label') }}</dt>
                        <dd class="mt-1">
                            @php
                                $statusBadge = match($vendor->status) {
                                    'active' => 'badge-success',
                                    'inactive' => 'badge-warning',
                                    'banned' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }}">
                                {{ __('cms.vendors.status_' . ($vendor->status ?? 'unknown')) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('cms.vendors.registered_at') }}</dt>
                        <dd class="mt-1 text-gray-900">
                            {{ $vendor->created_at ? $vendor->created_at->timezone(config('app.timezone'))->format('M j, Y H:i') : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('cms.vendors.shop_count_label') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ number_format($vendor->shops_count) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
            {{ __('cms.vendors.vendor_metrics_heading') }}
        </h3>
        <dl class="mt-4 space-y-4 text-sm text-gray-700">
            <div class="flex items-center justify-between">
                <dt>{{ __('cms.vendors.total_shops') }}</dt>
                <dd class="font-semibold text-gray-900">{{ number_format($vendor->shops_count) }}</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt>{{ __('cms.vendors.total_shop_products') }}</dt>
                <dd class="font-semibold text-gray-900">{{ number_format($totalProducts) }}</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt>{{ __('cms.vendors.active_shops') }}</dt>
                <dd class="font-semibold text-gray-900">{{ number_format($activeShopCount) }}</dd>
            </div>
        </dl>
    </x-admin.card>
</div>

<x-admin.card class="mt-6" :title="__('cms.vendors.shop_list_title')">
    @if ($vendor->shops->isEmpty())
        <p class="text-sm text-gray-600">{{ __('cms.vendors.shops_empty_state') }}</p>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($vendor->shops as $shop)
                <div class="p-4 border rounded-lg border-slate-200 bg-slate-50">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-base font-semibold text-slate-900">{{ $shop->name }}</h4>
                            <p class="text-xs text-slate-500">{{ $shop->slug }}</p>
                        </div>
                        <span class="badge {{ $shop->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                            {{ __('cms.vendors.shop_status_label_' . $shop->status) }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-slate-600 line-clamp-3">
                        {{ $shop->description ?: __('cms.vendors.shop_description_empty') }}
                    </p>
                    <dl class="mt-4 space-y-2 text-sm text-slate-600">
                        <div class="flex items-center justify-between">
                            <dt>{{ __('cms.vendors.shop_products_count') }}</dt>
                            <dd class="font-semibold text-slate-900">{{ number_format($shop->products_count) }}</dd>
                        </div>
                    </dl>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.card>
@endsection
