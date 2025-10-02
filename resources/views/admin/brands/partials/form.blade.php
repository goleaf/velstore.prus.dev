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
@endphp

<div class="card mt-4" data-brand-form>
    <div class="card-header card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.brands.heading') }}</h6>
    </div>

    <div class="card-body">
        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="brandLanguageTabs" role="tablist">
                        @foreach ($activeLanguages as $language)
                            @php
                                $tabId = Str::slug($language->code);
                            @endphp
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="brand-tab-{{ $tabId }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#brand-tab-pane-{{ $tabId }}"
                                    type="button"
                                    role="tab"
                                >
                                    {{ ucwords($language->name) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="tab-content mt-3" id="brandLanguageTabContent">
                    @foreach ($activeLanguages as $language)
                        @php
                            $tabId = Str::slug($language->code);
                            $translation = $brand?->translations?->firstWhere('locale', $language->code);
                        @endphp
                        <div
                            class="tab-pane fade show {{ $loop->first ? 'active' : '' }}"
                            id="brand-tab-pane-{{ $tabId }}"
                            role="tabpanel"
                            aria-labelledby="brand-tab-{{ $tabId }}"
                        >
                            <label class="form-label" for="brand-name-{{ $tabId }}">
                                {{ __('cms.brands.name') }} ({{ $language->code }})
                            </label>
                            <input
                                type="text"
                                id="brand-name-{{ $tabId }}"
                                name="translations[{{ $language->code }}][name]"
                                class="form-control @error("translations.{$language->code}.name") is-invalid @enderror"
                                value="{{ old("translations.{$language->code}.name", $translation->name ?? '') }}"
                            >
                            @error("translations.{$language->code}.name")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label class="form-label mt-3" for="brand-description-{{ $tabId }}">
                                {{ __('cms.brands.description') }} ({{ $language->code }})
                            </label>
                            <textarea
                                id="brand-description-{{ $tabId }}"
                                name="translations[{{ $language->code }}][description]"
                                class="form-control ck-editor-multi-languages @error("translations.{$language->code}.description") is-invalid @enderror"
                            >{{ old("translations.{$language->code}.description", $translation->description ?? '') }}</textarea>
                            @error("translations.{$language->code}.description")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="col-md-6 mt-3">
                    <div class="form-group">
                        <label for="brandLogoFile">{{ __('cms.brands.logo') }}</label>
                        <div class="custom-file">
                            <label class="btn btn-primary" for="brandLogoFile">{{ __('cms.brands.choose_file') }}</label>
                            <input
                                type="file"
                                name="logo_url"
                                accept="image/*"
                                class="form-control d-none"
                                id="brandLogoFile"
                            >
                        </div>
                        <div class="mt-2 {{ $logoPreviewUrl ? '' : 'd-none' }}" id="brandLogoPreview">
                            <img
                                id="brandLogoPreviewImg"
                                src="{{ $logoPreviewUrl ?? '' }}"
                                alt="{{ __('cms.brands.logo') }}"
                                class="img-thumbnail"
                                width="100"
                            >
                        </div>
                        @error('logo_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="mt-3 btn btn-primary">{{ $submitLabel }}</button>
        </form>
    </div>
</div>
