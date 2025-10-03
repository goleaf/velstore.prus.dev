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

const formatNumber = (value) => {
    const numericValue = Number(value);

    if (Number.isNaN(numericValue)) {
        return '0';
    }

    return new Intl.NumberFormat().format(numericValue);
};

const updateStats = (stats) => {
    if (!stats || typeof stats !== 'object') {
        return;
    }

    Object.entries(stats).forEach(([key, value]) => {
        const element = document.querySelector(`[data-coupons-stat="${key}"]`);
        if (!element) {
            return;
        }

        element.textContent = formatNumber(value);
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
    emptyRow.dataset.couponsEmptyRow = '';

    const emptyCell = document.createElement('td');
    emptyCell.colSpan = Number.isNaN(columnCount) ? 1 : columnCount;
    emptyCell.className = 'table-cell py-6 text-center text-sm text-gray-500';
    emptyCell.textContent = emptyMessage;

    emptyRow.appendChild(emptyCell);

    return emptyRow;
};

const removeCouponRow = (couponId, tableElement) => {
    if (!tableElement) {
        window.location.reload();
        return;
    }

    const deleteButton = document.querySelector(`[data-coupon-delete="${couponId}"]`);
    const row = deleteButton?.closest('tr') ?? tableElement.querySelector(`[data-coupon-row="${couponId}"]`);
    const tableBody = row?.parentElement ?? tableElement.querySelector('tbody');

    if (!row || !tableBody) {
        window.location.reload();
        return;
    }

    row.remove();

    if (tableBody.children.length > 0) {
        return;
    }

    const emptyRow = createEmptyRow(tableElement);
    if (emptyRow) {
        tableBody.appendChild(emptyRow);
    }
};

const initializeCouponPage = () => {
    const modal = document.querySelector('[data-coupons-delete-modal]');
    const tableElement = document.querySelector('[data-coupons-table]');

    if (!modal) {
        return;
    }

    const confirmButton = modal.querySelector('[data-confirm-delete]');
    const cancelTriggers = modal.querySelectorAll('[data-dismiss-modal]');
    const couponLabel = modal.querySelector('[data-coupon-label]');

    const deleteUrlTemplate = modal.dataset.deleteUrl;
    const successTitle = modal.dataset.successTitle;
    const successMessage = modal.dataset.successMessage;
    const errorTitle = modal.dataset.errorTitle;
    const errorMessage = modal.dataset.errorMessage;

    let currentCouponId = null;

    const hideModal = () => {
        modal.classList.add('hidden');
        currentCouponId = null;

        if (couponLabel) {
            couponLabel.textContent = '';
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
        const deleteButton = event.target.closest?.('[data-coupon-delete]');
        if (!deleteButton) {
            return;
        }

        event.preventDefault();

        currentCouponId = deleteButton.dataset.couponDelete ?? null;

        if (couponLabel) {
            couponLabel.textContent = deleteButton.dataset.couponLabel ?? '';
        }

        showModal();
    });

    if (!confirmButton || !deleteUrlTemplate) {
        return;
    }

    confirmButton.addEventListener('click', async () => {
        if (!currentCouponId) {
            return;
        }

        confirmButton.disabled = true;

        const csrfToken = getCsrfToken();
        const deleteUrl = deleteUrlTemplate.replace('__COUPON_ID__', currentCouponId);

        try {
            const response = await axios.delete(deleteUrl, {
                data: { _token: csrfToken },
            });

            if (response.data?.success) {
                removeCouponRow(currentCouponId, tableElement);
                updateStats(response.data.stats);
                showToast('success', response.data.message ?? successMessage, successTitle);
                hideModal();
            } else {
                showToast('error', response.data?.message ?? errorMessage, errorTitle);
            }
        } catch (error) {
            console.error('Failed to delete coupon', error);
            showToast('error', errorMessage, errorTitle);
        } finally {
            confirmButton.disabled = false;
        }
    });
};

document.addEventListener('DOMContentLoaded', initializeCouponPage);
