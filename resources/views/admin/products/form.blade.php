@extends('admin.layouts.admin')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle = $isEdit ? __('cms.products.title_edit') : __('cms.products.title_create');
    $pageDescription = $isEdit
        ? __('cms.products.edit_description')
        : __('cms.products.create_description');
    $formAction = $isEdit ? route('admin.products.update', $product) : route('admin.products.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';
    $defaultVariant = [
        'id' => null,
        'name' => '',
        'price' => '',
        'discount_price' => '',
        'stock' => '',
        'SKU' => '',
        'barcode' => '',
        'weight' => '',
        'dimensions' => '',
        'size_id' => null,
        'color_id' => null,
    ];
    $variantFormData = old('variants');
    if (! is_array($variantFormData)) {
        if ($isEdit && $product->variants->count()) {
            $variantFormData = $product->variants->map(function ($variant) use ($defaultVariant) {
                return array_merge($defaultVariant, [
                    'id' => $variant->id,
                    'name' => $variant->translations->firstWhere('language_code', app()->getLocale())?->name
                        ?? $variant->translations->first()?->name
                        ?? '',
                    'price' => $variant->price,
                    'discount_price' => $variant->discount_price,
                    'stock' => $variant->stock,
                    'SKU' => $variant->SKU,
                    'barcode' => $variant->barcode,
                    'weight' => $variant->weight,
                    'dimensions' => $variant->dimensions,
                    'size_id' => $variant->size_id ?? null,
                    'color_id' => $variant->color_id ?? null,
                ]);
            })->values()->toArray();
        } else {
            $variantFormData = [$defaultVariant];
        }
    } else {
        $variantFormData = array_values($variantFormData);
    }
    if (empty($variantFormData)) {
        $variantFormData = [$defaultVariant];
    }

    $existingImages = $isEdit ? $product->images : collect();
    $existingImagesData = $existingImages->map(function ($image) {
        $path = $image->image_url;
        if (! $path) {
            return ['id' => $image->id, 'url' => null];
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $url = $path;
        } elseif (Str::startsWith($path, ['assets/', 'images/', 'storage/'])) {
            $url = asset($path);
        } elseif (Storage::disk('public')->exists($path)) {
            $url = Storage::url($path);
        } else {
            $url = asset('storage/' . ltrim($path, '/'));
        }

        return [
            'id' => $image->id,
            'url' => $url,
        ];
    })->filter(fn ($image) => $image['url'])->values()->toArray();

    $metrics = $productMetrics ?? null;
    $timezone = config('app.timezone', 'UTC');
    $isActiveStatus = false;
    $statusLabel = __('cms.products.status_inactive');
    $statusBadgeClass = 'badge badge-danger';
    $categoryName = __('cms.products.no_category');
    $brandName = __('cms.products.no_brand');
    $vendorName = null;
    $lastSoldAt = null;
    $createdAtFormatted = null;
    $updatedAtFormatted = null;

    $formatDateTime = static function ($value) use ($timezone) {
        return $value ? $value->timezone($timezone)->format('M j, Y H:i') : null;
    };

    if ($isEdit) {
        $statusValue = $product->status;
        $isActiveStatus = in_array($statusValue, [1, '1', true, 'active'], true);
        $statusLabel = $isActiveStatus ? __('cms.products.status_active') : __('cms.products.status_inactive');
        $statusBadgeClass = $isActiveStatus ? 'badge badge-success' : 'badge badge-danger';
        $categoryName = optional($product->category?->translation)->name ?? __('cms.products.no_category');
        $brandName = optional($product->brand?->translation)->name ?? __('cms.products.no_brand');
        $vendorName = $product->vendor?->name;
        $lastSoldAt = $metrics['last_sold_at'] ?? null;
        $createdAtFormatted = $formatDateTime($product->created_at);
        $updatedAtFormatted = $formatDateTime($product->updated_at);
        $lastSoldAt = $lastSoldAt ? $formatDateTime($lastSoldAt) : null;
    }

    $translationChecklist = [];
    $shouldShowTranslationOverview = $isEdit;
    $activeLanguageTab = old('active_language_tab');
    if ($languages->count()) {
        $fieldLabels = [
            'name' => __('cms.products.product_name'),
            'description' => __('cms.products.description'),
            'short_description' => __('cms.products.short_description'),
        ];

        foreach ($languages as $language) {
            $langCode = $language->code;
            $oldTranslation = old("translations.$langCode", []);
            $existingTranslation = $product->translations->firstWhere('language_code', $langCode);

            $values = [
                'name' => $oldTranslation['name'] ?? $existingTranslation?->name,
                'description' => $oldTranslation['description'] ?? $existingTranslation?->description,
                'short_description' => $oldTranslation['short_description'] ?? $existingTranslation?->short_description,
            ];

            $missing = collect($values)
                ->filter(fn ($value) => blank($value))
                ->keys()
                ->map(fn ($key) => $fieldLabels[$key] ?? Str::headline($key))
                ->values()
                ->all();

            $hasContent = collect($values)->filter(fn ($value) => filled($value))->isNotEmpty();

            if ($hasContent || ! empty($oldTranslation)) {
                $shouldShowTranslationOverview = true;
            }

            $translationChecklist[$langCode] = [
                'language_name' => ucwords($language->name ?? $langCode),
                'missing' => $missing,
                'complete' => empty($missing),
            ];

            if (! $activeLanguageTab && $errors->has("translations.$langCode.*")) {
                $activeLanguageTab = $langCode;
            }
        }
    }

    if (! $activeLanguageTab || ! $languages->firstWhere('code', $activeLanguageTab)) {
        $activeLanguageTab = $languages->first()->code ?? 'en';
    }
@endphp

@section('content')
<x-admin.page-header :title="$pageTitle" :description="$pageDescription">
    <x-admin.button-link href="{{ route('admin.products.index') }}" class="btn-outline">
        {{ __('cms.products.back_to_index') }}
    </x-admin.button-link>
</x-admin.page-header>

@if ($isEdit && $metrics)
    <x-admin.card :title="__('cms.products.insights_title')">
        <p class="text-sm text-gray-500 mb-4">{{ __('cms.products.insights_subtitle') }}</p>
        <div class="grid gap-4 lg:grid-cols-3">
            <section class="border border-gray-200 rounded-lg bg-gray-50 p-4 h-full">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('cms.products.summary_section_profile') }}</p>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_product_id') }}</dt>
                        <dd class="font-semibold text-gray-900">#{{ $product->id }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_slug') }}</dt>
                        <dd class="font-medium text-gray-900 truncate" title="{{ $product->slug ?? '—' }}">{{ $product->slug ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 items-center">
                        <dt class="text-gray-500">{{ __('cms.products.summary_status') }}</dt>
                        <dd><span class="{{ $statusBadgeClass }}">{{ $statusLabel }}</span></dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_category') }}</dt>
                        <dd class="font-medium text-gray-900 truncate" title="{{ $categoryName }}">{{ $categoryName }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_brand') }}</dt>
                        <dd class="font-medium text-gray-900 truncate" title="{{ $brandName }}">{{ $brandName }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_vendor') }}</dt>
                        <dd class="font-medium text-gray-900 truncate" title="{{ $vendorName ?? __('cms.products.no_vendor_assigned') }}">
                            {{ $vendorName ?? __('cms.products.no_vendor_assigned') }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_created_at') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $createdAtFormatted ?? __('cms.products.summary_not_available') }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_updated_at') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $updatedAtFormatted ?? __('cms.products.summary_not_available') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="border border-gray-200 rounded-lg bg-gray-50 p-4 h-full">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('cms.products.summary_section_performance') }}</p>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_total_sales') }}</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($metrics['total_sales']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_total_revenue') }}</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($metrics['total_revenue'], 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_average_rating') }}</dt>
                        <dd class="font-semibold text-gray-900">
                            @if ($metrics['average_rating'] !== null)
                                {{ number_format($metrics['average_rating'], 2) }}
                            @else
                                {{ __('cms.products.summary_no_data') }}
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_review_count') }}</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($metrics['review_count']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_last_sale') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $lastSoldAt ?? __('cms.products.summary_no_data') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="border border-gray-200 rounded-lg bg-gray-50 p-4 h-full">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('cms.products.summary_section_inventory') }}</p>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_total_variants') }}</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($metrics['total_variants']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_total_stock') }}</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($metrics['total_stock']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">{{ __('cms.products.summary_low_stock', ['threshold' => $metrics['low_stock_threshold']]) }}</dt>
                        <dd class="font-semibold text-gray-900">{{ number_format($metrics['low_stock_count']) }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </x-admin.card>
@endif

<form x-data="productForm({
        activeTab: '{{ $activeLanguageTab }}',
        variantIndex: {{ count($variantFormData) }},
        existingImages: @json($existingImagesData)
    })"
      action="{{ $formAction }}"
      method="POST"
      enctype="multipart/form-data"
      class="space-y-6">
    @csrf
    @if ($isEdit)
        @method($formMethod)
    @endif

    <input type="hidden" name="active_language_tab" x-model="activeTab">

    @if ($shouldShowTranslationOverview && ! empty($translationChecklist))
        <x-admin.card :title="__('cms.products.translation_overview_title')">
            <p class="text-sm text-gray-500 mb-4">{{ __('cms.products.translation_overview_description') }}</p>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($translationChecklist as $langCode => $status)
                    <section class="border border-gray-100 rounded-lg bg-gray-50 p-4 h-full">
                        <header class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 uppercase">{{ $langCode }}</p>
                                <p class="text-xs text-gray-500">{{ $status['language_name'] }}</p>
                            </div>
                            <span class="badge {{ $status['complete'] ? 'badge-success' : 'badge-warning' }}">
                                {{ $status['complete'] ? __('cms.products.translation_status_complete') : __('cms.products.translation_status_missing') }}
                            </span>
                        </header>
                        @if ($status['complete'])
                            <p class="text-xs text-success-700 mt-3">{{ __('cms.products.translation_status_all_good') }}</p>
                        @else
                            <div class="mt-3">
                                <p class="text-xs font-semibold text-gray-600">{{ __('cms.products.translation_status_missing_fields') }}:</p>
                                <ul class="mt-2 space-y-1 text-xs text-gray-600 list-disc list-inside">
                                    @foreach ($status['missing'] as $missingLabel)
                                        <li>{{ $missingLabel }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        </x-admin.card>
    @endif

    <x-admin.card
        :title="__('cms.products.section_information_title')"
        :actions="view('admin.products.partials.language-tabs', [
            'languages' => $languages,
            'activeLanguageTab' => $activeLanguageTab,
            'translationChecklist' => $translationChecklist,
            'errors' => $errors,
        ])"
    >
        <div class="space-y-6">
            @foreach ($languages as $language)
                @php
                    $langCode = $language->code;
                @endphp
                <div x-show="activeTab === '{{ $langCode }}'" class="space-y-4" x-cloak>
                    <div>
                        <label class="form-label">{{ __('cms.products.product_name') }} ({{ $langCode }})</label>
                        <input
                            type="text"
                            name="translations[{{ $langCode }}][name]"
                            class="form-control"
                            value="{{ old("translations.$langCode.name", $product->getTranslation('name', $langCode)) }}"
                            required>
                        @error("translations.$langCode.name")
                            <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('cms.products.description') }} ({{ $langCode }})</label>
                        <textarea
                            name="translations[{{ $langCode }}][description]"
                            class="form-control ck-editor"
                            rows="5">{{ old("translations.$langCode.description", $product->getTranslation('description', $langCode)) }}</textarea>
                        @error("translations.$langCode.description")
                            <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('cms.products.short_description') }} ({{ $langCode }})</label>
                        <textarea
                            name="translations[{{ $langCode }}][short_description]"
                            class="form-control"
                            rows="3"
                            placeholder="{{ __('cms.products.short_description_placeholder') }}">{{ old("translations.$langCode.short_description", $product->getTranslation('short_description', $langCode)) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">{{ __('cms.products.short_description_helper') }}</p>
                        @error("translations.$langCode.short_description")
                            <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('cms.products.tags') }} ({{ $langCode }})</label>
                        <input
                            type="text"
                            name="translations[{{ $langCode }}][tags]"
                            class="form-control"
                            value="{{ old("translations.$langCode.tags", $product->getTranslation('tags', $langCode)) }}"
                            placeholder="{{ __('cms.products.tags_placeholder') }}">
                        <p class="text-xs text-gray-500 mt-1">{{ __('cms.products.tags_helper') }}</p>
                        @error("translations.$langCode.tags")
                            <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>
    </x-admin.card>

    <x-admin.card :title="__('cms.products.section_details_title')">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="form-label">{{ __('cms.products.category') }}</label>
                <select name="category_id" class="form-select">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                            {{ $category->translation->name ?? __('cms.products.no_category') }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="form-label">{{ __('cms.products.brand') }}</label>
                <select name="brand_id" class="form-select">
                    <option value="">{{ __('cms.products.no_brand') }}</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>
                            {{ $brand->translation->name ?? __('cms.products.no_brand_assigned') }}
                        </option>
                    @endforeach
                </select>
                @error('brand_id')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="form-label">{{ __('cms.products.vendor') }}</label>
                <select name="vendor_id" class="form-select">
                    <option value="">{{ __('cms.products.select_vendor') }}</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected(old('vendor_id', $product->vendor_id) == $vendor->id)>
                            {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
                @error('vendor_id')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </x-admin.card>

    <x-admin.card :title="__('cms.products.section_variants_title')">
        <div class="flex justify-end mb-4">
            <button type="button" class="btn btn-outline" @click="addVariant">
                {{ __('cms.products.add_variant') }}
            </button>
        </div>
        @error('variants')
            <p class="text-danger-600 text-sm mb-3">{{ $message }}</p>
        @enderror
        <div id="variants-container" class="space-y-4">
            @foreach ($variantFormData as $index => $variant)
                @include('admin.products.partials.variant-block', [
                    'index' => $index,
                    'displayIndex' => $loop->iteration,
                    'variant' => $variant,
                    'sizes' => $sizes,
                    'colors' => $colors,
                ])
            @endforeach
        </div>
        <template id="variant-template">
            @include('admin.products.partials.variant-block', [
                'index' => '__INDEX__',
                'displayIndex' => '__DISPLAY__',
                'variant' => $defaultVariant,
                'sizes' => $sizes,
                'colors' => $colors,
                'isTemplate' => true,
            ])
        </template>
    </x-admin.card>

    <x-admin.card :title="__('cms.products.section_images_title')">
        <div class="mb-6">
            <label class="form-label">{{ __('cms.products.images') }}</label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-300 transition-colors">
                <div class="space-y-2">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="text-sm text-gray-600">
                        <label for="productImages" class="cursor-pointer text-primary-600 hover:text-primary-500 font-medium">
                            {{ __('cms.products.choose_file') }}
                        </label>
                        <span class="text-gray-400">{{ __('cms.products.or_drag_drop') }}</span>
                        <input id="productImages" name="images[]" type="file" class="hidden" multiple accept="image/*" @change="previewImages">
                    </div>
                    <p class="text-xs text-gray-500">{{ __('cms.products.image_upload_hint') }}</p>
                </div>
            </div>
            @error('images.*')
                <p class="text-danger-600 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="newImages.length" class="mb-6" x-cloak>
            <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('cms.products.new_images') }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <template x-for="(image, index) in newImages" :key="index">
                    <div class="relative group">
                        <img :src="image.preview" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                        <button type="button" @click="removeNewImage(index)" class="absolute -top-2 -right-2 bg-danger-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">×</button>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="existingImages.length" x-cloak>
            <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('cms.products.current_images') }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <template x-for="image in existingImages" :key="image.id">
                    <div class="relative group" :id="`existing-image-${image.id}`">
                        <img :src="image.url" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                        <div class="absolute inset-0 bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                            <button type="button" @click="removeExistingImage(image.id)" class="bg-danger-500 text-white px-3 py-1 rounded text-sm">
                                {{ __('cms.products.remove') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </x-admin.card>

    <div class="flex justify-end gap-3">
        <x-admin.button-link href="{{ route('admin.products.index') }}" class="btn-outline">
            {{ __('cms.products.cancel') }}
        </x-admin.button-link>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? __('cms.products.update_product') : __('cms.products.save_product') }}
        </button>
    </div>
</form>
@endsection

@once
    @push('scripts')
        <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js" data-ckeditor-script></script>
    @endpush
@endonce

@push('scripts')
<script>
    function productForm(config) {
        return {
            activeTab: config.activeTab,
            variantIndex: config.variantIndex,
            newImages: [],
            existingImages: config.existingImages,
            addVariant() {
                const container = document.getElementById('variants-container');
                const currentBlocks = container.querySelectorAll('[data-variant-block]').length;
                const template = document.getElementById('variant-template').innerHTML
                    .replace(/__INDEX__/g, this.variantIndex)
                    .replace(/__DISPLAY__/g, currentBlocks + 1);
                container.insertAdjacentHTML('beforeend', template);
                this.variantIndex++;
            },
            removeVariant(index) {
                const element = document.getElementById(`variant-${index}`);
                if (element) {
                    element.remove();
                }
            },
            previewImages(event) {
                const files = Array.from(event.target.files || []);
                this.newImages = [];
                files.forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            this.newImages.push({ preview: e.target.result });
                        };
                        reader.readAsDataURL(file);
                    }
                });
            },
            removeNewImage(index) {
                this.newImages.splice(index, 1);
            },
            removeExistingImage(id) {
                const imageElement = document.getElementById(`existing-image-${id}`);
                if (imageElement) {
                    imageElement.remove();
                }
                this.existingImages = this.existingImages.filter(image => image.id !== id);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_images[]';
                input.value = id;
                document.querySelector('form').appendChild(input);
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const initializeEditors = () => {
            if (typeof ClassicEditor === 'undefined') {
                return;
            }
            document.querySelectorAll('.ck-editor').forEach((element) => {
                if (! element.classList.contains('ck-editor-initialized')) {
                    ClassicEditor.create(element)
                        .then(() => element.classList.add('ck-editor-initialized'))
                        .catch(error => console.error('CKEditor error:', error));
                }
            });
        };

        if (typeof ClassicEditor !== 'undefined') {
            initializeEditors();
        } else {
            const script = document.querySelector('script[data-ckeditor-script]');
            if (script) {
                script.addEventListener('load', initializeEditors, { once: true });
            }
        }
    });
</script>
@endpush
