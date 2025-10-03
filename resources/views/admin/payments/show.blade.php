@extends('admin.layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h6 class="mb-0">
            {{ __('cms.payments.details_title') }}
            <span class="text-primary">#{{ $payment->id }}</span>
        </h6>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> {{ __('cms.payments.back') }}
        </a>
    </div>

    @php
        $statusVariants = [
            'completed' => 'success',
            'pending' => 'warning text-dark',
            'failed' => 'danger',
            'refunded' => 'info text-dark',
        ];
        $statusVariant = $statusVariants[$payment->status] ?? 'secondary';

        $statusTranslationKey = 'cms.payments.' . $payment->status;
        $statusLabel = __($statusTranslationKey);
        if ($statusLabel === $statusTranslationKey) {
            $statusLabel = ucfirst($payment->status);
        }

        $notAvailable = __('cms.payments.not_available');
    @endphp

    <div class="card mt-3">
        <div class="card-header card-header-bg text-white">
            <h6 class="mb-0">{{ __('cms.payments.details_title') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <tbody>
                        <tr>
                            <th class="w-25">{{ __('cms.payments.id') }}</th>
                            <td>#{{ $payment->id }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('cms.payments.order') }}</th>
                            <td>
                                @if ($payment->order)
                                    <a href="{{ route('admin.orders.show', $payment->order->id) }}" class="text-decoration-none">
                                        #{{ $payment->order->id }}
                                    </a>
                                @else
                                    {{ $notAvailable }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('cms.payments.user') }}</th>
                            <td>{{ $payment->customer_display_name ?? $payment->order?->guest_email ?? $notAvailable }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('cms.payments.gateway') }}</th>
                            <td>{{ $payment->gateway->name ?? $notAvailable }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('cms.payments.shops') }}</th>
                            <td>
                                @if (!empty($payment->shop_names))
                                    <ul class="mb-0 ps-3">
                                        @foreach ($payment->shop_names as $shopName)
                                            <li>{{ $shopName }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{ $notAvailable }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('cms.payments.amount') }}</th>
                            <td>
                                {{ number_format((float) $payment->amount, 2) }}
                                @if ($payment->currency)
                                    <span class="text-muted">{{ $payment->currency }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('cms.payments.status') }}</th>
                            <td>
                                <span class="badge bg-{{ $statusVariant }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('cms.payments.transaction_id') }}</th>
                            <td>{{ $payment->transaction_id ?? $notAvailable }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('cms.payments.created_at') }}</th>
                            <td>{{ optional($payment->created_at)->format('d M Y, h:i A') ?? $notAvailable }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
