@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('cms.coupons.heading') }}</h6>
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-light btn-sm">{{ __('cms.coupons.add_new') }}</a>
        </div>
        <div class="card-body">
            <table id="coupons-table" class="table table-bordered mt-4 w-100">
                <thead>
                    <tr>
                        <th>{{ __('cms.coupons.id') }}</th>
                        <th>{{ __('cms.coupons.code') }}</th>
                        <th>{{ __('cms.coupons.discount') }}</th>
                        <th>{{ __('cms.coupons.type') }}</th>
                        <th>{{ __('cms.coupons.expires_at') }}</th>
                        <th>{{ __('cms.coupons.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteCouponModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cms.coupons.delete_confirm_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">{{ __('cms.coupons.delete_confirm_message') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.coupons.delete_cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCoupon">{{ __('cms.coupons.delete_button') }}</button>
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
            const table = $('#coupons-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.coupons.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'code', name: 'code' },
                    { data: 'discount', name: 'discount', orderable: false, searchable: false },
                    { data: 'type', name: 'type' },
                    { data: 'expires_at', name: 'expires_at', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                language: @json($datatableLang)
            });

            let couponToDeleteId = null;

            $(document).on('click', '.btn-edit-coupon', function() {
                const url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });

            $(document).on('click', '.btn-delete-coupon', function() {
                couponToDeleteId = $(this).data('id');
                $('#deleteCouponModal').modal('show');
            });

            $('#confirmDeleteCoupon').on('click', function() {
                if (couponToDeleteId === null) {
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.coupons.destroy', ':id') }}'.replace(':id', couponToDeleteId),
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload(null, false);
                            toastr.success(response.message || '{{ __('cms.coupons.deleted') }}', "{{ __('cms.coupons.success') }}", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-top-right",
                                timeOut: 4000
                            });
                        } else {
                            toastr.error(response.message || '{{ __('cms.coupons.errors.delete_failed') }}', "{{ __('cms.coupons.error_title') }}", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-top-right",
                                timeOut: 5000
                            });
                        }
                        $('#deleteCouponModal').modal('hide');
                        couponToDeleteId = null;
                    },
                    error: function() {
                        toastr.error('{{ __('cms.coupons.errors.delete_failed') }}', "{{ __('cms.coupons.error_title') }}", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                        $('#deleteCouponModal').modal('hide');
                    }
                });
            });
        });
    </script>
@endsection
