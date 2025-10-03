@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header :title="__('cms.coupons.create_title')">
        <x-admin.button-link href="{{ route('admin.coupons.index') }}" class="btn-outline">
            {{ __('cms.coupons.back_to_list') }}
        </x-admin.button-link>
    </x-admin.page-header>

    @include('admin.coupons.partials.form', [
        'formAction' => route('admin.coupons.store'),
        'formMethod' => 'POST',
        'submitLabel' => __('cms.coupons.save'),
    ])
@endsection
