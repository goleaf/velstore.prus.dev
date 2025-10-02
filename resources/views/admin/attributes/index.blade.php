
@extends('admin.layouts.admin')

@section('content')

<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.attributes.title_manage') }}</h6>
    </div>
    <div class="card-body">
        <table id="attributes-table" class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>{{ __('cms.attributes.id') }}</th>
                    <th>{{ __('cms.attributes.name') }}</th>
                    <th>{{ __('cms.attributes.values') }}</th>
                    <th>{{ __('cms.attributes.action') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="deleteAttributeModal" tabindex="-1" aria-labelledby="deleteAttributeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
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

@section('js')
@php
    $datatableLang = __('cms.datatables'); 
@endphp
<script>
    $(document).ready(function() {
        $('#attributes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.attributes.data') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'values', name: 'values', orderable: false, searchable: false },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var editUrl = '{{ route('admin.attributes.edit', ':id') }}'.replace(':id', row.id);
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-edit-attribute" data-url="${editUrl}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-delete-attribute" data-id="${row.id}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            pageLength: 10,
            language: {!! json_encode($datatableLang) !!}
        });
    });


    let attributeToDeleteId = null;

    $(document).on('click', '.btn-edit-attribute', function() {
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    });

    $(document).on('click', '.btn-delete-attribute', function() {
        attributeToDeleteId = $(this).data('id');
        $('#deleteAttributeModal').modal('show');
    });

    $('#confirmDeleteAttribute').off('click').on('click', function() {
        if (attributeToDeleteId !== null) {
            $.ajax({
                url: '{{ route('admin.attributes.destroy', ':id') }}'.replace(':id', attributeToDeleteId),
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.success) {
                        $('#attributes-table').DataTable().ajax.reload();
                        toastr.error(response.message, "Success", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                        $('#deleteAttributeModal').modal('hide');
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
                    toastr.error("Error deleting attribute! Please try again.", "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteAttributeModal').modal('hide');
                }
            });
        }
    });

</script>

@endsection
