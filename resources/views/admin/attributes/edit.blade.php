@extends('admin.layouts.admin')
@section('title', __('cms.attributes.title_edit'))

@section('content')
<x-admin.page-header :title="__('cms.attributes.title_edit')" :description="$attribute->name">
    <x-admin.button-link href="{{ route('admin.attributes.index') }}" class="btn-outline">
        {{ __('cms.attributes.cancel') }}
    </x-admin.button-link>
</x-admin.page-header>

@include('admin.attributes.partials.form', [
    'action' => route('admin.attributes.update', $attribute->id),
    'method' => 'PUT',
    'languages' => $languages,
    'attribute' => $attribute,
    'submitLabel' => __('cms.attributes.update_attribute'),
])
@endsection

@section('js')
    @include('admin.attributes.partials.form-scripts', ['languages' => $languages])
@endsection
