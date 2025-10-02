@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.customers.customer_list') }}</h6>
        <button type="button" class="btn btn-light btn-sm"
                data-url="{{ route('admin.customers.create') }}">{{ __('cms.sidebar.customers.add_new') }}</button>
    </div>
    <div class="card-body">
        <table id="customers-table" class="table table-bordered mt-4 dt-style">
            <thead>
                <tr>
                    <th>{{ __('cms.customers.id') }}</th>
                    <th>{{ __('cms.customers.name') }}</th>
                    <th>{{ __('cms.customers.email') }}</th>
                    <th>{{ __('cms.customers.phone') }}</th>
                    <th>{{ __('cms.customers.address') }}</th>
                    <th>{{ __('cms.customers.status') }}</th>
                    <th>{{ __('cms.customers.actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCustomerModalLabel">{{ __('cms.customers.confirm_delete_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{ __('cms.customers.confirm_delete_message') }}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.customers.cancel_button') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCustomer">{{ __('cms.customers.delete_button') }}</button>
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
    $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.customers.data') }}",
            type: "GET"
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'address', name: 'address' },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ],
        pageLength: 10,
        language: @json($datatableLang)
    });
});

let customerToDeleteId = null;

$(document).on('click', '.btn-delete-customer', function() {
    customerToDeleteId = $(this).data('id');
    $('#deleteCustomerModal').modal('show');
});

$('#confirmDeleteCustomer').off('click').on('click', function() {
    if (customerToDeleteId !== null) {
        $.ajax({
            url: '{{ route('admin.customers.destroy', ':id') }}'.replace(':id', customerToDeleteId),
            method: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                if (response.success) {
                    $('#customers-table').DataTable().ajax.reload();
                    toastr.error(response.message, "Deleted", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteCustomerModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting customer', "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                }
            },
            error: function() {
                toastr.error("Error deleting customer!", "Error");
                $('#deleteCustomerModal').modal('hide');
            }
        });
    }
});
</script>
@endsection
