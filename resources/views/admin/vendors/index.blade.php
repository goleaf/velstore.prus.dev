@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.vendors.title_list') }}</h6>
        </div>
        <div class="card-body">
            <table id="vendors-table" class="table table-bordered mt-4 dt-style">
                <thead>
                    <tr>
                        <th>{{ __('cms.vendors.id') }}</th>
                        <th>{{ __('cms.vendors.name') }}</th>
                        <th>{{ __('cms.vendors.email') }}</th>
                        <th>{{ __('cms.vendors.phone') }}</th>
                        <th>{{ __('cms.vendors.status') }}</th>
                        <th>{{ __('cms.vendors.actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteVendorModal" tabindex="-1" aria-labelledby="deleteVendorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="deleteVendorModalLabel">{{ __('cms.vendors.modal_confirm_delete_title') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">{{ __('cms.vendors.modal_confirm_delete_body') }}</div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.vendors.cancel') }}</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteVendor">{{ __('cms.vendors.delete') }}</button>
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
    $('#vendors-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.vendors.data') }}",
            type: "GET"
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { 
                data: 'status',
                name: 'status',
                render: function(data) {
                    return data === 'active' 
                        ? '<span class="badge bg-success">{{ __('cms.vendors.active') }}</span>'
                        : '<span class="badge bg-danger">{{ __('cms.vendors.inactive') }}</span>';
                }
            },
            {
                data: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-danger btn-delete-vendor" data-id="${row.id}">
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
});

let vendorToDeleteId = null;

$(document).on('click', '.btn-delete-vendor', function() {
    vendorToDeleteId = $(this).data('id');        
    $('#deleteVendorModal').modal('show');
});

$('#confirmDeleteVendor').off('click').on('click', function() {
    if (vendorToDeleteId !== null) {
        $.ajax({
            url: '{{ route('admin.vendors.destroy', ':id') }}'.replace(':id', vendorToDeleteId),
            method: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                if (response.success) {
                    $('#vendors-table').DataTable().ajax.reload();
                    toastr.error(response.message, "Deleted", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteVendorModal').modal('hide');
                }
            },
            error: function() {
                toastr.error("{{ __('cms.vendors.error_delete') }}", "Error");
                $('#deleteVendorModal').modal('hide');
            }
        });
    }
});
</script>
@endsection
