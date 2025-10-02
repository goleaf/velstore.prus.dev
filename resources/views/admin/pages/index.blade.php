@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="mb-0">{{ __('cms.pages.title') }}</h6>
    </div>
    <div class="card-body">
        <table id="pagesTable" class="table table-bordered mt-4 dt-style">
            <thead>
                <tr>
                    <th>{{ __('cms.pages.table_title') }}</th>
                    <th>{{ __('cms.pages.table_slug') }}</th>
                    <th>{{ __('cms.pages.table_status') }}</th>
                    <th>{{ __('cms.pages.table_actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Delete Page Modal -->
<div class="modal fade" id="deletePageModal" tabindex="-1" aria-labelledby="deletePageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('cms.pages.delete_modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">{{ __('cms.pages.delete_modal_text') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.pages.delete_modal_cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePage">{{ __('cms.pages.delete_modal_delete') }}</button>
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
let pageToDeleteId = null;

$(document).ready(function() {
    const table = $('#pagesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        destroy: true,
        language: {!! json_encode($datatableLang) !!}, 
        ajax: {
            url: "{{ route('admin.pages.data') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" }
        },
        columns: [
            { data: 'translated_title', name: 'translated_title' },
            { data: 'slug', name: 'slug' },
            { data: 'status', name: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10
    });

    $(document).on('click', '.btn-edit-page', function() {
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    });

    $(document).on('click', '.btn-delete-page', function() {
        pageToDeleteId = $(this).data('id');
        $('#deletePageModal').modal('show');
    });

    $('#confirmDeletePage').off('click').on('click', function() {
        if (!pageToDeleteId) return;

        $.ajax({
            url: '{{ route("admin.pages.destroy", ":id") }}'.replace(':id', pageToDeleteId),
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                _method: 'DELETE'
            },
            success: function(response) {
                table.ajax.reload();
                $('#deletePageModal').modal('hide');
                toastr.success(response.message || "{{ __('cms.pages.toastr_success') }}");
            },
            error: function() {
                toastr.error("{{ __('cms.pages.toastr_error') }}");
                $('#deletePageModal').modal('hide');
            }
        });
    });
});
</script>
@endsection
