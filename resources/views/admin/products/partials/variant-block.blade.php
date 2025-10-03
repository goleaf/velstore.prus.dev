@php
    $variant = $variant ?? [];
    $indexKey = $index;
    $displayLabel = isset($displayIndex) && $displayIndex !== null && $displayIndex !== '__DISPLAY__'
        ? __('cms.products.variant_label', ['number' => $displayIndex])
        : __('cms.products.variant_label', ['number' => '__DISPLAY__']);
    $sizeSelected = $variant['size_id'] ?? null;
    $colorSelected = $variant['color_id'] ?? null;
    $field = fn(string $name) => "variants[{$indexKey}][{$name}]";
    $showErrors = empty($isTemplate ?? false);
@endphp

<div class="border border-gray-200 rounded-lg p-5 bg-gray-50" id="variant-{{ $index }}" data-variant-block>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-800">{{ $displayLabel }}</h3>
        <button type="button" class="text-danger-600 hover:text-danger-700" @click="removeVariant('{{ $index }}')">
            {{ __('cms.products.remove') }}
        </button>
    </div>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="form-label">{{ __('cms.products.variant_name_en') }}</label>
            <input type="text" name="{{ $field('name') }}" class="form-control" value="{{ old('variants.' . $index . '.name', $variant['name'] ?? '') }}" placeholder="Variant name">
            @if ($showErrors)
                @error('variants.' . $index . '.name')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.price') }}</label>
            <input type="number" step="0.01" name="{{ $field('price') }}" class="form-control" value="{{ old('variants.' . $index . '.price', $variant['price'] ?? '') }}" placeholder="0.00">
            @if ($showErrors)
                @error('variants.' . $index . '.price')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.discount_price') }}</label>
            <input type="number" step="0.01" name="{{ $field('discount_price') }}" class="form-control" value="{{ old('variants.' . $index . '.discount_price', $variant['discount_price'] ?? '') }}" placeholder="0.00">
            @if ($showErrors)
                @error('variants.' . $index . '.discount_price')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.stock') }}</label>
            <input type="number" name="{{ $field('stock') }}" class="form-control" value="{{ old('variants.' . $index . '.stock', $variant['stock'] ?? '') }}" placeholder="0">
            @if ($showErrors)
                @error('variants.' . $index . '.stock')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.sku') }}</label>
            <input type="text" name="{{ $field('SKU') }}" class="form-control" value="{{ old('variants.' . $index . '.SKU', $variant['SKU'] ?? '') }}" placeholder="SKU-001">
            @if ($showErrors)
                @error('variants.' . $index . '.SKU')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.barcode') }}</label>
            <input type="text" name="{{ $field('barcode') }}" class="form-control" value="{{ old('variants.' . $index . '.barcode', $variant['barcode'] ?? '') }}" placeholder="123456789">
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.weight') }}</label>
            <input type="number" step="0.01" name="{{ $field('weight') }}" class="form-control" value="{{ old('variants.' . $index . '.weight', $variant['weight'] ?? '') }}" placeholder="1.2">
            @if ($showErrors)
                @error('variants.' . $index . '.weight')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.dimension') }}</label>
            <input type="text" name="{{ $field('dimensions') }}" class="form-control" value="{{ old('variants.' . $index . '.dimensions', $variant['dimensions'] ?? '') }}" placeholder="10x20x5">
            @if ($showErrors)
                @error('variants.' . $index . '.dimensions')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.size') }}</label>
            <select name="{{ $field('size_id') }}" class="form-select">
                <option value="">{{ __('cms.products.select_size') }}</option>
                @foreach ($sizes as $size)
                    <option value="{{ $size->id }}" @selected(old('variants.' . $index . '.size_id', $sizeSelected) == $size->id)>
                        {{ $size->value }}
                    </option>
                @endforeach
            </select>
            @if ($showErrors)
                @error('variants.' . $index . '.size_id')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
        <div>
            <label class="form-label">{{ __('cms.products.color') }}</label>
            <select name="{{ $field('color_id') }}" class="form-select">
                <option value="">{{ __('cms.products.select_color') }}</option>
                @foreach ($colors as $color)
                    <option value="{{ $color->id }}" @selected(old('variants.' . $index . '.color_id', $colorSelected) == $color->id)>
                        {{ $color->value }}
                    </option>
                @endforeach
            </select>
            @if ($showErrors)
                @error('variants.' . $index . '.color_id')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            @endif
        </div>
    </div>
    @if (! empty($variant['id']))
        <input type="hidden" name="{{ $field('id') }}" value="{{ $variant['id'] }}">
    @endif
</div>
