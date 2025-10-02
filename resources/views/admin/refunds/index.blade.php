@extends('admin.layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto mt-4 space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('cms.refunds.title') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('cms.refunds.manage') }}</p>
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
                            <th scope="col" class="table-header-cell">{{ __('cms.refunds.payment') }}</th>
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
    const dataTable = tableElement.DataTable({
        processing: true,
        serverSide: true,
        ajax: @json(route('admin.refunds.getData')),
        language: @json($datatableLang),
        columns: [
            { data: 'id', name: 'id' },
            { data: 'payment', name: 'payment_id' },
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
