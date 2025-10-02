@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header
        :title="__('cms.categories.title_edit')"
        :description="__('cms.categories.edit_description')"
    >
        <x-admin.button-link href="{{ route('admin.categories.index') }}" class="btn-outline">
            {{ __('cms.categories.back_to_index') }}
        </x-admin.button-link>
    </x-admin.page-header>

    @include('admin.categories.partials.form', [
        'formAction' => route('admin.categories.update', $category),
        'formMethod' => 'PUT',
        'parentOptions' => $parentOptions,
        'category' => $category,
        'activeLanguages' => $activeLanguages,
    ])
@endsection
