

@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.brands.heading') }}
            </h6>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="deleteBrandModalLabel">{{ __('cms.brands.massage_confirm') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"> {{ __('cms.brands.confirm_delete') }}</div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.brands.massage_cancel') }}</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBrand">{{ __('cms.brands.massage_delete') }}</button>
            </div>
        </div>
        </div>
    </div>
    <!-- End Delete Modal -->
  
@endsection

@section('js')
@php
    $datatableLang = __('cms.datatables');
@endphp

<script>

    $(document).ready(function() {
        $('#brands-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.brands.getData') }}",
                type: 'GET',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'slug', name: 'slug' },
                { 
                    data: 'logo_url', 
                    render: function(data) {
                        if (data) {
                            var logoPath = data.startsWith('http') ? data : '/storage/' + data;
                            return '<img src="' + logoPath + '" alt="Logo" width="50">';
                        } else {
                            return 'No Logo';
                        }
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data, type, row) {
                        // Render a toggle switch based on the status value
                        var isChecked = data ? 'checked' : ''; // If active, checked
                        return `<label class="switch">
                                    <input type="checkbox" class="toggle-status" data-id="${row.id}" ${isChecked}>
                                    <span class="slider round"></span>
                                </label>`;
                    }
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var editUrl = '{{ route('admin.brands.edit', ':id') }}'.replace(':id', row.id);
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
            language: @json($datatableLang)
        });

        $(document).on('click', '.btn-edit-brand', function() {
            const url = $(this).data('url');
            if (url) {
                window.location.href = url;
            }
        });

        $(document).on('click', '.btn-delete-brand', function() {
            brandToDeleteId = $(this).data('id');
            $('#deleteBrandModal').modal('show');
        });

        // Handle toggle switch (activate/deactivate status)
        $(document).on('change', '.toggle-status', function() {
            var brandId = $(this).data('id');
            var isActive = $(this).prop('checked') ? 1 : 0; // 1 for active, 0 for inactive
            $.ajax({
                url: '{{ route('admin.brands.updateStatus') }}',
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: brandId,
                    status: isActive
                },
                success: function(response) {
                    // Optionally show a success message
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
                    // Optionally show an error message
                    alert('Error updating status!');
                    // Revert the toggle if something goes wrong
                    $(this).prop('checked', !isActive);
                }
            });
        });

    });


    let brandToDeleteId = null;

    $('#confirmDeleteBrand').off('click').on('click', function() {
        if (brandToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.brands.destroy', ':id') }}'.replace(':id', brandToDeleteId),
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.success) {
                        $('#brands-table').DataTable().ajax.reload();
                        toastr.error(response.message, "Deleted", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                        
                        $('#deleteBrandModal').modal('hide');
                    } else {
                        toastr.error(response.message || 'Error deleting brand', "Error", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                    }
                },
                error: function() {
                    toastr.error('Error deleting brand!', "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteBrandModal').modal('hide');
                }
            });
        }
    });

</script>
@endsection
