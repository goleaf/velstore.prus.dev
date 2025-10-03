@extends('admin.layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto mt-4 space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('cms.refunds.title') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('cms.refunds.manage') }}</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-primary-100 bg-primary-50 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-primary-600">{{ __('cms.refunds.summary_total_count') }}</p>
            <p class="mt-2 text-2xl font-semibold text-primary-900">{{ number_format($stats['total'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-success-100 bg-success-50 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-success-600">{{ __('cms.refunds.summary_completed_count') }}</p>
            <p class="mt-2 text-2xl font-semibold text-success-900">{{ number_format($stats['completed'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">{{ __('cms.refunds.summary_total_amount') }}</p>
            <p class="mt-2 text-2xl font-semibold text-amber-900">{{ number_format($stats['refunded_amount'] ?? 0, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('cms.refunds.summary_pending_count') }}</p>
            <p class="mt-2 text-2xl font-semibold text-indigo-900">{{ number_format($stats['pending'] ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('cms.refunds.summary_average_amount') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stats['average_amount'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
        <div class="bg-white border border-gray-200 shadow-sm rounded-2xl">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('cms.refunds.filters_title') }}</h2>
            </div>

            <div class="px-6 py-6">
                <form id="refundFilters" class="grid gap-4 lg:grid-cols-6">
                    <div class="lg:col-span-2">
                        <label class="form-label" for="refundStatusFilter">{{ __('cms.refunds.status_filter_label') }}</label>
                        <select
                            id="refundStatusFilter"
                            name="status[]"
                            class="form-select h-36"
                            multiple
                            aria-describedby="refundStatusHelp"
                        >
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(in_array($value, $filters['status'] ?? []))>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <p id="refundStatusHelp" class="mt-2 text-xs text-gray-500">{{ __('cms.refunds.status_filter_help') }}</p>
                    </div>
                    <div>
                        <label class="form-label" for="refundDateFrom">{{ __('cms.refunds.date_from_label') }}</label>
                        <input
                            id="refundDateFrom"
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ $filters['date_from'] ?? '' }}"
                        >
                    </div>
                    <div>
                        <label class="form-label" for="refundDateTo">{{ __('cms.refunds.date_to_label') }}</label>
                        <input
                            id="refundDateTo"
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ $filters['date_to'] ?? '' }}"
                        >
                    </div>
                    <div>
                        <label class="form-label" for="refundShopFilter">{{ __('cms.refunds.shop_filter_label') }}</label>
                        <select id="refundShopFilter" name="shop_id" class="form-select">
                            <option value="">{{ __('cms.refunds.shop_filter_placeholder') }}</option>
                            @foreach ($shopOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $value === (string) ($filters['shop_id'] ?? ''))>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="refundGatewayFilter">{{ __('cms.refunds.gateway_filter_label') }}</label>
                        <select id="refundGatewayFilter" name="gateway_id" class="form-select">
                            <option value="">{{ __('cms.refunds.gateway_filter_placeholder') }}</option>
                            @foreach ($gatewayOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) $value === (string) ($filters['gateway_id'] ?? ''))>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="form-label" for="refundSearch">{{ __('cms.refunds.search_filter_label') }}</label>
                        <input
                            id="refundSearch"
                            type="search"
                            name="search_term"
                            class="form-control"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="{{ __('cms.refunds.search_filter_placeholder') }}"
                        >
                    </div>
                    <div class="lg:col-span-6 flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="btn btn-primary">{{ __('cms.refunds.apply_filters') }}</button>
                        <button type="button" class="btn btn-outline" id="resetRefundFilters">{{ __('cms.refunds.reset_filters') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white border border-gray-200 shadow-sm rounded-2xl">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('cms.refunds.shop_breakdown_title') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('cms.refunds.shop_breakdown_help') }}</p>
            </div>
            <div class="px-6 py-6">
                <div class="space-y-4">
                    @forelse ($shopBreakdown as $shop)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $shop->shop_name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ trans_choice('cms.refunds.shop_breakdown_count', $shop->refund_count ?? 0, ['count' => $shop->refund_count ?? 0]) }}
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-primary-600">{{ number_format((float) ($shop->total_amount ?? 0), 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('cms.refunds.shop_breakdown_empty') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 shadow-sm rounded-2xl">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('cms.refunds.list') }}</h2>
        </div>

        <div class="px-6 py-6">
            <div class="overflow-x-auto">
                <table id="refunds-table" class="table w-full text-sm text-gray-600">
                    <thead class="table-header">
                        <tr>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.id') }}</th>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.reference') }}</th>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.payment') }}</th>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.shop_column') }}</th>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.customer_column') }}</th>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.amount') }}</th>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.status') }}</th>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.reason') }}</th>
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-body"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="deleteRefundModal"
     class="fixed inset-0 z-40 hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="deleteRefundModalTitle"
     aria-hidden="true">
    <div class="flex min-h-full items-center justify-center px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/50" data-refunds-modal-backdrop></div>

        <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 id="deleteRefundModalTitle" class="text-lg font-semibold text-gray-900">
                    {{ __('cms.refunds.delete_confirm') }}
                </h2>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm text-gray-600">{{ __('cms.refunds.delete_message') }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                <button type="button" class="btn btn-outline" data-refunds-modal-close>
                    {{ __('cms.refunds.cancel') }}
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteRefund">
                    {{ __('cms.refunds.delete') }}
                </button>
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
document.addEventListener('DOMContentLoaded', () => {
    const tableElement = $('#refunds-table');
    const filterForm = document.getElementById('refundFilters');
    const resetButton = document.getElementById('resetRefundFilters');
    const statusSelect = document.getElementById('refundStatusFilter');
    const shopSelect = document.getElementById('refundShopFilter');
    const gatewaySelect = document.getElementById('refundGatewayFilter');
    const searchInput = document.getElementById('refundSearch');

    const dataTable = tableElement.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: @json(route('admin.refunds.getData')),
            data: (params) => {
                if (!filterForm) {
                    return;
                }

                const formData = new FormData(filterForm);
                const statuses = formData.getAll('status[]').filter(Boolean);

                if (statuses.length) {
                    params.status = statuses;
                }

                const dateFrom = formData.get('date_from');
                const dateTo = formData.get('date_to');

                if (dateFrom) {
                    params.date_from = dateFrom;
                }

                if (dateTo) {
                    params.date_to = dateTo;
                }

                const shopId = formData.get('shop_id');
                if (shopId) {
                    params.shop_id = shopId;
                }

                const gatewayId = formData.get('gateway_id');
                if (gatewayId) {
                    params.gateway_id = gatewayId;
                }

                const searchTerm = formData.get('search_term');
                if (searchTerm) {
                    params.search_term = searchTerm;
                }
            },
        },
        language: @json($datatableLang),
        columns: [
            { data: 'id', name: 'id' },
            { data: 'reference', name: 'refund_id', orderable: false, searchable: false },
            { data: 'payment', name: 'payment_id' },
            { data: 'shop', name: 'shop_name', orderable: false, searchable: false },
            { data: 'customer', name: 'customer_name', orderable: false, searchable: false },
            { data: 'amount', name: 'amount' },
            { data: 'status', name: 'status' },
            { data: 'reason', name: 'reason' },
            { data: 'action', orderable: false, searchable: false },
        ],
        pageLength: 10,
        responsive: true,
        order: [[0, 'desc']],
    });

    tableElement.on('draw.dt', () => {
        tableElement.find('tbody tr').each(function () {
            this.classList.add('table-row');
            $(this).children('td').each(function () {
                this.classList.add('table-cell');
            });
        });
    });

    filterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        dataTable.ajax.reload();
    });

    resetButton?.addEventListener('click', () => {
        if (!filterForm) {
            return;
        }

        filterForm.reset();

        if (statusSelect) {
            Array.from(statusSelect.options).forEach((option) => {
                option.selected = false;
            });
        }

        if (shopSelect) {
            shopSelect.value = '';
        }

        if (gatewaySelect) {
            gatewaySelect.value = '';
        }

        if (searchInput) {
            searchInput.value = '';
        }

        dataTable.ajax.reload();
    });

    let refundToDeleteId = null;

    const modal = document.getElementById('deleteRefundModal');
    const modalBackdrop = modal?.querySelector('[data-refunds-modal-backdrop]');
    const confirmButton = document.getElementById('confirmDeleteRefund');
    const closeButtons = modal?.querySelectorAll('[data-refunds-modal-close]') ?? [];

    const openModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.removeAttribute('aria-hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const viewButton = target.closest('.btn-view-refund');
        if (viewButton instanceof HTMLElement) {
            const url = viewButton.dataset.url;
            if (url) {
                event.preventDefault();
                window.location.href = url;
            }
        }

        const deleteButton = target.closest('.btn-delete-refund');
        if (deleteButton instanceof HTMLElement) {
            const refundId = deleteButton.dataset.id;
            if (refundId) {
                event.preventDefault();
                refundToDeleteId = refundId;
                openModal();
            }
        }
    });

    confirmButton?.addEventListener('click', async () => {
        if (!refundToDeleteId) {
            return;
        }

        const deleteUrlTemplate = @json(route('admin.refunds.destroy', '__REFUND__'));
        const deleteUrl = deleteUrlTemplate.replace('__REFUND__', refundToDeleteId);

        try {
            const { data } = await axios.delete(deleteUrl, {
                data: { _token: @json(csrf_token()) },
            });

            if (data?.success) {
                dataTable.ajax.reload(null, false);
                toastr.success(
                    data?.message ?? '{{ __('cms.refunds.delete') }}',
                    '{{ __('cms.refunds.success') }}',
                    {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        timeOut: 5000,
                    }
                );
            } else {
                toastr.error(
                    data?.message ?? '{{ __('cms.refunds.delete_error') }}',
                    '{{ __('cms.notifications.error') }}',
                    {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        timeOut: 5000,
                    }
                );
            }
        } catch (error) {
            toastr.error(
                '{{ __('cms.refunds.delete_error') }}',
                '{{ __('cms.notifications.error') }}',
                {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000,
                }
            );
        } finally {
            closeModal();
            refundToDeleteId = null;
        }
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            refundToDeleteId = null;
            closeModal();
        });
    });

    modalBackdrop?.addEventListener('click', () => {
        refundToDeleteId = null;
        closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal?.classList.contains('hidden')) {
            refundToDeleteId = null;
            closeModal();
        }
    });
});
</script>
@endsection
