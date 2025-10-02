import axios from 'axios';

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

const createEmptyRow = (tableElement) => {
    const tableBody = tableElement?.querySelector('tbody');
    if (!tableBody) {
        return null;
    }

    const emptyMessage = tableElement.dataset.emptyMessage ?? '';
    const columnCountValue = tableElement.dataset.columnCount ?? '1';
    const columnCount = Number.parseInt(columnCountValue, 10);

    const emptyRow = document.createElement('tr');
    emptyRow.dataset.ordersEmptyRow = '';

    const emptyCell = document.createElement('td');
    emptyCell.colSpan = Number.isNaN(columnCount) ? 1 : columnCount;
    emptyCell.className = 'table-cell py-6 text-center text-sm text-gray-500';
    emptyCell.textContent = emptyMessage;

    emptyRow.appendChild(emptyCell);

    return emptyRow;
};

const removeOrderRow = (orderId, tableElement) => {
    if (!tableElement) {
        window.location.reload();
        return;
    }

    const deleteButton = document.querySelector(`[data-order-delete="${orderId}"]`);
    const row = deleteButton?.closest('tr') ?? tableElement.querySelector(`[data-order-row="${orderId}"]`);

    if (!row) {
        window.location.reload();
        return;
    }

    const tableBody = row.parentElement;
    row.remove();

    if (!tableBody || tableBody.children.length > 0) {
        return;
    }

    const emptyRow = createEmptyRow(tableElement);
    if (emptyRow) {
        tableBody.appendChild(emptyRow);
    }
};

const initializeDeleteModal = () => {
    const modal = document.querySelector('[data-orders-delete-modal]');
    if (!modal) {
        return;
    }

    const confirmButton = modal.querySelector('[data-confirm-delete]');
    const cancelTriggers = modal.querySelectorAll('[data-dismiss-modal]');
    const orderLabel = modal.querySelector('[data-order-label]');
    const tableElement = document.querySelector('[data-orders-table]');

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
        const deleteButton = event.target.closest?.('[data-order-delete]');
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
                removeOrderRow(currentOrderId, tableElement);
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

document.addEventListener('DOMContentLoaded', initializeDeleteModal);
