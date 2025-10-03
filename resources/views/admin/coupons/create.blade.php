@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header
        :title="__('cms.coupons.create_title')"
        :description="__('cms.coupons.form_description')"
    >
        <x-admin.button-link href="{{ route('admin.coupons.index') }}" class="btn-outline btn-sm">
            {{ __('cms.coupons.back_to_list') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <x-admin.card class="mt-6" :title="__('cms.coupons.form_title')">
        @include('admin.coupons.partials.form', [
            'action' => route('admin.coupons.store'),
            'method' => 'POST',
            'submitLabel' => __('cms.coupons.save'),
            'cancelUrl' => route('admin.coupons.index'),
        ])
    </x-admin.card>
@endsection
