@php
    $defaultLocale = config('app.locale');
    $pageModel = $page ?? null;
    $statusValue = old('status', optional($pageModel)->status ?? 1);
    $slugValue = old('slug', optional($pageModel)->slug);
    $templateValue = old('template', optional($pageModel)->template ?? 'default');
    $showInNavigation = (bool) old('show_in_navigation', optional($pageModel)->show_in_navigation);
    $showInFooter = (bool) old('show_in_footer', optional($pageModel)->show_in_footer);
    $isFeatured = (bool) old('is_featured', optional($pageModel)->is_featured);
    $publishedAtValue = old('published_at', optional(optional($pageModel)->published_at)->format('Y-m-d\TH:i'));
@endphp

<form id="pageForm" action="{{ $action }}" method="POST" enctype="multipart/form-data" class="mt-4" novalidate>
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 mb-2">{{ __('cms.pages.form_section_overview') }}</h2>
                    <p class="text-muted small mb-4">{{ __('cms.pages.form_section_overview_help') }}</p>

                    <input type="hidden" name="status" id="page-status-input" value="{{ $statusValue ? 1 : 0 }}">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="page-status-toggle"
                               {{ $statusValue ? 'checked' : '' }}>
                        <label class="form-check-label" for="page-status-toggle">{{ __('cms.pages.form_status_label') }}</label>
                    </div>
                    <p class="text-muted small mt-2" id="page-status-help">
                        {{ $statusValue ? __('cms.pages.form_status_help_active') : __('cms.pages.form_status_help_inactive') }}
                    </p>

                    <div class="mt-4">
                        <label class="form-label fw-semibold" for="page-slug">{{ __('cms.pages.form_slug_label') }}</label>
                        <div class="input-group">
                            <span class="input-group-text text-muted">{{ rtrim(url('/'), '/') }}/</span>
                            <input type="text"
                                   id="page-slug"
                                   name="slug"
                                   value="{{ $slugValue }}"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   placeholder="{{ __('cms.pages.slug_preview_placeholder') }}">
                        </div>
                        @error('slug')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <button type="button" class="btn btn-link btn-sm px-0 mt-1" data-generate-slug>
                            {{ __('cms.pages.form_slug_generate') }}
                        </button>
                        <p class="text-muted small mb-0" data-slug-preview-help>
                            {{ __('cms.pages.slug_preview_hint') }}
                        </p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">{{ __('cms.pages.form_visibility_label') }}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="show-in-navigation"
                                   name="show_in_navigation" {{ $showInNavigation ? 'checked' : '' }}>
                            <label class="form-check-label" for="show-in-navigation">
                                {{ __('cms.pages.form_visibility_navigation') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="show-in-footer"
                                   name="show_in_footer" {{ $showInFooter ? 'checked' : '' }}>
                            <label class="form-check-label" for="show-in-footer">
                                {{ __('cms.pages.form_visibility_footer') }}
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="is-featured"
                                   name="is_featured" {{ $isFeatured ? 'checked' : '' }}>
                            <label class="form-check-label" for="is-featured">
                                {{ __('cms.pages.form_visibility_featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('cms.pages.form_template_label') }}</label>
                        <div class="btn-group w-100" role="group" aria-label="{{ __('cms.pages.form_template_label') }}">
                            <input type="radio" class="btn-check" name="template" id="template-default" value="default"
                                   {{ $templateValue === 'default' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="template-default">
                                {{ __('cms.pages.form_template_default') }}
                            </label>

                            <input type="radio" class="btn-check" name="template" id="template-hero" value="with-hero"
                                   {{ $templateValue === 'with-hero' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="template-hero">
                                {{ __('cms.pages.form_template_hero') }}
                            </label>
                        </div>
                        @error('template')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="published-at">{{ __('cms.pages.form_publish_at_label') }}</label>
                        <input type="datetime-local"
                               id="published-at"
                               name="published_at"
                               value="{{ $publishedAtValue }}"
                               class="form-control @error('published_at') is-invalid @enderror">
                        @error('published_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <p class="text-muted small mb-0 mt-2">{{ __('cms.pages.form_publish_at_help') }}</p>
                    </div>

                    <div class="bg-light rounded-3 p-3 mt-4">
                        <h3 class="h6 mb-2">{{ __('cms.pages.form_tips_title') }}</h3>
                        <ul class="small text-muted mb-0">
                            <li>{{ __('cms.pages.form_tip_primary_language', ['code' => strtoupper($defaultLocale)]) }}</li>
                            <li>{{ __('cms.pages.form_tip_media') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-2">{{ __('cms.pages.form_section_translations') }}</h2>
                    <p class="text-muted small mb-4">{{ __('cms.pages.form_section_translations_help') }}</p>

                    <ul class="nav nav-tabs" role="tablist">
                        @foreach($activeLanguages as $language)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                        data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $language->code }}"
                                        type="button">
                                    {{ ucwords($language->name) }}
                                    @if($language->code === $defaultLocale)
                                        <span class="badge bg-primary-subtle text-primary ms-1">
                                            {{ __('cms.pages.form_default_badge') }}
                                        </span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-4">
                        @foreach($activeLanguages as $language)
                            @php
                                $translation = optional($pageModel)->translations->firstWhere('language_code', $language->code);
                                $oldTranslation = old('translations.'.$language->code, []);
                            @endphp

                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lang-{{ $language->code }}">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">{{ __('cms.pages.form_title', ['code' => strtoupper($language->code)]) }}</label>
                                    <input
                                        type="text"
                                        name="translations[{{ $language->code }}][title]"
                                        value="{{ $oldTranslation['title'] ?? $translation?->title }}"
                                        class="form-control @error('translations.'.$language->code.'.title') is-invalid @enderror"
                                        data-language-code="{{ $language->code }}"
                                        data-translation-title
                                    >
                                    @error('translations.'.$language->code.'.title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">{{ __('cms.pages.form_excerpt', ['code' => strtoupper($language->code)]) }}</label>
                                    <textarea
                                        name="translations[{{ $language->code }}][excerpt]"
                                        class="form-control @error('translations.'.$language->code.'.excerpt') is-invalid @enderror"
                                        rows="2"
                                    >{{ $oldTranslation['excerpt'] ?? $translation?->excerpt }}</textarea>
                                    @error('translations.'.$language->code.'.excerpt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">{{ __('cms.pages.form_content', ['code' => strtoupper($language->code)]) }}</label>
                                    <textarea
                                        id="content_{{ $language->code }}"
                                        name="translations[{{ $language->code }}][content]"
                                        class="form-control ck-editor-multi-languages @error('translations.'.$language->code.'.content') is-invalid @enderror"
                                    >{{ $oldTranslation['content'] ?? $translation?->content }}</textarea>
                                    @error('translations.'.$language->code.'.content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('cms.pages.form_meta_title', ['code' => strtoupper($language->code)]) }}</label>
                                        <input type="text"
                                               name="translations[{{ $language->code }}][meta_title]"
                                               value="{{ $oldTranslation['meta_title'] ?? $translation?->meta_title }}"
                                               class="form-control @error('translations.'.$language->code.'.meta_title') is-invalid @enderror">
                                        @error('translations.'.$language->code.'.meta_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">{{ __('cms.pages.form_meta_description', ['code' => strtoupper($language->code)]) }}</label>
                                        <textarea
                                            name="translations[{{ $language->code }}][meta_description]"
                                            class="form-control @error('translations.'.$language->code.'.meta_description') is-invalid @enderror"
                                            rows="2"
                                        >{{ $oldTranslation['meta_description'] ?? $translation?->meta_description }}</textarea>
                                        @error('translations.'.$language->code.'.meta_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4 mt-4">
                                    <label class="form-label fw-semibold" for="image_file_{{ $language->code }}">
                                        {{ __('cms.pages.form_image', ['code' => strtoupper($language->code)]) }}
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="btn btn-outline-primary" for="image_file_{{ $language->code }}">
                                            <i class="bi bi-upload me-2"></i>{{ __('cms.pages.choose_file') }}
                                        </label>
                                        <input type="file"
                                            id="image_file_{{ $language->code }}"
                                            name="translations[{{ $language->code }}][image]"
                                            accept="image/*"
                                            class="form-control d-none @error('translations.'.$language->code.'.image') is-invalid @enderror"
                                            onchange="previewImage(this, '{{ $language->code }}')">
                                        <span class="text-muted small">{{ __('cms.pages.form_image_help') }}</span>
                                    </div>

                                    <input type="hidden"
                                           id="image_base64_{{ $language->code }}"
                                           name="translations[{{ $language->code }}][image_base64]"
                                           value="{{ $oldTranslation['image_base64'] ?? '' }}">

                                    <div class="mt-3" id="image_preview_{{ $language->code }}"
                                         style="{{ ($oldTranslation['image_base64'] ?? $translation?->image_url) ? '' : 'display:none;' }}">
                                        <img id="image_preview_img_{{ $language->code }}"
                                             src="{{ $oldTranslation['image_base64'] ?? ($translation && $translation->image_url ? Storage::url($translation->image_url) : '#') }}"
                                             alt="{{ __('cms.pages.form_image', ['code' => strtoupper($language->code)]) }}"
                                             class="img-thumbnail" style="max-width: 200px;">
                                    </div>

                                    @error('translations.'.$language->code.'.image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
                    {{ __('cms.pages.back_to_index') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ $mode === 'edit' ? __('cms.pages.form_update') : __('cms.pages.form_save') }}
                </button>
            </div>
        </div>
    </div>
</form>
