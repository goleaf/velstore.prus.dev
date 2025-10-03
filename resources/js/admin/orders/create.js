const safeJsonParse = (value, fallback) => {
    if (!value) {
        return fallback;
    }

    try {
        return JSON.parse(value);
    } catch (error) {
        console.warn('Failed to parse dataset JSON', error);
        return fallback;
    }
};

const formatCurrency = (value) => {
    const number = Number.parseFloat(value || 0);

    if (Number.isNaN(number)) {
        return '0.00';
    }

    return number.toFixed(2);
};

const getProductCollection = (data, shopId) => {
    if (!data || !shopId) {
        return [];
    }

    const normalizedKey = String(shopId);

    return data[normalizedKey] ?? data[Number.parseInt(normalizedKey, 10)] ?? [];
};

const initializeOrderForm = () => {
    const form = document.querySelector('[data-order-form]');
    if (!form) {
        return;
    }

    const shopSelect = form.querySelector('[data-order-shop]');
    const itemsContainer = form.querySelector('[data-order-items]');
    const addButton = form.querySelector('[data-add-item]');
    const template = form.querySelector('template[data-order-item-template]');
    const totalElement = form.querySelector('[data-order-total]');
    const emptyState = form.querySelector('[data-order-empty-state]');

    const productsData = safeJsonParse(form.dataset.products, {});
    const oldItems = safeJsonParse(form.dataset.oldItems, []);

    const productPlaceholder = form.dataset.productPlaceholder ?? '';
    const emptyText = form.dataset.emptyText ?? '';
    const selectShopText = form.dataset.selectShopText ?? '';
    const noProductsText = form.dataset.noProductsText ?? '';

    let lineIndex = 0;

    const getProductsForCurrentShop = () => {
        const shopId = shopSelect?.value;
        const products = getProductCollection(productsData, shopId);

        return Array.isArray(products) ? products : [];
    };

    const updateEmptyState = () => {
        if (!emptyState) {
            return;
        }

        const shopId = shopSelect?.value;
        const hasLines = !!itemsContainer?.querySelector('[data-order-item]');
        if (hasLines) {
            emptyState.classList.add('hidden');
            return;
        }

        let message = emptyText;
        if (!shopId) {
            message = selectShopText || emptyText;
        } else if (getProductsForCurrentShop().length === 0) {
            message = noProductsText || emptyText;
        }

        emptyState.textContent = message;
        emptyState.classList.remove('hidden');
    };

    const updateTotals = () => {
        if (!itemsContainer || !totalElement) {
            return;
        }

        let total = 0;
        itemsContainer.querySelectorAll('[data-order-item]').forEach((line) => {
            const quantityInput = line.querySelector('[data-item-quantity]');
            const unitPriceInput = line.querySelector('[data-item-unit-price]');
            const subtotalElement = line.querySelector('[data-item-subtotal]');

            const quantity = Number.parseFloat(quantityInput?.value ?? '0');
            const price = Number.parseFloat(unitPriceInput?.value ?? '0');
            const subtotal = (Number.isNaN(quantity) ? 0 : quantity) * (Number.isNaN(price) ? 0 : price);

            if (subtotalElement) {
                subtotalElement.textContent = formatCurrency(subtotal);
            }

            total += subtotal;
        });

        totalElement.textContent = formatCurrency(total);
    };

    const populateProductOptions = (selectElement, shopId, selectedValue = '') => {
        if (!selectElement) {
            return;
        }

        const products = getProductCollection(productsData, shopId);
        selectElement.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = productPlaceholder;
        selectElement.appendChild(placeholderOption);

        if (!Array.isArray(products)) {
            return;
        }

        products.forEach((product) => {
            const option = document.createElement('option');
            option.value = String(product.id);
            option.textContent = product.sku
                ? `${product.name} (${product.sku})`
                : product.name;
            option.dataset.price = product.price;
            option.dataset.sku = product.sku ?? '';
            selectElement.appendChild(option);
        });

        if (selectedValue && selectElement.querySelector(`option[value="${CSS.escape(String(selectedValue))}"]`)) {
            selectElement.value = String(selectedValue);
        }
    };

    const assignInputNames = (lineElement, index) => {
        lineElement.querySelectorAll('[data-name-template]').forEach((element) => {
            const templateName = element.getAttribute('data-name-template');
            if (!templateName) {
                return;
            }

            element.setAttribute('name', templateName.replace('__INDEX__', index));
            element.removeAttribute('data-name-template');
        });
    };

    const updateLinePricingDetails = (lineElement) => {
        const productSelect = lineElement.querySelector('[data-item-product]');
        const unitPriceInput = lineElement.querySelector('[data-item-unit-price]');
        const skuElement = lineElement.querySelector('[data-item-sku]');

        if (!productSelect) {
            return;
        }

        const selectedOption = productSelect.selectedOptions[0];
        const optionPrice = selectedOption ? Number.parseFloat(selectedOption.dataset.price ?? '0') : 0;

        if (unitPriceInput && !unitPriceInput.value) {
            unitPriceInput.value = optionPrice > 0 ? optionPrice.toFixed(2) : '';
        }

        if (skuElement) {
            skuElement.textContent = selectedOption?.dataset.sku ?? '';
        }
    };

    const bindLineEvents = (lineElement) => {
        const productSelect = lineElement.querySelector('[data-item-product]');
        const quantityInput = lineElement.querySelector('[data-item-quantity]');
        const unitPriceInput = lineElement.querySelector('[data-item-unit-price]');
        const removeButton = lineElement.querySelector('[data-remove-item]');

        if (productSelect) {
            productSelect.addEventListener('change', () => {
                updateLinePricingDetails(lineElement);
                updateTotals();
            });
        }

        if (quantityInput) {
            quantityInput.addEventListener('input', () => {
                const parsed = Number.parseInt(quantityInput.value, 10);
                if (Number.isNaN(parsed) || parsed < 1) {
                    quantityInput.value = '1';
                }
                updateTotals();
            });
        }

        if (unitPriceInput) {
            unitPriceInput.addEventListener('input', () => {
                const parsed = Number.parseFloat(unitPriceInput.value);
                if (Number.isNaN(parsed) || parsed < 0) {
                    unitPriceInput.value = parsed < 0 ? '0' : '';
                }
                updateTotals();
            });
        }

        if (removeButton) {
            removeButton.addEventListener('click', () => {
                lineElement.remove();
                updateTotals();
                updateEmptyState();
            });
        }
    };

    const addLine = (item = null) => {
        if (!template || !itemsContainer) {
            return;
        }

        const fragment = template.content.cloneNode(true);
        const lineElement = fragment.querySelector('[data-order-item]');
        if (!lineElement) {
            return;
        }

        const index = lineIndex++;
        assignInputNames(lineElement, index);

        const productSelect = lineElement.querySelector('[data-item-product]');
        const quantityInput = lineElement.querySelector('[data-item-quantity]');
        const unitPriceInput = lineElement.querySelector('[data-item-unit-price]');

        const shopId = shopSelect?.value ?? null;
        if (productSelect) {
            populateProductOptions(productSelect, shopId, item?.product_id ?? '');
        }

        if (quantityInput && item?.quantity) {
            quantityInput.value = item.quantity;
        }

        if (unitPriceInput && item?.unit_price !== undefined) {
            unitPriceInput.value = item.unit_price;
        }

        itemsContainer.appendChild(lineElement);
        bindLineEvents(lineElement);
        updateLinePricingDetails(lineElement);
        updateTotals();
        updateEmptyState();
    };

    const resetLines = () => {
        if (itemsContainer) {
            itemsContainer.innerHTML = '';
        }
        lineIndex = 0;
        updateTotals();
        updateEmptyState();
    };

    const updateAddButtonState = () => {
        if (!addButton || !shopSelect) {
            return;
        }

        const products = getProductsForCurrentShop();
        const isDisabled = !shopSelect.value || products.length === 0;
        addButton.disabled = isDisabled;
        updateEmptyState();
    };

    if (addButton) {
        addButton.addEventListener('click', () => {
            addLine();
        });
    }

    if (shopSelect) {
        shopSelect.addEventListener('change', () => {
            resetLines();
            updateAddButtonState();

            if (!addButton?.disabled) {
                addLine();
            }
        });
    }

    const initialShop = form.dataset.initialShop ?? '';
    if (shopSelect && initialShop) {
        shopSelect.value = initialShop;
    }

    updateAddButtonState();

    if (Array.isArray(oldItems) && oldItems.length > 0) {
        oldItems.forEach((item) => addLine(item));
    } else if (!addButton?.disabled) {
        addLine();
    } else {
        updateEmptyState();
    }
};

document.addEventListener('DOMContentLoaded', initializeOrderForm);
