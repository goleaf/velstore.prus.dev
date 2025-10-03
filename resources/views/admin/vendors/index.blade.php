@extends('admin.layouts.admin')

@php
    $datatableLang = __('cms.datatables');
    $vendorStats = $stats['vendors'] ?? [
        'total' => 0,
        'active' => 0,
        'inactive' => 0,
        'banned' => 0,
    ];
    $shopStats = $stats['shops'] ?? [
        'total' => 0,
        'active' => 0,
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
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
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
        <div class="p-4 rounded-lg bg-indigo-50 border border-indigo-100">
            <p class="text-xs uppercase tracking-wide text-indigo-600 mb-1">{{ __('cms.vendors.total_shops') }}</p>
            <p class="text-xl font-semibold text-indigo-900">{{ number_format($shopStats['total']) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-cyan-50 border border-cyan-100">
            <p class="text-xs uppercase tracking-wide text-cyan-600 mb-1">{{ __('cms.vendors.active_shops') }}</p>
            <p class="text-xl font-semibold text-cyan-900">{{ number_format($shopStats['active']) }}</p>
        </div>
    </div>
</x-admin.card>

<x-admin.card class="mt-6" :title="__('cms.vendors.table_title')">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
        <div class="max-w-xs w-full">
            <label for="statusFilter" class="form-label text-xs uppercase tracking-wide text-slate-600">
                {{ __('cms.vendors.filter_status_label') }}
            </label>
            <select id="statusFilter" class="form-select">
                <option value="">{{ __('cms.vendors.filter_status_all') }}</option>
                @foreach ($statusOptions ?? [] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <x-admin.table id="vendors-table" :columns="[
        __('cms.vendors.id'),
        __('cms.vendors.name'),
        __('cms.vendors.email'),
        __('cms.vendors.phone'),
        __('cms.vendors.registered_at'),
        __('cms.vendors.shops'),
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
                show: '{{ route('admin.vendors.show', ['vendor' => '__ID__']) }}',
                destroy: '{{ route('admin.vendors.destroy', ['id' => '__ID__']) }}',
            };

            const statusFilter = document.getElementById('statusFilter');
            const datatableLanguage = {
                ...@json($datatableLang),
                emptyTable: '{{ __('cms.vendors.empty_state') }}',
            };

            const table = $('#vendors-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: routes.data,
                    type: 'GET',
                    data: function (d) {
                        d.status = statusFilter?.value ?? '';
                    },
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone', defaultContent: '—' },
                    { data: 'registered_at', name: 'registered_at', orderable: false, searchable: false, defaultContent: '—' },
                    { data: 'shops', name: 'shops', orderable: false, searchable: false, defaultContent: '—' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false },
                ],
                pageLength: 10,
                order: [[0, 'desc']],
                language: datatableLanguage,
            });

            statusFilter?.addEventListener('change', () => {
                table.ajax.reload();
            });

            const deleteModalElement = document.getElementById('deleteVendorModal');
            const deleteModal = deleteModalElement ? new bootstrap.Modal(deleteModalElement) : null;
            let vendorToDelete = null;

            document.addEventListener('click', (event) => {
                const deleteTrigger = event.target.closest('[data-action="delete-vendor"]');
                if (deleteTrigger) {
                    vendorToDelete = deleteTrigger.getAttribute('data-vendor-id');
                    if (vendorToDelete && deleteModal) {
                        deleteModal.show();
                    }

                    return;
                }

                const viewTrigger = event.target.closest('[data-action="view-vendor"]');
                if (viewTrigger) {
                    const vendorId = viewTrigger.getAttribute('data-vendor-id');
                    if (vendorId) {
                        window.location.href = routes.show.replace('__ID__', vendorId);
                    }
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
