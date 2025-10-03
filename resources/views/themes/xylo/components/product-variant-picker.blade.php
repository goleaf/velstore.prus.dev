@props([
    'product',
    'variantMap' => [],
    'inStock' => false,
])

<div
    id="product-variant-picker"
    data-product-id="{{ $product->id }}"
    data-product-type="{{ $product->product_type }}"
    data-cart-url="{{ route('cart.add') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-variant-map='@json($variantMap)'
    data-in-stock="{{ $inStock ? 'true' : 'false' }}"
>
    <div id="product-attributes" class="product-options">
        @php
            $groupedAttributes = $product->attributeValues->groupBy(fn($item) => $item->attribute->id);
        @endphp

        @foreach ($groupedAttributes as $attributeId => $values)
            <div class="attribute-options mt-3">
                <h3>{{ $values->first()->attribute->name }}</h3>
                <div class="{{ strtolower($values->first()->attribute->name) }}-wrapper">
                    @foreach ($values as $index => $value)
                        @php
                            $inputId = strtolower($values->first()->attribute->name) . '-' . $index;
                        @endphp
                        <input
                            type="radio"
                            name="attribute_{{ $attributeId }}"
                            id="{{ $inputId }}"
                            value="{{ $value->id }}"
                            {{ $index === 0 ? 'checked' : '' }}
                        >
                        <label
                            for="{{ $inputId }}"
                            class="{{ strtolower($values->first()->attribute->name) === 'color' ? 'color-circle ' . strtolower($value->translated_value) : 'size-box' }}"
                        >
                            @if (strtolower($values->first()->attribute->name) === 'size')
                                {{ $value->translated_value }}
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="cart-actions mt-3 d-flex">
        <div class="quantity me-4">
            <button type="button" class="quantity-btn" data-change="-1">-</button>
            <input type="text" id="qty" value="1">
            <button type="button" class="quantity-btn" data-change="1">+</button>
        </div>
        <button type="button" class="add-to-cart read-more">
            {{ __('store.product_detail.add_to_cart') }}
        </button>
    </div>
</div>

