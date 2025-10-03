@extends('admin.layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4">
    <div>
        <h1 class="h4 mb-1">{{ __('cms.pages.title') }}</h1>
        <p class="text-muted mb-0">{{ __('cms.pages.index_description') }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('cms.pages.create_button') }}
        </a>
    </div>
</div>

<div class="row g-3 mt-1" data-page-stats>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted text-uppercase small fw-semibold mb-1">{{ __('cms.pages.stat_total') }}</p>
                <p class="display-6 mb-0">{{ $stats['total'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100 border-success-subtle">
            <div class="card-body">
                <p class="text-muted text-uppercase small fw-semibold mb-1">{{ __('cms.pages.stat_active') }}</p>
                <p class="display-6 text-success mb-0">{{ $stats['active'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100 border-warning-subtle">
            <div class="card-body">
                <p class="text-muted text-uppercase small fw-semibold mb-1">{{ __('cms.pages.stat_inactive') }}</p>
                <p class="display-6 text-warning mb-0">{{ $stats['inactive'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted text-uppercase small fw-semibold mb-1">{{ __('cms.pages.stat_last_updated') }}</p>
                <p class="fs-4 mb-0">
                    @if ($stats['last_updated'])
                        {{ $stats['last_updated']->diffForHumans() }}
                    @else
                        {{ __('cms.pages.stat_last_updated_never') }}
                    @endif
                </p>
                <span class="small text-muted">{{ $stats['last_updated']?->format('M j, Y H:i') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 shadow-sm">
    <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div>
            <h2 class="h6 mb-0">{{ __('cms.pages.table_heading') }}</h2>
            <p class="text-muted small mb-0">{{ __('cms.pages.table_description') }}</p>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="pagesTable" class="table table-striped align-middle" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('cms.pages.table_title') }}</th>
                        <th>{{ __('cms.pages.table_slug') }}</th>
                        <th>{{ __('cms.pages.table_languages') }}</th>
                        <th>{{ __('cms.pages.table_status') }}</th>
                        <th>{{ __('cms.pages.table_last_updated') }}</th>
                        <th>{{ __('cms.pages.table_actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
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
            { data: 'title', name: 'title' },
            { data: 'slug', name: 'slug' },
            { data: 'languages', name: 'languages', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'updated_at', name: 'updated_at' },
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

    $(document).on('change', '.js-page-status-toggle', function() {
        const checkbox = $(this);
        const pageId = checkbox.data('id');
        const status = checkbox.is(':checked') ? 1 : 0;
        const previousState = status === 1 ? 0 : 1;

        checkbox.prop('disabled', true);

        $.ajax({
            url: '{{ route("admin.pages.updateStatus") }}',
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: pageId,
                status: status,
            },
            success: function(response) {
                toastr.success(response.message || "{{ __('cms.pages.toastr_status_updated') }}");
                table.ajax.reload(null, false);
            },
            error: function() {
                checkbox.prop('checked', previousState === 1);
                toastr.error("{{ __('cms.pages.toastr_status_error') }}");
            },
            complete: function() {
                checkbox.prop('disabled', false);
            }
        });
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
