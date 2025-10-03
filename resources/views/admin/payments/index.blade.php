@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.payments.title') }}</h6>
        </div>

        <div class="card-body">
            <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle mb-4">
                <h6 class="mb-3 text-muted">{{ __('cms.payments.filters_heading') }}</h6>
                <form id="payment-filters" class="row g-3">
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="filterStatus" class="form-label">{{ __('cms.payments.status') }}</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">{{ __('cms.payments.filters_all_statuses') }}</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="filterGateway" class="form-label">{{ __('cms.payment_gateways.title') }}</label>
                        <select id="filterGateway" class="form-select">
                            <option value="">{{ __('cms.payments.filters_all_gateways') }}</option>
                            @foreach ($gateways as $gateway)
                                <option value="{{ $gateway->id }}">{{ $gateway->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="filterShop" class="form-label">{{ __('cms.payments.shops') }}</label>
                        <select id="filterShop" class="form-select">
                            <option value="">{{ __('cms.payments.filters_all_shops') }}</option>
                            @foreach ($shops as $shop)
                                <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="filterDateFrom" class="form-label">{{ __('cms.payments.filters_date_from') }}</label>
                        <input type="date" id="filterDateFrom" class="form-control">
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="filterDateTo" class="form-label">{{ __('cms.payments.filters_date_to') }}</label>
                        <input type="date" id="filterDateTo" class="form-control">
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">{{ __('cms.payments.filters_apply') }}</button>
                        <button type="button" class="btn btn-outline-secondary" id="resetFilters">{{ __('cms.payments.filters_reset') }}</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table id="payments-table" class="table table-bordered table-striped align-middle w-100">
                    <thead>
                        <tr>
                            <th>{{ __('cms.payments.id') }}</th>
                            <th>{{ __('cms.payments.order') }}</th>
                            <th>{{ __('cms.payments.user') }}</th>
                            <th>{{ __('cms.payments.shops') }}</th>
                            <th>{{ __('cms.payments.gateway') }}</th>
                            <th>{{ __('cms.payments.amount') }}</th>
                            <th>{{ __('cms.payments.status') }}</th>
                            <th>{{ __('cms.payments.created_at') }}</th>
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
                ajax: {
                    url: "{{ route('admin.payments.getData') }}",
                    data: function (data) {
                        data.status = $('#filterStatus').val();
                        data.gateway_id = $('#filterGateway').val();
                        data.shop_id = $('#filterShop').val();
                        data.date_from = $('#filterDateFrom').val();
                        data.date_to = $('#filterDateTo').val();
                    }
                },
                language: @json($datatableLang),
                columns: [
                    { data: 'id', name: 'payments.id' },
                    { data: 'order', name: 'order_id', orderable: false, searchable: false },
                    { data: 'customer', name: 'customer', orderable: false, searchable: false },
                    { data: 'shops', name: 'shops', orderable: false, searchable: false },
                    { data: 'gateway', name: 'gateway_id' },
                    { data: 'amount', name: 'amount' },
                    { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'transaction_id', name: 'transaction_id' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                order: [[7, 'desc']],
                pageLength: 10
            });

            $('#payment-filters').on('submit', function (event) {
                event.preventDefault();
                table.ajax.reload();
            });

            $('#resetFilters').on('click', function () {
                const form = document.getElementById('payment-filters');
                form.reset();
                table.ajax.reload();
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
