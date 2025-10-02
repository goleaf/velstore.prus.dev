@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header :title="__('cms.brands.heading')">
        <x-admin.button-link href="{{ route('admin.brands.index') }}" class="btn-outline">
            {{ __('cms.sidebar.brands.list') }}
        </x-admin.button-link>
    </x-admin.page-header>

    @include('admin.brands.partials.form', [
        'formAction' => route('admin.brands.store'),
        'activeLanguages' => $activeLanguages,
        'submitLabel' => __('cms.brands.create'),
    ])
@endsection

@push('scripts')
    @include('admin.brands.partials.form-scripts')
@endpush
