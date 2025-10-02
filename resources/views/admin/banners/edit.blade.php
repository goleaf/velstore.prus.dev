@extends('admin.layouts.admin')

@section('title', __('cms.banners.edit_banner'))

@section('content')
    @php
        $pageTitle = __('cms.banners.edit_banner');
    @endphp

    @include('admin.banners.partials.form', [
        'isEdit' => true,
        'formAction' => route('admin.banners.update', $banner->id),
        'languages' => $languages,
        'banner' => $banner,
        'translations' => $translations,
        'pageTitle' => $pageTitle,
    ])
@endsection
