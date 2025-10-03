@extends('admin.layouts.admin')

@section('content')
    @php
        $statusLabel = \App\Models\Refund::labelForStatus($refund->status);
        $statusClass = \App\Models\Refund::badgeClassForStatus($refund->status);
        $payment = $refund->payment;
        $order = $payment?->order;
        $customer = $order?->customer;
        $shopNames = $order
            ? $order->details->map(fn ($detail) => $detail->product?->shop?->name)->filter()->unique()->values()
            : collect();
        $items = $order?->details ?? collect();
        $createdAt = optional($refund->created_at)->format('M d, Y h:i A');
        $updatedAt = optional($refund->updated_at)->format('M d, Y h:i A');
        $timeline = [
            ['label' => __('cms.refunds.timeline_created'), 'value' => $createdAt ?? __('cms.refunds.not_available')],
            ['label' => __('cms.refunds.timeline_updated'), 'value' => $updatedAt ?? __('cms.refunds.not_available')],
            ['label' => __('cms.refunds.timeline_status'), 'value' => $statusLabel],
        ];
    @endphp

    <div class="max-w-5xl mx-auto mt-4 space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium text-primary-600 uppercase tracking-wide">{{ __('cms.refunds.title') }}</p>
                <h1 class="text-3xl font-semibold text-gray-900">{{ __('cms.refunds.details_title') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('cms.refunds.manage') }}</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium ring-1 {{ $statusClass }}">
                <span class="h-2 w-2 rounded-full bg-current"></span>
                {{ $statusLabel }}
            </span>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl shadow-sm">
                <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('cms.refunds.details_title') }}</h2>
                    <button type="button" class="btn btn-outline btn-sm" data-url="{{ route('admin.refunds.index') }}">
                        {{ __('cms.refunds.back') }}
                    </button>
                </div>

                <div class="px-6 py-6 space-y-6">
                    <dl class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.id') }}</dt>
                            <dd class="mt-1 text-base font-semibold text-gray-900">#{{ $refund->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.reference') }}</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $refund->refund_id ?? __('cms.refunds.not_available') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.amount') }}</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                {{ number_format((float) $refund->amount, 2) }} {{ strtoupper($refund->currency ?? '') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.status') }}</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $statusClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $statusLabel }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.created_at') }}</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $createdAt ?? __('cms.refunds.not_available') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.updated_at') }}</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $updatedAt ?? __('cms.refunds.not_available') }}</dd>
                        </div>
                    </dl>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('cms.refunds.payment_summary_title') }}</h3>
                            <dl class="mt-3 space-y-2">
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('cms.refunds.payment') }}</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if ($payment)
                                            {{ __('cms.refunds.payment') }} #{{ $payment->id }} · {{ number_format((float) $payment->amount, 2) }} {{ strtoupper($payment->currency ?? '') }}
                                        @else
                                            {{ __('cms.refunds.not_available') }}
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('cms.refunds.gateway_summary_title') }}</dt>
                                    <dd class="text-sm text-gray-900">{{ $payment?->gateway?->name ?? __('cms.refunds.not_available') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('cms.refunds.order_number') }}</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if ($order)
                                            #{{ $order->id }}
                                        @else
                                            {{ __('cms.refunds.not_available') }}
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('cms.refunds.shop_column') }}</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if ($shopNames->isNotEmpty())
                                            {{ $shopNames->join(', ') }}
                                        @else
                                            {{ __('cms.refunds.not_available') }}
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            @if ($order)
                                <a href="{{ route('admin.orders.show', $order) }}" class="mt-4 inline-flex items-center text-sm font-semibold text-primary-600 hover:text-primary-700">
                                    {{ __('cms.refunds.view_order') }}
                                </a>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('cms.refunds.customer_summary_title') }}</h3>
                            <dl class="mt-3 space-y-2">
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('cms.refunds.customer_column') }}</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if ($customer)
                                            {{ $customer->name }} • {{ $customer->email }}
                                        @elseif ($order?->guest_email)
                                            {{ $order->guest_email }}
                                        @else
                                            {{ __('cms.refunds.not_available') }}
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('cms.refunds.payment_status_label') }}</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $payment?->status ? ucfirst($payment->status) : __('cms.refunds.not_available') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('cms.refunds.reason') }}</h3>
                        <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">
                            {{ $refund->reason ? $refund->reason : __('cms.refunds.not_available') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('cms.refunds.timeline_title') }}</h2>
                </div>
                <div class="px-6 py-6">
                    <ol class="space-y-4">
                        @foreach ($timeline as $event)
                            <li class="flex items-start gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-primary-500"></span>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ $event['label'] }}</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $event['value'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>

        @if ($items->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
                <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('cms.refunds.items_summary_title') }}</h2>
                    @if ($order)
                        <span class="text-sm text-gray-500">{{ __('cms.refunds.order_number') }} #{{ $order->id }}</span>
                    @endif
                </div>
                <div class="px-6 py-6 space-y-4">
                    @foreach ($items as $item)
                        <div class="flex flex-col gap-1 border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $item->product?->translation?->name ?? $item->product?->slug ?? __('cms.refunds.not_available') }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ __('cms.refunds.item_line', [
                                    'quantity' => $item->quantity,
                                    'price' => number_format((float) $item->price, 2),
                                ]) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
