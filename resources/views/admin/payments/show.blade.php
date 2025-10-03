@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header
        :title="__('cms.payments.details_heading', ['id' => $payment->id])"
        :description="__('cms.payments.details_description')"
    >
        <x-admin.button-link href="{{ route('admin.payments.index') }}" class="btn-outline">
            {{ __('cms.payments.back') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <x-admin.card class="mt-6" :title="__('cms.payments.details_title')">
        <div class="grid gap-6">
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-sm font-medium text-gray-500">{{ __('cms.payments.status') }}</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-xl font-semibold text-gray-900">{{ $statusLabel }}</span>
                    <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                </div>
                <p class="mt-2 text-xs text-gray-500">{{ __('cms.payments.status_hint') }}</p>
            </div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.payments.id') }}</dt>
                    <dd class="mt-1 text-base font-semibold text-gray-900">#{{ $payment->id }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.payments.created_at') }}</dt>
                    <dd class="mt-1 text-base text-gray-900">
                        {{ optional($payment->created_at)->format('d M Y, h:i A') ?? __('cms.payments.not_available') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.payments.order') }}</dt>
                    <dd class="mt-1 text-base text-gray-900">
                        @if ($payment->order)
                            <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-primary-600 hover:text-primary-700">
                                #{{ $payment->order->id }}
                            </a>
                        @else
                            {{ __('cms.payments.not_available') }}
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.payments.user') }}</dt>
                    <dd class="mt-1 text-base text-gray-900">
                        {{ $payment->customer_display_name ?? $payment->order?->guest_email ?? __('cms.payments.not_available') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.payments.gateway') }}</dt>
                    <dd class="mt-1 text-base text-gray-900">
                        {{ $payment->gateway->name ?? __('cms.payments.not_available') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.payments.transaction_id') }}</dt>
                    <dd class="mt-1 text-base text-gray-900">
                        {{ $payment->transaction_id ?? __('cms.payments.not_available') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.payments.amount') }}</dt>
                    <dd class="mt-1 text-base font-semibold text-gray-900">
                        {{ number_format((float) $payment->amount, 2) }}
                        @if ($payment->currency)
                            <span class="text-sm font-normal text-gray-500">{{ $payment->currency }}</span>
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('cms.payments.shops') }}</dt>
                    <dd class="mt-1 text-base text-gray-900">
                        @if (!empty($payment->shop_names))
                            <div class="flex flex-wrap gap-2">
                                @foreach ($payment->shop_names as $shopName)
                                    <span class="badge badge-gray">{{ $shopName }}</span>
                                @endforeach
                            </div>
                        @else
                            {{ __('cms.payments.not_available') }}
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </x-admin.card>
@endsection
