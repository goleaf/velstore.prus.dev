
@extends('admin.layouts.admin')

@section('content')

<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.products.title_manage') }}</h6>
    </div>
    <div class="card-body">
        <table id="products-table" class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>{{ __('cms.products.id') }}</th>
                    <th>{{ __('cms.products.name') }}</th>                    
                    <th>{{ __('cms.products.price') }}</th>                   
                    <th>{{ __('cms.products.status') }}</th>
                    <th>{{ __('cms.products.action') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductModalLabel">{{ __('cms.products.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">{{ __('cms.products.delete_confirmation') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.products.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteProduct">{{ __('cms.products.delete') }}</button>
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
        var table = $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.products.data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },               
                { data: 'price', name: 'price' },              
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
                        var editUrl = '{{ route('admin.products.edit', ':id') }}'.replace(':id', row.id);
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-edit-product" data-url="${editUrl}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-delete-product" data-id="${row.id}">
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

        $(document).on('change', '.toggle-status', function() {
            var productId = $(this).data('id');
            var newStatus = $(this).prop('checked') ? 1 : 0;
            updateProductStatus(productId, newStatus);
        });

        $(document).on('click', '.btn-edit-product', function() {
            const url = $(this).data('url');
            if (url) {
                window.location.href = url;
            }
        });

        $(document).on('click', '.btn-delete-product', function() {
            productToDeleteId = $(this).data('id');
            $('#deleteProductModal').modal('show');
        });

    });

    let productToDeleteId = null;

    $('#confirmDeleteProduct').off('click').on('click', function() {
        if (productToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.products.destroy', ':id') }}'.replace(':id', productToDeleteId),
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.success) {
                        $('#products-table').DataTable().ajax.reload();
                        toastr.error(response.message, "Success", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                        $('#deleteProductModal').modal('hide');
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
                    toastr.error('Error deleting product!', "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteProductModal').modal('hide');
                }
            });
        }
    });

    function updateProductStatus(id, status) {
        $.ajax({
            url: '{{ route('admin.products.updateStatus') }}',
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    $('#products-table').DataTable().ajax.reload(); 
                    toastr.success(response.message, "Success", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                } else {
                    toastr.error(response.message, "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                }
            },
            error: function(xhr) {
                toastr.error("Error updating product status! Please try again.", "Error", {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: 5000
                });
            }
        });
    }

</script>

@endsection
