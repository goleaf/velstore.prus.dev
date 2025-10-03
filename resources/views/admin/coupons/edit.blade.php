@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header
        :title="__('cms.coupons.edit_title')"
        :description="__('cms.coupons.form_description')"
    >
        <x-admin.button-link href="{{ route('admin.coupons.index') }}" class="btn-outline btn-sm">
            {{ __('cms.coupons.back_to_list') }}
        </x-admin.button-link>
    </x-admin.page-header>

    @include('admin.coupons.partials.form', [
        'formAction' => route('admin.coupons.update', $coupon->id),
        'formMethod' => 'PUT',
        'submitLabel' => __('cms.coupons.update'),
        'coupon' => $coupon,
    ])
@endsection
