@extends('admin.layouts.admin')

@section('content')
    @php
        $status = strtolower($refund->status ?? 'pending');
        $statusStyles = [
            'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-500/20',
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-500/20',
            'failed' => 'bg-rose-50 text-rose-700 ring-rose-500/20',
        ];

        $statusClass = $statusStyles[$status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/10';
        $payment = $refund->payment;
        $paymentStatus = $payment?->status ?? __('cms.refunds.not_available');
        $paymentAmount = $payment?->amount;
        $paymentGateway = $payment?->gateway?->name;
        $createdAt = optional($refund->created_at)->format('M d, Y h:i A');
        $updatedAt = optional($refund->updated_at)->format('M d, Y h:i A');
    @endphp

    <div class="max-w-4xl mx-auto mt-4 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-primary-600 uppercase tracking-wide">{{ __('cms.refunds.title') }}</p>
                <h1 class="text-3xl font-semibold text-gray-900">{{ __('cms.refunds.details_title') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('cms.refunds.manage') }}</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium ring-1 {{ $statusClass }}">
                <span class="h-2 w-2 rounded-full bg-current"></span>
                {{ ucfirst($refund->status) }}
            </span>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('cms.refunds.details_title') }}</h2>
            </div>

            <div class="px-6 py-6">
                <dl class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.id') }}</dt>
                        <dd class="mt-1 text-base font-semibold text-gray-900">#{{ $refund->id }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.amount') }}</dt>
                        <dd class="mt-1 text-base text-gray-900">{{ number_format((float) $refund->amount, 2) }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.payment') }}</dt>
                        <dd class="mt-1 text-base text-gray-900">
                            @if ($payment)
                                <div class="flex flex-col gap-1">
                                    <span class="font-semibold text-gray-900">{{ __('cms.refunds.payment') }} #{{ $payment->id }}</span>
                                    <span class="text-sm text-gray-600">
                                        {{ __('cms.payments.amount') }}: {{ number_format((float) $paymentAmount, 2) }}
                                    </span>
                                    <span class="text-sm text-gray-600">
                                        {{ __('cms.payments.status') }}: {{ ucfirst($paymentStatus) }}
                                    </span>
                                    @if ($paymentGateway)
                                        <span class="text-sm text-gray-600">
                                            {{ __('cms.payments.gateway') }}: {{ $paymentGateway }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-500">{{ __('cms.refunds.not_available') }}</span>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.reason') }}</dt>
                        <dd class="mt-1 text-base text-gray-900 whitespace-pre-wrap">
                            {{ $refund->reason ? $refund->reason : __('cms.refunds.not_available') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.created_at') }}</dt>
                        <dd class="mt-1 text-base text-gray-900">
                            {{ $createdAt ?? __('cms.refunds.not_available') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('cms.refunds.updated_at') }}</dt>
                        <dd class="mt-1 text-base text-gray-900">
                            {{ $updatedAt ?? __('cms.refunds.not_available') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-500">{{ __('cms.refunds.manage') }}</p>
                <div class="flex items-center gap-3">
                    <button type="button" class="btn btn-outline" data-url="{{ route('admin.refunds.index') }}">
                        {{ __('cms.refunds.back') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
