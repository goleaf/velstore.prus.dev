@extends('admin.layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 mb-0">{{ __('cms.payment_gateways.create_title') }}</h1>
        <p class="text-muted mb-0">{{ __('cms.payment_gateways.create_description') }}</p>
    </div>
    <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('cms.payment_gateways.back_to_index') }}
    </a>
</div>

@include('admin.payment_gateways.partials.form', [
    'paymentGateway' => $paymentGateway,
    'action' => route('admin.payment-gateways.store'),
    'submitLabel' => __('cms.payment_gateways.create_button'),
])
@endsection

@push('scripts')
    @include('admin.payment_gateways.partials.scripts')
@endpush
