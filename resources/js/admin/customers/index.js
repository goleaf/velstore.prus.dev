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

const removeRow = (customerId) => {
    const trigger = document.querySelector(`[data-customer-delete="${customerId}"]`);
    const row = trigger?.closest('tr');

    if (!row) {
        window.location.reload();
        return;
    }

    const tbody = row.parentElement;
    row.remove();

    if (!tbody) {
        return;
    }

    if (tbody.children.length === 0) {
        const table = tbody.closest('[data-customers-table]');
        const emptyMessage = table?.dataset.emptyMessage ?? '';
        const columnCountValue = table?.dataset.columnCount ?? '1';
        const columnCount = Number.parseInt(columnCountValue, 10);

        const emptyRow = document.createElement('tr');
        emptyRow.dataset.customersEmptyRow = '';

        const emptyCell = document.createElement('td');
        emptyCell.colSpan = Number.isNaN(columnCount) ? 1 : columnCount;
        emptyCell.className = 'table-cell py-6 text-center text-sm text-gray-500';
        emptyCell.textContent = emptyMessage;

        emptyRow.appendChild(emptyCell);
        tbody.appendChild(emptyRow);
    }
};

const initializeCustomersModule = () => {
    const modal = document.querySelector('[data-customer-delete-modal]');
    if (!modal) {
        return;
    }

    const confirmButton = modal.querySelector('[data-confirm-delete]');
    const dismissTriggers = modal.querySelectorAll('[data-dismiss-modal]');
    const labelElement = modal.querySelector('[data-customer-label]');

    const deleteUrlTemplate = modal.dataset.deleteUrl;
    const successTitle = modal.dataset.successTitle;
    const successMessage = modal.dataset.successMessage;
    const errorTitle = modal.dataset.errorTitle;
    const errorMessage = modal.dataset.errorMessage;

    let currentCustomerId = null;

    const hideModal = () => {
        modal.classList.add('hidden');
        currentCustomerId = null;
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

    dismissTriggers.forEach((trigger) => {
        trigger.addEventListener('click', hideModal);
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
        const button = event.target.closest?.('[data-customer-delete]');
        if (!button) {
            return;
        }

        currentCustomerId = button.dataset.customerDelete ?? null;
        const customerLabel = button.dataset.customerLabel ?? '';

        if (labelElement) {
            labelElement.textContent = customerLabel;
        }

        showModal();
    });

    if (!confirmButton || !deleteUrlTemplate) {
        return;
    }

    confirmButton.addEventListener('click', async () => {
        if (!currentCustomerId) {
            return;
        }

        confirmButton.disabled = true;

        const csrfToken = getCsrfToken();
        const deleteUrl = deleteUrlTemplate.replace('__CUSTOMER_ID__', currentCustomerId);

        try {
            const response = await axios.delete(deleteUrl, {
                data: {
                    _token: csrfToken,
                },
            });

            if (response.data?.success) {
                removeRow(currentCustomerId);
                showToast('success', response.data?.message ?? successMessage, successTitle);
                hideModal();
            } else {
                showToast('error', response.data?.message ?? errorMessage, errorTitle);
            }
        } catch (error) {
            console.error('Failed to delete customer', error);
            showToast('error', errorMessage, errorTitle);
        } finally {
            confirmButton.disabled = false;
        }
    });
};

document.addEventListener('DOMContentLoaded', initializeCustomersModule);
