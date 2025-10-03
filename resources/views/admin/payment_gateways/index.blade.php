@extends('admin.layouts.admin')

@section('content')
<x-admin.page-header
    :title="__('cms.payment_gateways.title')"
    :description="__('cms.payment_gateways.index_description')"
>
    <a href="{{ route('admin.payment-gateways.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> {{ __('cms.payment_gateways.create_button') }}
    </a>
</x-admin.page-header>

<x-admin.card class="mt-6" title="{{ __('cms.payment_gateways.quick_stats_title') }}">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 border rounded bg-light h-100">
                <p class="text-uppercase text-muted small mb-1">{{ __('cms.payment_gateways.total_gateways') }}</p>
                <p class="h4 mb-0" id="stat-total-gateways">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-success-subtle h-100">
                <p class="text-uppercase text-success small mb-1">{{ __('cms.payment_gateways.active_gateways') }}</p>
                <p class="h4 mb-0 text-success" id="stat-active-gateways">{{ number_format($stats['active']) }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded bg-warning-subtle h-100">
                <p class="text-uppercase text-warning small mb-1">{{ __('cms.payment_gateways.inactive_gateways') }}</p>
                <p class="h4 mb-0 text-warning" id="stat-inactive-gateways">{{ number_format($stats['inactive']) }}</p>
            </div>
        </div>
    </div>
</x-admin.card>

<x-admin.card class="mt-4" title="{{ __('cms.payment_gateways.filters_title') }}">
    <div class="row g-3 align-items-end">
        <div class="col-md-6">
            <label for="gateway-search" class="form-label">{{ __('cms.payment_gateways.search_label') }}</label>
            <input
                type="search"
                id="gateway-search"
                class="form-control"
                placeholder="{{ __('cms.payment_gateways.search_placeholder') }}"
            >
        </div>
        <div class="col-md-3">
            <label for="status-filter" class="form-label">{{ __('cms.payment_gateways.status_filter_label') }}</label>
            <select id="status-filter" class="form-select">
                <option value="">{{ __('cms.payment_gateways.status_filter_all') }}</option>
                <option value="active" @selected($statusFilter === 'active')>{{ __('cms.payment_gateways.status_filter_active') }}</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>{{ __('cms.payment_gateways.status_filter_inactive') }}</option>
            </select>
        </div>
        <div class="col-md-3">
            <button id="reset-filters" type="button" class="btn btn-outline-secondary w-100">
                {{ __('cms.payment_gateways.reset_filters') }}
            </button>
        </div>
    </div>
</x-admin.card>

<x-admin.card class="mt-4">
    <div class="table-responsive">
        <table id="payment-gateways-table" class="table table-bordered table-striped align-middle w-100">
            <thead>
                <tr>
                    <th>{{ __('cms.payment_gateways.id') }}</th>
                    <th>{{ __('cms.payment_gateways.name') }}</th>
                    <th>{{ __('cms.payment_gateways.code') }}</th>
                    <th>{{ __('cms.payment_gateways.config_count') }}</th>
                    <th>{{ __('cms.payment_gateways.last_updated') }}</th>
                    <th>{{ __('cms.payment_gateways.status') }}</th>
                    <th>{{ __('cms.payment_gateways.action') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</x-admin.card>

<div class="modal fade" id="deleteGatewayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('cms.payment_gateways.delete_confirm') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">{{ __('cms.payment_gateways.delete_message') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.payment_gateways.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteGateway">{{ __('cms.payment_gateways.delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @php
        $datatableLang = __('cms.datatables');
    @endphp
    <script>
        (function () {
            'use strict';

            const tableElement = $('#payment-gateways-table');
            const statusFilter = document.getElementById('status-filter');
            const searchInput = document.getElementById('gateway-search');
            const resetButton = document.getElementById('reset-filters');
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteGatewayModal'));
            const csrfToken = @json(csrf_token());

            let gatewayToDeleteId = null;

            const table = tableElement.DataTable({
                processing: true,
                serverSide: true,
                language: @json($datatableLang),
                ajax: {
                    url: @json(route('admin.payment-gateways.getData')),
                    data: function (params) {
                        params.status = statusFilter.value;
                    }
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'payment_gateways.id' },
                    { data: 'name', name: 'payment_gateways.name' },
                    { data: 'code', name: 'payment_gateways.code' },
                    { data: 'configs_count', name: 'configs_count', searchable: false },
                    { data: 'updated_at_for_humans', name: 'payment_gateways.updated_at', searchable: false },
                    { data: 'status_badge', name: 'payment_gateways.is_active', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false },
                ],
            });

            table.on('xhr', function () {
                const json = table.ajax.json();

                if (json && json.stats) {
                    document.getElementById('stat-total-gateways').textContent = Number(json.stats.total).toLocaleString();
                    document.getElementById('stat-active-gateways').textContent = Number(json.stats.active).toLocaleString();
                    document.getElementById('stat-inactive-gateways').textContent = Number(json.stats.inactive).toLocaleString();
                }
            });

            statusFilter.addEventListener('change', function () {
                table.ajax.reload();
            });

            resetButton.addEventListener('click', function () {
                statusFilter.value = '';
                if (searchInput.value) {
                    searchInput.value = '';
                    table.search('');
                }
                table.ajax.reload();
            });

            searchInput.addEventListener('keyup', function () {
                table.search(this.value).draw();
            });

            $(document).on('click', '.btn-delete-gateway', function () {
                gatewayToDeleteId = $(this).data('id');
                deleteModal.show();
            });

            $('#confirmDeleteGateway').on('click', function () {
                if (!gatewayToDeleteId) {
                    return;
                }

                $.ajax({
                    url: @json(route('admin.payment-gateways.destroy', ':id')).replace(':id', gatewayToDeleteId),
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: function (response) {
                        if (response && response.success) {
                            toastr.success(response.message, @json(__('cms.payment_gateways.success')));
                            table.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || @json(__('cms.payment_gateways.delete_error')), @json(__('cms.payment_gateways.error')));
                        }
                    },
                    error: function () {
                        toastr.error(@json(__('cms.payment_gateways.delete_error')), @json(__('cms.payment_gateways.error')));
                    },
                    complete: function () {
                        deleteModal.hide();
                        gatewayToDeleteId = null;
                    }
                });
            });

            $(document).on('click', '.btn-toggle-status', function () {
                const url = $(this).data('url');

                if (!url) {
                    return;
                }

                $.ajax({
                    url: url,
                    method: 'PATCH',
                    data: { _token: csrfToken },
                    success: function (response) {
                        if (response && response.success) {
                            toastr.success(response.message, @json(__('cms.payment_gateways.success')));
                            table.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || @json(__('cms.payment_gateways.toggle_error')), @json(__('cms.payment_gateways.error')));
                        }
                    },
                    error: function () {
                        toastr.error(@json(__('cms.payment_gateways.toggle_error')), @json(__('cms.payment_gateways.error')));
                    }
                });
            });
        })();
    </script>
@endpush
