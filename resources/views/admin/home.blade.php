@extends('admin.layouts.admin')

@section('css')
@endsection

@section('content')
    <div class="max-w-7xl mx-auto mt-4 px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="flex items-center p-4 rounded-xl border border-gray-300 hover:bg-gray-50">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mr-4 border border-gray-300">
                    <i class="fas fa-dollar-sign text-gray-600"></i>
                </div>
                <div class="flex-1">
                    <h6 class="m-0 text-base font-semibold text-gray-800">{{ __('cms.messages.dashboard') }} - {{ __('cms.payments.title') }}</h6>
                    <p class="m-0 text-sm text-gray-500">{{ __('cms.payments.payment_amount') }}: {{ number_format($kpi['sales_today'] ?? 0, 2) }}</p>
                </div>
            </div>

            <div class="flex items-center p-4 rounded-xl border border-gray-300 hover:bg-gray-50">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mr-4 border border-gray-300">
                    <i class="fas fa-shopping-cart text-gray-600"></i>
                </div>
                <div class="flex-1">
                    <h6 class="m-0 text-base font-semibold text-gray-800">{{ __('cms.orders.title') }}</h6>
                    <p class="m-0 text-sm text-gray-500">{{ __('cms.orders.details_title') }}: {{ $kpi['orders_completed'] ?? 0 }}/{{ $kpi['orders_total'] ?? 0 }}</p>
                </div>
            </div>

            <div class="flex items-center p-4 rounded-xl border border-gray-300 hover:bg-gray-50">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mr-4 border border-gray-300">
                    <i class="fas fa-store text-gray-600"></i>
                </div>
                <div class="flex-1">
                    <h6 class="m-0 text-base font-semibold text-gray-800">{{ __('cms.vendors.title_list') }}</h6>
                    <p class="m-0 text-sm text-gray-500">{{ __('cms.messages.view_details') }}: {{ $kpi['vendors_total'] ?? 0 }}</p>
                </div>
            </div>

            <div class="flex items-center p-4 rounded-xl border border-gray-300 hover:bg-gray-50">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mr-4 border border-gray-300">
                    <i class="fas fa-users text-gray-600"></i>
                </div>
                <div class="flex-1">
                    <h6 class="m-0 text-base font-semibold text-gray-800">{{ __('cms.customers.customer_list') }}</h6>
                    <p class="m-0 text-sm text-gray-500">{{ __('cms.messages.view_details') }}: {{ $kpi['customers_total'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
    @if (isset($revenueTrend))
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="p-4 rounded-xl border border-gray-300">
                <h6 class="m-0 mb-2 text-base font-semibold text-gray-800">{{ __('cms.messages.view_details') }}</h6>
                <ul class="list-disc pl-6 text-sm text-gray-600">
                    @foreach ($revenueTrend as $point)
                        <li>{{ $point['date'] }}: {{ number_format($point['amount'], 2) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @if (isset($orderStatusBreakdown))
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="p-4 rounded-xl border border-gray-300">
                <h6 class="m-0 mb-2 text-base font-semibold text-gray-800">{{ __('cms.orders.title') }}</h6>
                <ul class="list-disc pl-6 text-sm text-gray-600">
                    @foreach ($orderStatusBreakdown as $status => $count)
                        <li>{{ ucfirst($status) }}: {{ $count }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection
