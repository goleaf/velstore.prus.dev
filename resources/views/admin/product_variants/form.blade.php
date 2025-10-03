@php
    $isEdit = $isEdit ?? false;
    $productVariant = $productVariant ?? null;

    $pageTitle = $isEdit
        ? __('cms.product_variants.title_edit')
        : __('cms.product_variants.title_create');
    $pageDescription = $isEdit
        ? __('cms.product_variants.edit_description')
        : __('cms.product_variants.create_description');
    $formAction = $isEdit
        ? route('admin.product_variants.update', $productVariant->id)
        : route('admin.product_variants.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';

    $languageCodes = $languages->pluck('code')->filter()->values();
    $tabStorageKey = 'admin_product_variants_active_tab';

    $localeResolution = \App\Support\Admin\TranslationLocaleResolver::resolve(
        $languageCodes,
        $errors,
        session()->getOldInput(),
        app()->getLocale(),
        config('app.fallback_locale'),
        'en'
    );

    $initialTab = $localeResolution->initial();
@endphp

<x-admin.page-header :title="$pageTitle" :description="$pageDescription">
    <x-admin.button-link href="{{ route('admin.product_variants.index') }}" class="btn-outline">
        {{ __('cms.product_variants.back_to_index') }}
    </x-admin.button-link>
</x-admin.page-header>

@if (session('success'))
    <div class="alert alert-success mt-6">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger mt-6">
        <p class="font-semibold">{{ __('cms.product_variants.form_validation_error') }}</p>
        <ul class="mt-2 list-disc list-inside space-y-1 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-admin.card class="mt-6">
    <form action="{{ $formAction }}" method="POST" class="space-y-8" id="productVariantForm">
        @csrf
        @if ($isEdit)
            @method($formMethod)
        @endif

        <section>
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {{ __('cms.product_variants.form_section_details') }}
            </h2>
            <div class="mt-4 grid gap-6 md:grid-cols-2">
                <div>
                    <label for="product_id" class="form-label">{{ __('cms.product_variants.form_product') }}</label>
                    <select name="product_id" id="product_id" class="form-select" required>
                        <option value="">{{ __('cms.product_variants.form_select_product') }}</option>
                        @foreach ($products as $product)
                            @php
                                $productName = optional($product->translation)->name
                                    ?? $product->translations->first()?->name
                                    ?? __('cms.products.unnamed_product');
                            @endphp
                            <option value="{{ $product->id }}" @selected(old('product_id', $productVariant?->product_id ?? '') == $product->id)>
                                {{ $productName }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="variant_slug" class="form-label">{{ __('cms.product_variants.form_variant_slug') }}</label>
                    <input
                        type="text"
                        name="variant_slug"
                        id="variant_slug"
                        class="form-control"
                        value="{{ old('variant_slug', $productVariant?->variant_slug ?? '') }}"
                        required
                    >
                    @error('variant_slug')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="form-label">{{ __('cms.product_variants.form_internal_name') }}</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control"
                        value="{{ old('name', $productVariant?->name ?? '') }}"
                        required
                    >
                    @error('name')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="value" class="form-label">{{ __('cms.product_variants.form_value') }}</label>
                    <input
                        type="text"
                        name="value"
                        id="value"
                        class="form-control"
                        value="{{ old('value', $productVariant?->value ?? '') }}"
                        required
                    >
                    @error('value')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {{ __('cms.product_variants.form_section_inventory') }}
            </h2>
            <div class="mt-4 grid gap-6 md:grid-cols-2">
                <div>
                    <label for="price" class="form-label">{{ __('cms.product_variants.form_price') }}</label>
                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        id="price"
                        class="form-control"
                        value="{{ old('price', $productVariant?->price ?? '') }}"
                        required
                    >
                    @error('price')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_price" class="form-label">{{ __('cms.product_variants.form_discount_price') }}</label>
                    <input
                        type="number"
                        step="0.01"
                        name="discount_price"
                        id="discount_price"
                        class="form-control"
                        value="{{ old('discount_price', $productVariant?->discount_price ?? '') }}"
                    >
                    @error('discount_price')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock" class="form-label">{{ __('cms.product_variants.form_stock') }}</label>
                    <input
                        type="number"
                        name="stock"
                        id="stock"
                        class="form-control"
                        value="{{ old('stock', $productVariant?->stock ?? '') }}"
                        required
                    >
                    @error('stock')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="SKU" class="form-label">{{ __('cms.product_variants.form_sku') }}</label>
                    <input
                        type="text"
                        name="SKU"
                        id="SKU"
                        class="form-control"
                        value="{{ old('SKU', $productVariant?->SKU ?? '') }}"
                        required
                    >
                    @error('SKU')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="weight" class="form-label">{{ __('cms.product_variants.form_weight') }}</label>
                    <input
                        type="text"
                        name="weight"
                        id="weight"
                        class="form-control"
                        value="{{ old('weight', $productVariant?->weight ?? '') }}"
                    >
                    @error('weight')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="dimensions" class="form-label">{{ __('cms.product_variants.form_dimensions') }}</label>
                    <input
                        type="text"
                        name="dimensions"
                        id="dimensions"
                        class="form-control"
                        value="{{ old('dimensions', $productVariant?->dimensions ?? '') }}"
                    >
                    @error('dimensions')
                        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        {{ __('cms.product_variants.form_section_translations') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ __('cms.product_variants.form_section_translations_description') }}
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <ul class="nav nav-tabs" id="productVariantLanguageTabs" role="tablist">
                    @foreach ($languages as $language)
                        @php
                            $langCode = $language->code;
                            $languageName = ucwords($language->name ?? $langCode);
                            $hasTranslationErrors = $errors->has("translations.$langCode.name") || $errors->has("translations.$langCode.value");
                            $isActiveTab = $langCode === $initialTab;
                        @endphp
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $isActiveTab ? 'active' : '' }} {{ $hasTranslationErrors ? 'text-danger-600' : '' }}"
                                id="product-variant-{{ $langCode }}-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#product-variant-{{ $langCode }}"
                                data-language-code="{{ $langCode }}"
                                type="button"
                                role="tab"
                                aria-controls="product-variant-{{ $langCode }}"
                                aria-selected="{{ $isActiveTab ? 'true' : 'false' }}"
                            >
                                {{ $languageName }}
                                @if ($hasTranslationErrors)
                                    <span class="ms-1">*</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content mt-4" id="productVariantLanguageTabContent">
                    @foreach ($languages as $language)
                        @php
                            $langCode = $language->code;
                            $languageName = ucwords($language->name ?? $langCode);
                            $existingTranslation = $isEdit && $productVariant
                                ? $productVariant->translations->firstWhere('locale', $langCode)
                                : null;
                            $nameValue = old("translations.$langCode.name", $existingTranslation->name ?? '');
                            $valueValue = old("translations.$langCode.value", $existingTranslation->value ?? '');
                            $isActiveTab = $langCode === $initialTab;
                        @endphp
                        <div
                            class="tab-pane fade {{ $isActiveTab ? 'show active' : '' }} rounded-lg border border-gray-200 bg-secondary-50/40 p-4"
                            id="product-variant-{{ $langCode }}"
                            role="tabpanel"
                            aria-labelledby="product-variant-{{ $langCode }}-tab"
                        >
                            <div class="space-y-4">
                                <div>
                                    <label for="translations_{{ $langCode }}_name" class="form-label">
                                        {{ __('cms.product_variants.form_translation_name', ['language' => $languageName]) }}
                                    </label>
                                    <input
                                        type="text"
                                        name="translations[{{ $langCode }}][name]"
                                        id="translations_{{ $langCode }}_name"
                                        class="form-control @error('translations.' . $langCode . '.name') is-invalid @enderror"
                                        value="{{ $nameValue }}"
                                        required
                                    >
                                    @error('translations.' . $langCode . '.name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="translations_{{ $langCode }}_value" class="form-label">
                                        {{ __('cms.product_variants.form_translation_value', ['language' => $languageName]) }}
                                    </label>
                                    <input
                                        type="text"
                                        name="translations[{{ $langCode }}][value]"
                                        id="translations_{{ $langCode }}_value"
                                        class="form-control @error('translations.' . $langCode . '.value') is-invalid @enderror"
                                        value="{{ $valueValue }}"
                                        placeholder="{{ __('cms.product_variants.form_translation_placeholder') }}"
                                    >
                                    @error('translations.' . $langCode . '.value')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <input type="hidden" name="active_tab" id="active_tab" value="{{ $initialTab }}">

        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <x-admin.button-link href="{{ route('admin.product_variants.index') }}" class="btn-outline">
                {{ __('cms.product_variants.back_to_index') }}
            </x-admin.button-link>
            <button type="submit" class="btn btn-primary">
                {{ $isEdit ? __('cms.product_variants.update_button') : __('cms.product_variants.create_button') }}
            </button>
        </div>
    </form>
</x-admin.card>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('productVariantForm');
            if (!form || typeof bootstrap === 'undefined') {
                return;
            }

            const tabStorageKey = @json($tabStorageKey);
            const activeTabInput = document.getElementById('active_tab');

            const tabTriggers = Array.from(
                form.querySelectorAll('#productVariantLanguageTabs button[data-bs-toggle="tab"]')
            );

            if (!tabTriggers.length) {
                return;
            }

            const availableTabs = tabTriggers
                .map((trigger) => trigger.dataset.languageCode)
                .filter((code) => typeof code === 'string' && code.length > 0);

            if (!availableTabs.length) {
                return;
            }

            let pendingFocusElement = null;

            const rememberActiveTab = (code) => {
                if (activeTabInput) {
                    activeTabInput.value = code || '';
                }

                if (!code) {
                    return;
                }

                try {
                    window.localStorage?.setItem(tabStorageKey, code);
                } catch (error) {
                    // Ignore storage availability issues (e.g., privacy mode).
                }
            };

            const showTab = (code, { focusElement = null } = {}) => {
                if (!code || !availableTabs.includes(code)) {
                    return;
                }

                const trigger = tabTriggers.find((button) => button.dataset.languageCode === code);
                if (!trigger) {
                    return;
                }

                const focusTarget = focusElement instanceof Element && focusElement.closest(`#product-variant-${code}`)
                    ? focusElement
                    : null;

                pendingFocusElement = focusTarget;
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            };

            tabTriggers.forEach((trigger) => {
                trigger.addEventListener('shown.bs.tab', (event) => {
                    const code = event.target?.dataset?.languageCode;
                    if (!code) {
                        return;
                    }

                    rememberActiveTab(code);

                    if (pendingFocusElement && typeof pendingFocusElement.focus === 'function') {
                        const elementToFocus = pendingFocusElement;
                        pendingFocusElement = null;

                        window.requestAnimationFrame(() => {
                            elementToFocus.focus({ preventScroll: true });
                        });
                    } else {
                        pendingFocusElement = null;
                    }
                });
            });

            const findTabForElement = (element) => {
                if (!element) {
                    return null;
                }

                const pane = element.closest('.tab-pane[id^="product-variant-"]');
                if (!pane) {
                    return null;
                }

                return pane.id.replace('product-variant-', '');
            };

            const getStoredTab = () => {
                try {
                    const stored = window.localStorage?.getItem(tabStorageKey);
                    return stored && availableTabs.includes(stored) ? stored : null;
                } catch (error) {
                    return null;
                }
            };

            const firstInvalidElement = form.querySelector('.is-invalid');
            const invalidTab = findTabForElement(firstInvalidElement);

            const presetTab = activeTabInput?.value && availableTabs.includes(activeTabInput.value)
                ? activeTabInput.value
                : null;

            let initialTab = invalidTab || presetTab || getStoredTab() || availableTabs[0];

            if (!initialTab) {
                rememberActiveTab('');
                return;
            }

            rememberActiveTab(initialTab);

            const focusTarget = invalidTab && firstInvalidElement && findTabForElement(firstInvalidElement) === invalidTab
                ? firstInvalidElement
                : null;

            showTab(initialTab, { focusElement: focusTarget });
        });
    </script>
@endpush
