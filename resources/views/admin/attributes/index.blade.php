@extends('admin.layouts.admin')

@section('title', __('cms.attributes.title_manage'))

@section('content')
<x-admin.page-header :title="__('cms.attributes.title_manage')">
    <x-admin.button-link href="{{ route('admin.attributes.create') }}" class="btn-primary">
        {{ __('cms.attributes.title_create') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card class="mt-6">
    <x-admin.table id="attributes-table" :columns="[
        __('cms.attributes.id'),
        __('cms.attributes.name'),
        __('cms.attributes.values'),
        __('cms.attributes.action'),
    ]">
    </x-admin.table>
</x-admin.card>

<div class="modal fade" id="deleteAttributeModal" tabindex="-1" aria-labelledby="deleteAttributeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAttributeModalLabel">{{ __('cms.attributes.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">{{ __('cms.attributes.delete_confirmation') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.attributes.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAttribute">{{ __('cms.attributes.delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    $datatableLang = __('cms.datatables');
@endphp

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const table = $('#attributes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.attributes.data') }}",
                    type: 'POST',
                    data: function (data) {
                        data._token = "{{ csrf_token() }}";
                    },
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'values', name: 'values', orderable: false, searchable: false },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const editUrl = "{{ route('admin.attributes.edit', ':id') }}".replace(':id', row.id);

                            return `
                                <div class="flex items-center gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-edit-attribute" data-url="${editUrl}" aria-label="{{ __('cms.attributes.title_edit') }}">
                                        <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-attribute" data-id="${row.id}" aria-label="{{ __('cms.attributes.delete') }}">
                                        <i class="bi bi-trash-fill" aria-hidden="true"></i>
                                    </button>
                                </div>
                            `;
                        },
                    },
                ],
                pageLength: 10,
                language: @json($datatableLang),
            });

            let attributeToDeleteId = null;
            const deleteModalElement = document.getElementById('deleteAttributeModal');
            const deleteModal = deleteModalElement ? new bootstrap.Modal(deleteModalElement) : null;
            const confirmDeleteButton = document.getElementById('confirmDeleteAttribute');

            $(document).on('click', '.btn-edit-attribute', function () {
                const url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });

            $(document).on('click', '.btn-delete-attribute', function () {
                attributeToDeleteId = $(this).data('id') ?? null;
                if (deleteModal) {
                    deleteModal.show();
                }
            });

            if (confirmDeleteButton) {
                confirmDeleteButton.addEventListener('click', () => {
                    if (!attributeToDeleteId) {
                        return;
                    }

                    $.ajax({
                        url: '{{ route('admin.attributes.destroy', ':id') }}'.replace(':id', attributeToDeleteId),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function (response) {
                            if (response.success) {
                                table.ajax.reload(null, false);
                                toastr.success(response.message, "{{ __('cms.attributes.success') }}", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: 'toast-top-right',
                                    timeOut: 5000,
                                });
                                attributeToDeleteId = null;
                                if (deleteModal) {
                                    deleteModal.hide();
                                }
                            } else {
                                const message = response.message || 'Error deleting attribute! Please try again.';
                                toastr.error(message, "{{ __('cms.attributes.confirm_delete') }}", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: 'toast-top-right',
                                    timeOut: 5000,
                                });
                            }
                        },
                        error: function () {
                            toastr.error('Error deleting attribute! Please try again.', 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 5000,
                            });
                        },
                    });
                });
            }

            if (deleteModalElement) {
                deleteModalElement.addEventListener('hidden.bs.modal', () => {
                    attributeToDeleteId = null;
                });
            }
        });
    </script>
@endsection
