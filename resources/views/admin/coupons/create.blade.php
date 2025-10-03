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

    @include('admin.coupons.partials.form', [
        'formAction' => route('admin.coupons.store'),
        'formMethod' => 'POST',
        'submitLabel' => __('cms.coupons.save'),
    ])
@endsection
