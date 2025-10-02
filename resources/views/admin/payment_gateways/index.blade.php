@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.payment_gateways.title') }}</h6>
    </div>

    <div class="card-body">
        <table id="gateways-table" class="table table-bordered mt-4 dt-style">
            <thead>
                <tr>
                    <th>{{ __('cms.payment_gateways.id') }}</th>
                    <th>{{ __('cms.payment_gateways.name') }}</th>
                    <th>{{ __('cms.payment_gateways.code') }}</th>
                    <th>{{ __('cms.payment_gateways.status') }}</th>
                    <th>{{ __('cms.payment_gateways.action') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteGatewayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('cms.payment_gateways.delete_confirm') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">{{ __('cms.payment_gateways.delete_message') }}</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.payment_gateways.cancel') }}</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteGateway">{{ __('cms.payment_gateways.delete') }}</button>
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
    $('#gateways-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.payment-gateways.getData') }}",
        language: @json($datatableLang),
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { 
                data: 'status', 
                name: 'is_active', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    return data ? "{{ __('cms.payment_gateways.active') }}" : "{{ __('cms.payment_gateways.inactive') }}";
                }
            },
            {
                data: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    var editUrl = '{{ route('admin.payment-gateways.edit', ':id') }}'.replace(':id', row.id);
                    return `
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary btn-edit-gateway" data-url="${editUrl}">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-delete-gateway" data-id="${row.id}">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        pageLength: 10
    });

    $(document).on('click', '.btn-edit-gateway', function() {
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    });

    $(document).on('click', '.btn-delete-gateway', function() {
        gatewayToDeleteId = $(this).data('id');
        $('#deleteGatewayModal').modal('show');
    });
});

let gatewayToDeleteId = null;

$('#confirmDeleteGateway').off('click').on('click', function() {
    if (gatewayToDeleteId !== null) {
        $.ajax({
            url: '{{ route('admin.payment-gateways.destroy', ':id') }}'.replace(':id', gatewayToDeleteId),
            method: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                if (response.success) {
                    $('#gateways-table').DataTable().ajax.reload();

                    toastr.error(response.message, "Deleted", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteGatewayModal').modal('hide');
                } else {
                    toastr.error(response.message || 'Error deleting payment gateway!', "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                }
            },
            error: function() {
                toastr.error('Error deleting payment gateway!', "Error", {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: 5000
                });
                $('#deleteGatewayModal').modal('hide');
            }
        });
    }
});
</script>
@endsection
