@extends('admin.layouts.admin')

@section('content')
    @include('admin.brands.partials.form', [
        'formAction' => route('admin.brands.store'),
        'activeLanguages' => $activeLanguages,
        'submitLabel' => __('cms.brands.create'),
    ])
@endsection

@push('scripts')
    @include('admin.brands.partials.form-scripts')
@endpush
