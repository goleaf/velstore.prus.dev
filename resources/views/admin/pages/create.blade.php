@extends('admin.layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4">
    <div>
        <h1 class="h4 mb-1">{{ __('cms.pages.create') }}</h1>
        <p class="text-muted mb-0">{{ __('cms.pages.create_description') }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>{{ __('cms.pages.back_to_index') }}
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-3" role="alert">
        <strong>{{ __('cms.pages.validation_error_title') }}</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@include('admin.pages.partials.form', [
    'mode' => 'create',
    'action' => route('admin.pages.store'),
    'activeLanguages' => $activeLanguages,
    'page' => null,
])
@endsection

@section('js')
@include('admin.pages.partials.form-scripts', [
    'activeLanguages' => $activeLanguages,
    'page' => null,
])
@endsection
