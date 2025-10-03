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
    emptyRow.dataset.paymentsEmptyRow = '';

    const emptyCell = document.createElement('td');
    emptyCell.colSpan = Number.isNaN(columnCount) ? 1 : columnCount;
    emptyCell.className = 'table-cell py-6 text-center text-sm text-gray-500';
    emptyCell.textContent = emptyMessage;

    emptyRow.appendChild(emptyCell);
    return emptyRow;
};

const removePaymentRow = (paymentId) => {
    const deleteButton = document.querySelector(`[data-payment-delete="${paymentId}"]`);
    const row = deleteButton?.closest('tr');

    if (!row) {
        window.location.reload();
        return;
    }

    const tableBody = row.parentElement;
    row.remove();

    if (!tableBody) {
        return;
    }

    if (tableBody.children.length === 0) {
        const table = tableBody.closest('[data-payments-table]');
        const emptyRow = createEmptyRow(table);
        if (emptyRow) {
            tableBody.appendChild(emptyRow);
        }
    }
};

const initializePaymentsModule = () => {
    const modal = document.querySelector('[data-payment-delete-modal]');
    if (!modal) {
        return;
    }

    const confirmButton = modal.querySelector('[data-confirm-delete]');
    const dismissButtons = modal.querySelectorAll('[data-dismiss-modal]');
    const labelElement = modal.querySelector('[data-payment-label]');
    const deleteUrlTemplate = modal.dataset.deleteUrl;
    const successTitle = modal.dataset.successTitle;
    const successMessage = modal.dataset.successMessage;
    const errorTitle = modal.dataset.errorTitle;
    const errorMessage = modal.dataset.errorMessage;

    let currentPaymentId = null;

    const hideModal = () => {
        modal.classList.add('hidden');
        currentPaymentId = null;
        if (labelElement) {
            labelElement.textContent = '';
        }
        if (confirmButton) {
            confirmButton.disabled = false;
        }
    };

    const showModal = () => {
        modal.classList.remove('hidden');
    };

    dismissButtons.forEach((button) => {
        button.addEventListener('click', hideModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            hideModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            hideModal();
        }
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest?.('[data-payment-delete]');
        if (!trigger) {
            return;
        }

        currentPaymentId = trigger.dataset.paymentDelete ?? null;
        const paymentLabel = trigger.dataset.paymentLabel ?? '';

        if (labelElement) {
            labelElement.textContent = paymentLabel;
        }

        showModal();
    });

    if (!confirmButton || !deleteUrlTemplate) {
        return;
    }

    confirmButton.addEventListener('click', async () => {
        if (!currentPaymentId) {
            return;
        }

        confirmButton.disabled = true;

        const csrfToken = getCsrfToken();
        const deleteUrl = deleteUrlTemplate.replace('__PAYMENT_ID__', currentPaymentId);

        try {
            const response = await axios.delete(deleteUrl, {
                data: {
                    _token: csrfToken,
                },
            });

            if (response.data?.success) {
                removePaymentRow(currentPaymentId);
                showToast('success', response.data?.message ?? successMessage, successTitle);
                hideModal();
            } else {
                showToast('error', response.data?.message ?? errorMessage, errorTitle);
            }
        } catch (error) {
            console.error('Failed to delete payment', error);
            showToast('error', errorMessage, errorTitle);
        } finally {
            confirmButton.disabled = false;
        }
    });
};

document.addEventListener('DOMContentLoaded', initializePaymentsModule);
