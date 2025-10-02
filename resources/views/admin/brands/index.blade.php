@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.brands.heading') }}</h6>
        </div>

        <div class="card-body">
            <table id="brands-table" class="table table-bordered mt-4 dt-style">
                <thead>
                    <tr>
                        <th>{{ __('cms.brands.id') }}</th>
                        <th>{{ __('cms.brands.name') }}</th>
                        <th>{{ __('cms.brands.logo') }}</th>
                        <th>{{ __('cms.brands.status') }}</th>
                        <th>{{ __('cms.brands.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBrandModalLabel">{{ __('cms.brands.massage_confirm') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ __('cms.brands.confirm_delete') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.brands.massage_cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBrand">{{ __('cms.brands.massage_delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $labels = [
        'logo' => __('cms.brands.logo'),
        'noLogo' => __('cms.brands.no_logo'),
    ];

    $labelFallbacks = [
        'logo' => 'Logo',
        'noLogo' => 'No logo',
    ];

    foreach ($labels as $key => $value) {
        $expected = 'cms.brands.' . ($key === 'logo' ? 'logo' : 'no_logo');
        if ($value === $expected) {
            $labels[$key] = $labelFallbacks[$key];
        }
    }

    $messages = [
        'statusUpdated' => __('cms.common.updated_successfully'),
        'statusFailed' => __('cms.common.update_failed'),
        'deleteSuccess' => __('cms.common.deleted_successfully'),
        'deleteFailed' => __('cms.common.delete_failed'),
    ];

    $messageFallbacks = [
        'statusUpdated' => 'Brand status updated successfully.',
        'statusFailed' => 'Unable to update brand status.',
        'deleteSuccess' => 'Brand deleted successfully.',
        'deleteFailed' => 'Unable to delete brand.',
    ];

    $messageSuffixes = [
        'statusUpdated' => 'updated_successfully',
        'statusFailed' => 'update_failed',
        'deleteSuccess' => 'deleted_successfully',
        'deleteFailed' => 'delete_failed',
    ];

    foreach ($messages as $key => $value) {
        if ($value === 'cms.common.' . $messageSuffixes[$key]) {
            $messages[$key] = $messageFallbacks[$key];
        }
    }
@endphp

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = @json(csrf_token());
            const datatableLang = @json(__('cms.datatables'));
            const labels = @json($labels);
            const messages = {
                ...@json($messages),
                statusError: 'Error updating status!',
                deleteError: 'Error deleting brand!',
            };
            const deleteBrandModalElement = document.getElementById('deleteBrandModal');
            const deleteBrandModal = deleteBrandModalElement ? new bootstrap.Modal(deleteBrandModalElement) : null;
            let brandToDeleteId = null;

            const brandsTable = $('#brands-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.brands.getData') }}",
                    type: 'GET',
                    data: function (request) {
                        request._token = csrfToken;
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name', orderable: false },
                    {
                        data: 'logo_url',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            if (!data) {
                                return `<span class="text-muted">${labels.noLogo}</span>`;
                            }

                            const normalizedPath = data.replace(/^\/+/, '');
                            const logoPath = data.startsWith('http') ? data : `/storage/${normalizedPath}`;
                            return `<img src="${logoPath}" alt="${labels.logo}" width="50" class="img-fluid">`;
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data, type, row) {
                            const normalizedStatus = typeof data === 'string' ? data.toLowerCase() : '';
                            const isActive = normalizedStatus === 'active';
                            return `
                                <label class="switch mb-0">
                                    <input
                                        type="checkbox"
                                        class="toggle-status"
                                        data-id="${row.id}"
                                        data-status="${normalizedStatus || 'inactive'}"
                                        ${isActive ? 'checked' : ''}
                                    >
                                    <span class="slider round"></span>
                                </label>
                            `;
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        render: function (_data, _type, row) {
                            const editUrl = `{{ route('admin.brands.edit', ':id') }}`.replace(':id', row.id);
                            return `
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-edit-brand" data-url="${editUrl}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-delete-brand" data-id="${row.id}">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                pageLength: 10,
                language: datatableLang
            });

            $(document).on('click', '.btn-edit-brand', function () {
                const url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });

            $(document).on('click', '.btn-delete-brand', function () {
                brandToDeleteId = $(this).data('id');
                if (deleteBrandModal) {
                    deleteBrandModal.show();
                }
            });

            $(document).on('change', '.toggle-status', function () {
                const $toggle = $(this);
                const brandId = $toggle.data('id');
                const previousStatus = ($toggle.data('status') || 'inactive').toString();
                const newStatus = $toggle.prop('checked') ? 'active' : 'inactive';

                $.ajax({
                    url: '{{ route('admin.brands.updateStatus') }}',
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        id: brandId,
                        status: newStatus,
                    },
                    success: function (response) {
                        if (response.success) {
                            const updatedStatus = (response.status || newStatus).toString().toLowerCase();
                            $toggle.data('status', updatedStatus);
                            $toggle.prop('checked', updatedStatus === 'active');
                            toastr.success(response.message || messages.statusUpdated, 'Updated', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 5000,
                            });
                            return;
                        }

                        $toggle.prop('checked', previousStatus === 'active');
                        $toggle.data('status', previousStatus);
                        toastr.error(response.message || messages.statusFailed, 'Failed', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-top-right',
                            timeOut: 5000,
                        });
                    },
                    error: function () {
                        $toggle.prop('checked', previousStatus === 'active');
                        $toggle.data('status', previousStatus);
                        toastr.error(messages.statusError, 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-top-right',
                            timeOut: 5000,
                        });
                    },
                });
            });

            const confirmDeleteButton = document.getElementById('confirmDeleteBrand');
            if (confirmDeleteButton) {
                confirmDeleteButton.addEventListener('click', () => {
                    if (!brandToDeleteId) {
                        return;
                    }

                    $.ajax({
                        url: `{{ route('admin.brands.destroy', ':id') }}`.replace(':id', brandToDeleteId),
                        method: 'DELETE',
                        data: {
                            _token: csrfToken,
                        },
                        success: function (response) {
                            if (response.success) {
                                brandsTable.ajax.reload(null, false);
                                toastr.success(response.message || messages.deleteSuccess, 'Deleted', {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: 'toast-top-right',
                                    timeOut: 5000,
                                });
                                if (deleteBrandModal) {
                                    deleteBrandModal.hide();
                                }
                                brandToDeleteId = null;
                                return;
                            }

                            toastr.error(response.message || messages.deleteFailed, 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 5000,
                            });
                        },
                        error: function () {
                            toastr.error(messages.deleteError, 'Error', {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 5000,
                            });
                        },
                    });
                });
            }
        });
    </script>
@endpush
