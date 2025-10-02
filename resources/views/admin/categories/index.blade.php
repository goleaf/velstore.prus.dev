
@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header  card-header-bg text-white">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.categories.heading') }}</h6>
        </div>
        <div class="card-body">
            <table id="categories-table" class="table table-bordered mt-4 dt-style">
                <thead>
                    <tr>
                        <th>{{ __('cms.categories.id') }}</th>
                        <th>{{ __('cms.categories.name') }}</th>
                        <th>{{ __('cms.categories.status') }}</th>
                        <th>{{ __('cms.categories.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">{{ __('cms.categories.massage_confirm') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"> {{ __('cms.categories.confirm_delete') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.categories.massage_cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCategory">{{ __('cms.categories.massage_delete') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete Category Modal -->
@endsection

@section('js')
@php
    $datatableLang = __('cms.datatables');
@endphp
<script>
    $(document).ready(function() {
        $('#categories-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.categories.data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },              
                {
                    data: 'status',
                    name: 'status',
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
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var editUrl = '{{ route('admin.categories.edit', ':id') }}'.replace(':id', row.id);
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-edit-category" data-url="${editUrl}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-delete-category" data-id="${row.id}">
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
            var categoryId = $(this).data('id');
            var isActive = $(this).prop('checked') ? 1 : 0; 
            $.ajax({
                url: '{{ route('admin.categories.updateStatus') }}',
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: categoryId,
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

    let categoryToDeleteId = null;

    $(document).on('click', '.btn-edit-category', function() {
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    });

    $(document).on('click', '.btn-delete-category', function() {
        categoryToDeleteId = $(this).data('id');
        $('#deleteCategoryModal').modal('show');
    });

    $('#confirmDeleteCategory').off('click').on('click', function() {
        if (categoryToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.categories.destroy', ':id') }}'.replace(':id', categoryToDeleteId),
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.success) {
                        $('#categories-table').DataTable().ajax.reload();
                        toastr.error(response.message, "Success", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });

                        $('#deleteCategoryModal').modal('hide');
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
                    console.log('Error deleting category!');
                    $('#deleteCategoryModal').modal('hide');
                }
            });
        }
    });
</script>
@endsection


