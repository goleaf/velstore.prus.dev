@extends('admin.layouts.admin')

@section('content')
    @include('admin.brands.partials.form', [
        'formAction' => route('admin.brands.update', $brand->id),
        'activeLanguages' => $activeLanguages,
        'brand' => $brand,
        'submitLabel' => __('cms.brands.update'),
    ])
@endsection

@push('scripts')
    @include('admin.brands.partials.form-scripts')
@endpush
