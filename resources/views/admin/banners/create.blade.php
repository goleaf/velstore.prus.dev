@extends('admin.layouts.admin')

@section('title', __('cms.banners.create_banner'))

@section('content')
    @php
        $pageTitle = __('cms.banners.create_banner');
        $banner = null;
        $translations = collect();
    @endphp

    @include('admin.banners.partials.form', [
        'isEdit' => false,
        'formAction' => route('admin.banners.store'),
        'languages' => $languages,
        'banner' => $banner,
        'translations' => $translations,
        'pageTitle' => $pageTitle,
    ])
@endsection
