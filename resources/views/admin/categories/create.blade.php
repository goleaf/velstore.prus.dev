@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header
        :title="__('cms.categories.title_create')"
        :description="__('cms.categories.create_description')"
    >
        <x-admin.button-link href="{{ route('admin.categories.index') }}" class="btn-outline">
            {{ __('cms.categories.back_to_index') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <div class="mt-6">
        @include('admin.categories.partials.form', [
            'formAction' => route('admin.categories.store'),
            'formMethod' => 'POST',
            'parentOptions' => $parentOptions,
            'selectedParent' => $selectedParent ?? null,
            'category' => null,
            'activeLanguages' => $activeLanguages,
        ])
    </div>
@endsection
