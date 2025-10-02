@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.payments.title') }}</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="payments-table" class="table table-bordered table-striped align-middle mt-4 w-100">
                    <thead>
                        <tr>
                            <th>{{ __('cms.payments.id') }}</th>
                            <th>{{ __('cms.payments.order') }}</th>
                            <th>{{ __('cms.payments.gateway') }}</th>
                            <th>{{ __('cms.payments.amount') }}</th>
                            <th>{{ __('cms.payments.status') }}</th>
                            <th>{{ __('cms.payments.transaction') }}</th>
                            <th>{{ __('cms.payments.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate the body -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cms.payments.delete_confirm') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">{{ __('cms.payments.delete_message') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('cms.payments.cancel') }}</button>
                    <button type="button" class="btn btn-danger"
                            id="confirmDeletePayment">{{ __('cms.payments.delete') }}</button>
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
            const $paymentsTable = $('#payments-table');
            const $deletePaymentModal = $('#deletePaymentModal');
            const $confirmDeletePayment = $('#confirmDeletePayment');
            let paymentToDeleteId = null;

            const toastrOptions = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 5000
            };

            const table = $paymentsTable.DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.payments.getData') }}",
                language: @json($datatableLang),
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'order', name: 'order_id' },
                    { data: 'gateway', name: 'gateway_id' },
                    { data: 'amount', name: 'amount' },
                    { data: 'status', name: 'status' },
                    { data: 'transaction_id', name: 'transaction_id' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10
            });

            $(document).on('click', '.btn-view-payment', function() {
                const url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });

            $(document).on('click', '.btn-delete-payment', function() {
                paymentToDeleteId = $(this).data('id');
                $deletePaymentModal.modal('show');
            });

            $deletePaymentModal.on('hidden.bs.modal', function() {
                paymentToDeleteId = null;
            });

            $confirmDeletePayment.on('click', function() {
                if (paymentToDeleteId === null) {
                    return;
                }

                $confirmDeletePayment.prop('disabled', true);

                $.ajax({
                    url: '{{ route('admin.payments.destroy', ':id') }}'.replace(':id', paymentToDeleteId),
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    }
                })
                    .done(function(response) {
                        if (response.success) {
                            table.ajax.reload(null, false);
                            toastr.success(
                                response.message || '{{ __('cms.payments.deleted') }}',
                                '{{ __('cms.payments.success') }}',
                                toastrOptions
                            );
                            $deletePaymentModal.modal('hide');
                            paymentToDeleteId = null;
                        } else {
                            toastr.error(
                                response.message || '{{ __('cms.payments.delete_error') }}',
                                '{{ __('cms.payments.delete_error') }}',
                                toastrOptions
                            );
                        }
                    })
                    .fail(function() {
                        toastr.error(
                            '{{ __('cms.payments.delete_error') }}',
                            '{{ __('cms.payments.delete_error') }}',
                            toastrOptions
                        );
                    })
                    .always(function() {
                        $confirmDeletePayment.prop('disabled', false);
                    });
            });
        });
    </script>
@endsection
