@extends('admin.layouts.admin')

@section('content')
<x-admin.page-header
    :title="__('cms.categories.title_manage')"
    :description="__('cms.categories.index_description')"
>
    <x-admin.button-link href="{{ route('admin.categories.create') }}" class="btn-primary">
        {{ __('cms.categories.add_new') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card>
    <div class="grid gap-3 md:grid-cols-3">
        <div class="p-4 rounded-lg bg-primary-50 border border-primary-100">
            <p class="text-xs uppercase tracking-wide text-primary-600 mb-1">{{ __('cms.categories.total_categories') }}</p>
            <p class="text-xl font-semibold text-primary-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-success-50 border border-success-100">
            <p class="text-xs uppercase tracking-wide text-success-600 mb-1">{{ __('cms.categories.active_categories') }}</p>
            <p class="text-xl font-semibold text-success-900">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-warning-50 border border-warning-100">
            <p class="text-xs uppercase tracking-wide text-warning-600 mb-1">{{ __('cms.categories.inactive_categories') }}</p>
            <p class="text-xl font-semibold text-warning-900">{{ number_format($stats['inactive']) }}</p>
        </div>
    </div>
</x-admin.card>

<x-admin.card class="mt-6">
    @include('admin.categories.partials.filter-form', [
        'filters' => $filters,
        'parentOptions' => $parentOptions,
    ])
</x-admin.card>

<x-admin.card class="mt-6">
    @include('admin.categories.partials.table', [
        'categories' => $categories,
    ])
</x-admin.card>
@endsection
