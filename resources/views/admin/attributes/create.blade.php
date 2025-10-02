@extends('admin.layouts.admin')
@section('title', __('cms.attributes.title_create'))

@section('content')
<x-admin.page-header :title="__('cms.attributes.title_create')">
    <x-admin.button-link href="{{ route('admin.attributes.index') }}" class="btn-outline">
        {{ __('cms.attributes.cancel') }}
    </x-admin.button-link>
</x-admin.page-header>

@include('admin.attributes.partials.form', [
    'action' => route('admin.attributes.store'),
    'method' => 'POST',
    'languages' => $languages,
    'submitLabel' => __('cms.attributes.save_attribute'),
])
@endsection

@section('js')
    @include('admin.attributes.partials.form-scripts', ['languages' => $languages])
@endsection
