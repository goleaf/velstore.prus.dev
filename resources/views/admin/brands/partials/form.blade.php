<?php use Illuminate\Support\Str; ?>
@php
    $isEdit = isset($brand) && $brand;
    $submitLabel = $submitLabel ?? ($isEdit ? __('cms.brands.update') : __('cms.brands.create'));
    $logoPreviewUrl = $logoPreviewUrl ?? null;
    $activeLanguages = isset($activeLanguages) ? collect($activeLanguages) : collect();

    if (! $logoPreviewUrl && $isEdit && $brand->logo_url) {
        $logoPreviewUrl = filter_var($brand->logo_url, FILTER_VALIDATE_URL)
            ? $brand->logo_url
            : asset('storage/' . ltrim($brand->logo_url, '/'));
    }

    $defaultActiveTab = old('active_tab');
    if (! $defaultActiveTab && $activeLanguages->count() > 0) {
        $defaultActiveTab = $activeLanguages->first()->code;
    }
@endphp

<x-admin.card data-brand-form :title="__('cms.brands.form_title')" class="mt-6">
    <form
        action="{{ $formAction }}"
        method="POST"
        enctype="multipart/form-data"
        novalidate
        class="space-y-8"
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <input type="hidden" name="active_tab" value="{{ $defaultActiveTab }}" id="brandActiveTabInput">

        @if ($errors->any())
            <div class="rounded-md border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700">
                <p class="font-semibold">{{ __('cms.notifications.validation_error') }}</p>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-6">
            <div class="border-b border-gray-200">
                <div class="nav-tabs" id="brandLanguageTabs" role="tablist">
                    @foreach ($activeLanguages as $language)
                        @php
                            $tabId = Str::slug($language->code);
                        @endphp
                        <button
                            type="button"
                            class="{{ $loop->first ? 'nav-tab nav-tab-active' : 'nav-tab nav-tab-inactive' }}"
                            id="brand-tab-{{ $tabId }}"
                            role="tab"
                            aria-controls="brand-tab-pane-{{ $tabId }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            data-tab-button
                            data-tab-target="brand-tab-pane-{{ $tabId }}"
                            data-tab-value="{{ $language->code }}"
                        >
                            {{ ucwords($language->name) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6" id="brandLanguageTabContent">
                @foreach ($activeLanguages as $language)
                    @php
                        $tabId = Str::slug($language->code);
                        $translation = $brand?->translations?->firstWhere('locale', $language->code);
                    @endphp
                    <section
                        id="brand-tab-pane-{{ $tabId }}"
                        class="space-y-5 {{ $loop->first ? '' : 'hidden' }}"
                        role="tabpanel"
                        aria-labelledby="brand-tab-{{ $tabId }}"
                        aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                        data-tab-panel
                    >
                        <div>
                            <label class="form-label" for="brand-name-{{ $tabId }}">
                                {{ __('cms.brands.name') }} ({{ strtoupper($language->code) }})
                            </label>
                            <input
                                type="text"
                                id="brand-name-{{ $tabId }}"
                                name="translations[{{ $language->code }}][name]"
                                @class([
                                    'form-control',
                                    'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has("translations.{$language->code}.name"),
                                ])
                                value="{{ old("translations.{$language->code}.name", $translation->name ?? '') }}"
                            >
                            @error("translations.{$language->code}.name")
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="brand-description-{{ $tabId }}">
                                {{ __('cms.brands.description') }} ({{ strtoupper($language->code) }})
                            </label>
                            <textarea
                                id="brand-description-{{ $tabId }}"
                                name="translations[{{ $language->code }}][description]"
                                class="form-control ck-editor-multi-languages @error("translations.{$language->code}.description") border-danger-300 focus:border-danger-500 focus:ring-danger-500 @enderror"
                            >{{ old("translations.{$language->code}.description", $translation->description ?? '') }}</textarea>
                            @error("translations.{$language->code}.description")
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <div class="space-y-4 max-w-xl">
                <label for="brandLogoFile" class="form-label">{{ __('cms.brands.logo') }}</label>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="btn btn-primary cursor-pointer" for="brandLogoFile">
                        {{ __('cms.brands.choose_file') }}
                    </label>
                    <input
                        type="file"
                        name="logo_url"
                        accept="image/*"
                        class="hidden"
                        id="brandLogoFile"
                    >
                </div>
                <div class="mt-3 {{ $logoPreviewUrl ? '' : 'hidden' }}" id="brandLogoPreview">
                    <img
                        id="brandLogoPreviewImg"
                        src="{{ $logoPreviewUrl ?? '' }}"
                        alt="{{ __('cms.brands.logo') }}"
                        class="h-24 w-24 rounded border border-gray-200 object-contain"
                    >
                </div>
                @error('logo_url')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
            <x-admin.button-link href="{{ route('admin.brands.index') }}" class="btn-outline">
                {{ __('cms.brands.massage_cancel') }}
            </x-admin.button-link>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </form>
</x-admin.card>
