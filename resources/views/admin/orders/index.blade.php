@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white">
            <h6>{{ __('cms.orders.title') }}</h6>
        </div>
        <div class="card-body">
            <table id="orders-table" class="table table-bordered mt-4 w-100">
                <thead>
                    <tr>
                        <th>{{ __('cms.orders.id') }}</th>
                        <th>{{ __('cms.orders.order_date') }}</th>
                        <th>{{ __('cms.orders.status') }}</th>
                        <th>{{ __('cms.orders.total_price') }}</th>
                        <th>{{ __('cms.orders.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cms.orders.delete_confirm_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">{{ __('cms.orders.delete_confirm_message') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('cms.orders.delete_cancel') }}</button>
                    <button type="button" class="btn btn-danger"
                            id="confirmDeleteOrder">{{ __('cms.orders.delete_button') }}</button>
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
            const table = $('#orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.orders.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        const params = new URLSearchParams(window.location.search);
                        const status = params.get('status');
                        if (status) {
                            d.status = status;
                        }
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'order_date',
                        name: 'order_date',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'total_price',
                        name: 'total_price',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                pageLength: 10,
                language: @json($datatableLang)
            });

            let orderToDeleteId = null;

            $(document).on('click', '.btn-view-order', function() {
                const url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });

            $(document).on('click', '.btn-delete-order', function() {
                orderToDeleteId = $(this).data('id');
                $('#deleteOrderModal').modal('show');
            });

            $('#confirmDeleteOrder').on('click', function() {
                if (orderToDeleteId === null) return;

                $.ajax({
                    url: '{{ route('admin.orders.destroy', ':id') }}'.replace(':id',
                        orderToDeleteId),
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            table.ajax.reload(null, false);
                            toastr.error(res.message || 'Order deleted successfully',
                                "Deleted", {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-top-right",
                                    timeOut: 5000
                                });
                            $('#deleteOrderModal').modal('hide');
                            orderToDeleteId = null;
                        } else {
                            toastr.error(res.message || 'Failed to delete order', "Error", {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-top-right",
                                timeOut: 5000
                            });
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while deleting the order', "Error", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                        $('#deleteOrderModal').modal('hide');
                    }
                });
            });
        });
    </script>
@endsection
