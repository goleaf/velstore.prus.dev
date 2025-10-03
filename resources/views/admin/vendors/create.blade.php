@extends('admin.layouts.admin')

@section('content')
<x-admin.page-header
    :title="__('cms.vendors.title_create')"
    :description="__('cms.vendors.create_description')"
>
    <x-admin.button-link href="{{ route('admin.vendors.index') }}" class="btn-outline">
        {{ __('cms.vendors.back_to_index') }}
    </x-admin.button-link>
</x-admin.page-header>

@include('admin.vendors.partials.form', [
    'action' => route('admin.vendors.store'),
    'statusOptions' => $statusOptions,
    'defaultStatus' => $defaultStatus ?? 'active',
    'cancelRoute' => route('admin.vendors.index'),
])
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-trim]').forEach((input) => {
                input.addEventListener('blur', () => {
                    if (typeof input.value === 'string') {
                        input.value = input.value.trim();
                    }
                });
            });
        });
    </script>
@endpush
