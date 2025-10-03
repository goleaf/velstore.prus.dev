@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('cms.coupons.edit_title') }}</h6>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-light btn-sm">
                {{ __('cms.coupons.back_to_list') }}
            </a>
        </div>
        <div class="card-body">
            @include('admin.coupons.partials.form', [
                'action' => route('admin.coupons.update', $coupon->id),
                'method' => 'PUT',
                'submitLabel' => __('cms.coupons.update'),
                'coupon' => $coupon,
            ])
        </div>
    </div>
@endsection
