@extends('admin.layouts.admin')

@section('title', 'All Banners')

@section('content')
    <div class="container">

       <div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.banners.all_banners') }}</h6>
    </div>
    <div class="card-body">
                <table id="banners-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('cms.banners.id') }}</th>
                            <th>{{ __('cms.banners.banner_type') }}</th>
                            <th>{{ __('cms.banners.image') }}</th>
                            <th>{{ __('cms.banners.status') }}</th> 
                            <th>{{ __('cms.banners.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteBannerModal" tabindex="-1" aria-labelledby="deleteBannerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBannerModalLabel">{{ __('cms.banners.massage_confirm') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"> {{ __('cms.banners.confirm_delete') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.banners.massage_cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBanner">{{ __('cms.banners.massage_delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
@php
    $datatableLang = __('cms.datatables');
@endphp

    <script>
     
    $(document).ready(function() {
        $('#banners-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.banners.data') }}", 
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'type', name: 'type' },
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { 
                    data: 'status', 
                    name: 'status', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        var isChecked = data ? 'checked' : ''; 
                        return `<label class="switch">
                                    <input type="checkbox" class="toggle-status" data-id="${row.id}" ${isChecked}>
                                    <span class="slider round"></span>
                                </label>`;
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var editUrl = '{{ route('admin.banners.edit', ':id') }}'.replace(':id', row.id);
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-edit-banner" data-url="${editUrl}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-delete-banner" data-id="${row.id}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],

            pageLength: 10,
            language: @json($datatableLang) 
        });

        $(document).on('click', '.btn-edit-banner', function() {
            const url = $(this).data('url');
            if (url) {
                window.location.href = url;
            }
        });

        $(document).on('change', '.toggle-status', function() {
            var bannerId = $(this).data('id');
            var isActive = $(this).prop('checked') ? 1 : 0; 
            $.ajax({
                url: '{{ route('admin.banners.updateStatus') }}', 
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: bannerId,
                    status: isActive
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, "Updated", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                    } else {
                        toastr.error(response.message, "Failed", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                    }
                },
                error: function() {
                    alert('Error updating status!');
                    $(this).prop('checked', !isActive);
                }
            });
        });
    });

    let bannerToDeleteId = null;

    $(document).on('click', '.btn-delete-banner', function() {
        bannerToDeleteId = $(this).data('id');
        $('#deleteBannerModal').modal('show');
    });

    $('#confirmDeleteBanner').off('click').on('click', function() {
        if (bannerToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.banners.destroy', ':id') }}'.replace(':id', bannerToDeleteId),
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.success) {
                        $('#banners-table').DataTable().ajax.reload();
                        toastr.error(response.message, "Deleted", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                        $('#deleteBannerModal').modal('hide');
                    } else {
                        toastr.error(response.message, "Error", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                    }
                },
                error: function() {
                    toastr.error('Error deleting banner!', "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteBannerModal').modal('hide');
                }
            });
        }
    });

    </script>
@endsection
