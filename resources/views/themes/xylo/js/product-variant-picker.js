document.addEventListener('DOMContentLoaded', () => {
    const picker = document.getElementById('product-variant-picker');

    if (!picker) {
        return;
    }

    const productId = parseInt(picker.dataset.productId, 10);
    const productType = picker.dataset.productType;
    const cartUrl = picker.dataset.cartUrl;
    const csrfToken = picker.dataset.csrfToken;
    const variantPrice = document.getElementById('variant-price');
    const productStock = document.getElementById('product-stock');
    const currencySymbol = document.getElementById('currency-symbol');

    let variantMap = [];

    try {
        variantMap = JSON.parse(picker.dataset.variantMap || '[]');
    } catch (error) {
        console.error('Unable to parse variant map data.', error);
    }

    const qtyInput = picker.querySelector('#qty');
    const quantityButtons = picker.querySelectorAll('.quantity-btn');
    const addToCartButton = picker.querySelector('.add-to-cart');

    function getSelectedAttributeValueIds() {
        return Array.from(picker.querySelectorAll('#product-attributes input[type="radio"]:checked'))
            .map((input) => parseInt(input.value, 10))
            .filter((value) => !Number.isNaN(value))
            .sort((a, b) => a - b);
    }

    function findMatchingVariantId(selectedAttrIds) {
        for (const variant of variantMap) {
            const variantAttrIds = variant.attributes.slice().sort((a, b) => a - b);
            if (JSON.stringify(variantAttrIds) === JSON.stringify(selectedAttrIds)) {
                return variant.id;
            }
        }
        return null;
    }

    function updateVariantPricing(variantId) {
        if (!variantId) {
            return;
        }

        const params = new URLSearchParams({
            variant_id: variantId,
            product_id: productId,
        });

        fetch(`/get-variant-price?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    console.log('Unable to fetch variant price.');
                    return;
                }

                if (variantPrice) {
                    variantPrice.textContent = data.price;
                }

                if (productStock) {
                    productStock.textContent = data.stock;
                    if (data.is_out_of_stock) {
                        productStock.classList.add('text-danger');
                    } else {
                        productStock.classList.remove('text-danger');
                    }
                }

                if (currencySymbol) {
                    currencySymbol.textContent = data.currency_symbol;
                }
            })
            .catch(() => {
                alert('Something went wrong. Please try again.');
            });
    }

    function updateCartCount(cart) {
        const totalCount = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
        const cartCount = document.getElementById('cart-count');

        if (cartCount) {
            cartCount.textContent = totalCount;
        }
    }

    function handleAddToCart() {
        const quantity = parseInt(qtyInput?.value, 10) || 1;
        const selectedAttributes = getSelectedAttributeValueIds();

        fetch(cartUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity,
                attribute_value_ids: selectedAttributes,
                product_type: productType,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (typeof toastr !== 'undefined') {
                    if (data.success === false && data.message) {
                        toastr.error(data.message);
                    } else if (data.message) {
                        toastr.success(data.message);
                    }
                }

                if (data.cart) {
                    updateCartCount(data.cart);
                }
            })
            .catch((error) => console.error('Error:', error));
    }

    function changeQty(amount) {
        const currentQty = parseInt(qtyInput?.value, 10) || 1;
        let newQty = currentQty + amount;

        if (newQty < 1) {
            newQty = 1;
        }

        if (qtyInput) {
            qtyInput.value = String(newQty);
        }
    }

    function handleAttributeChange() {
        const selectedAttrIds = getSelectedAttributeValueIds();
        const variantId = findMatchingVariantId(selectedAttrIds);

        if (!variantId) {
            alert('Selected variant not available.');
            return;
        }

        updateVariantPricing(variantId);
    }

    quantityButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            const amount = parseInt(event.currentTarget.dataset.change, 10);
            if (!Number.isNaN(amount)) {
                changeQty(amount);
            }
        });
    });

    if (addToCartButton) {
        addToCartButton.addEventListener('click', handleAddToCart);
    }

    picker.querySelectorAll('#product-attributes input[type="radio"]').forEach((input) => {
        input.addEventListener('change', handleAttributeChange);
    });

    handleAttributeChange();
});

