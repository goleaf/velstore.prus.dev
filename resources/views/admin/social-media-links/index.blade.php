

@extends('admin.layouts.admin')

@section('content')

<div class="card mt-4">
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.social_media_links.create') }}</h6>
    </div>
    <div class="card-body">
        
        <a href="{{ route('admin.social-media-links.create') }}" class="btn btn-success float-end mb-3">{{ __('cms.social_media_links.add_new') }}</a>
        <table id="social-media-links-table" class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>{{ __('cms.social_media_links.id') }}</th>
                    <th>{{ __('cms.social_media_links.platform') }}</th>
                    <th>{{ __('cms.social_media_links.link') }}</th>
                    <th>{{ __('cms.social_media_links.action') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Delete Social Media Link Modal -->
<div class="modal fade" id="deleteSocialMediaLinkModal" tabindex="-1" aria-labelledby="deleteSocialMediaLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSocialMediaLinkModalLabel">{{ __('cms.social_media_links.massage_confirm') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"> {{ __('cms.social_media_links.confirm_delete') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.social_media_links.massage_cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSocialMediaLink">{{ __('cms.social_media_links.massage_delete') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- End Delete Social Media Link Modal -->

@endsection

@section('js')
@php
    $datatableLang = __('cms.datatables'); // Load the datatables translation
@endphp

<script>
    $(document).ready(function() {
    $('#social-media-links-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.social-media-links.data') }}",
            type: 'POST',
            data: function(d) {
                d._token = "{{ csrf_token() }}";
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'platform', name: 'platform' },
            { data: 'link', name: 'link' },
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


let socialMediaLinkToDeleteId = null;

$(document).on('click', '.btn-edit-social-link', function() {
    const url = $(this).data('url');
    if (url) {
        window.location.href = url;
    }
});

$(document).on('click', '.btn-delete-social-link', function() {
    socialMediaLinkToDeleteId = $(this).data('id');
    $('#deleteSocialMediaLinkModal').modal('show');
});

$('#confirmDeleteSocialMediaLink').off('click').on('click', function() {
    if (socialMediaLinkToDeleteId !== null) {
        $.ajax({
            url: '{{ route('admin.social-media-links.destroy', ':id') }}'.replace(':id', socialMediaLinkToDeleteId),
            method: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                if (response.success) {
                    $('#social-media-links-table').DataTable().ajax.reload();
                    toastr.error(response.message, "Success", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteSocialMediaLinkModal').modal('hide');
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
                toastr.error("Error deleting social media link! Please try again.", "Error", {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: 5000
                });
                $('#deleteSocialMediaLinkModal').modal('hide');
            }
        });
    }
}); 

</script>

@endsection

