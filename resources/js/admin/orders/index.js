import $ from 'jquery';
import 'datatables.net-dt';
import axios from 'axios';

const parseJson = (value) => {
    if (!value) {
        return undefined;
    }

    try {
        return JSON.parse(value);
    } catch (error) {
        console.warn('Unable to parse JSON value for orders table language.', error);
        return undefined;
    }
};

const getCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

const showToast = (type, message, title) => {
    if (!window.toastr) {
        return;
    }

    const toast = window.toastr[type] ?? window.toastr.info;
    toast(message, title, {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000,
    });
};

const initializeDeleteModal = (tableInstance) => {
    const modal = document.querySelector('[data-orders-delete-modal]');
    if (!modal) {
        return;
    }

    const confirmButton = modal.querySelector('[data-confirm-delete]');
    const cancelTriggers = modal.querySelectorAll('[data-dismiss-modal]');
    const orderLabel = modal.querySelector('[data-order-label]');

    const deleteUrlTemplate = modal.dataset.deleteUrl;
    const successTitle = modal.dataset.successTitle;
    const successMessage = modal.dataset.successMessage;
    const errorTitle = modal.dataset.errorTitle;
    const errorMessage = modal.dataset.errorMessage;

    let currentOrderId = null;

    const hideModal = () => {
        modal.classList.add('hidden');
        currentOrderId = null;
        if (orderLabel) {
            orderLabel.textContent = '';
        }
        if (confirmButton) {
            confirmButton.disabled = false;
        }
    };

    const showModal = () => {
        modal.classList.remove('hidden');
    };

    cancelTriggers.forEach((trigger) => {
        trigger.addEventListener('click', hideModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            hideModal();
        }
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            hideModal();
        }
    });

    document.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('[data-order-delete]');
        if (!deleteButton) {
            return;
        }

        const { orderDelete: orderId, orderLabel: orderLabelText } = deleteButton.dataset;

        currentOrderId = orderId ?? null;

        if (orderLabel) {
            orderLabel.textContent = orderLabelText ?? '';
        }

        showModal();
    });

    if (!confirmButton || !deleteUrlTemplate) {
        return;
    }

    confirmButton.addEventListener('click', async () => {
        if (!currentOrderId) {
            return;
        }

        confirmButton.disabled = true;

        const csrfToken = getCsrfToken();
        const deleteUrl = deleteUrlTemplate.replace('__ORDER_ID__', currentOrderId);

        try {
            const response = await axios.delete(deleteUrl, {
                data: {
                    _token: csrfToken,
                },
            });

            if (response.data?.success) {
                tableInstance.ajax.reload(null, false);
                showToast('success', response.data.message ?? successMessage, successTitle);
                hideModal();
            } else {
                showToast('error', response.data?.message ?? errorMessage, errorTitle);
            }
        } catch (error) {
            console.error('Failed to delete order', error);
            showToast('error', errorMessage, errorTitle);
        } finally {
            confirmButton.disabled = false;
        }
    });
};

const initializeOrdersTable = () => {
    const tableElement = document.querySelector('[data-orders-table]');
    if (!tableElement) {
        return;
    }

    const language = parseJson(tableElement.dataset.language);
    const pageLength = Number.parseInt(tableElement.dataset.pageLength ?? '10', 10);
    const sourceUrl = tableElement.dataset.source;

    if (!sourceUrl) {
        return;
    }

    const tableInstance = $(tableElement).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: sourceUrl,
            type: 'POST',
            data(d) {
                d._token = getCsrfToken();
                const params = new URLSearchParams(window.location.search);
                const status = params.get('status');
                if (status) {
                    d.status = status;
                }
            },
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'order_date', name: 'order_date', orderable: false, searchable: false },
            { data: 'status', name: 'status' },
            { data: 'total_price', name: 'total_price', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        pageLength: Number.isNaN(pageLength) ? 10 : pageLength,
        language,
    });

    initializeDeleteModal(tableInstance);
};

document.addEventListener('DOMContentLoaded', initializeOrdersTable);
