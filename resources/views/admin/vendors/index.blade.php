@extends('admin.layouts.admin')

@php
    $datatableLang = __('cms.datatables');
    $vendorStats = $stats ?? [
        'total' => 0,
        'active' => 0,
        'inactive' => 0,
        'banned' => 0,
    ];
@endphp

@section('content')
<x-admin.page-header
    :title="__('cms.vendors.title_manage')"
    :description="__('cms.vendors.index_description')"
>
    <x-admin.button-link href="{{ route('admin.vendors.create') }}" class="btn-primary">
        {{ __('cms.vendors.add_vendor') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card>
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-600 mb-1">{{ __('cms.vendors.total_vendors') }}</p>
            <p class="text-xl font-semibold text-slate-900">{{ number_format($vendorStats['total']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-100">
            <p class="text-xs uppercase tracking-wide text-emerald-600 mb-1">{{ __('cms.vendors.active_vendors') }}</p>
            <p class="text-xl font-semibold text-emerald-900">{{ number_format($vendorStats['active']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-amber-50 border border-amber-100">
            <p class="text-xs uppercase tracking-wide text-amber-600 mb-1">{{ __('cms.vendors.inactive_vendors') }}</p>
            <p class="text-xl font-semibold text-amber-900">{{ number_format($vendorStats['inactive']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-rose-50 border border-rose-100">
            <p class="text-xs uppercase tracking-wide text-rose-600 mb-1">{{ __('cms.vendors.banned_vendors') }}</p>
            <p class="text-xl font-semibold text-rose-900">{{ number_format($vendorStats['banned']) }}</p>
        </div>
    </div>
</x-admin.card>

<x-admin.card class="mt-6" :title="__('cms.vendors.table_title')">
    <x-admin.table id="vendors-table" :columns="[
        __('cms.vendors.id'),
        __('cms.vendors.name'),
        __('cms.vendors.email'),
        __('cms.vendors.phone'),
        __('cms.vendors.status'),
        __('cms.vendors.actions'),
    ]">
    </x-admin.table>
</x-admin.card>

<div class="modal fade" id="deleteVendorModal" tabindex="-1" aria-labelledby="deleteVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteVendorModalLabel">{{ __('cms.vendors.modal_confirm_delete_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0 text-gray-600">{{ __('cms.vendors.modal_confirm_delete_body') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('cms.vendors.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteVendor">{{ __('cms.vendors.confirm_delete_button') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const routes = {
                data: '{{ route('admin.vendors.data') }}',
                destroy: '{{ route('admin.vendors.destroy', ['id' => '__ID__']) }}',
            };

            const table = $('#vendors-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: routes.data,
                    type: 'GET',
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone', defaultContent: '—' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false },
                ],
                pageLength: 10,
                order: [[0, 'desc']],
                language: @json($datatableLang),
            });

            const deleteModalElement = document.getElementById('deleteVendorModal');
            const deleteModal = deleteModalElement ? new bootstrap.Modal(deleteModalElement) : null;
            let vendorToDelete = null;

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-action="delete-vendor"]');
                if (! trigger) {
                    return;
                }

                vendorToDelete = trigger.getAttribute('data-vendor-id');
                if (vendorToDelete && deleteModal) {
                    deleteModal.show();
                }
            });

            const confirmDeleteButton = document.getElementById('confirmDeleteVendor');
            if (confirmDeleteButton) {
                confirmDeleteButton.addEventListener('click', () => {
                    if (! vendorToDelete) {
                        return;
                    }

                    $.ajax({
                        url: routes.destroy.replace('__ID__', vendorToDelete),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            if (response && response.success) {
                                table.ajax.reload(null, false);
                                toastr.success(response.message || '{{ __('cms.vendors.success_delete') }}');
                            } else {
                                toastr.error((response && response.message) || '{{ __('cms.vendors.error_delete') }}');
                            }

                            vendorToDelete = null;
                            deleteModal?.hide();
                        },
                        error: function() {
                            toastr.error('{{ __('cms.vendors.error_delete') }}');
                            vendorToDelete = null;
                            deleteModal?.hide();
                        }
                    });
                });
            }

            deleteModalElement?.addEventListener('hidden.bs.modal', () => {
                vendorToDelete = null;
            });
        });
    </script>
@endpush
