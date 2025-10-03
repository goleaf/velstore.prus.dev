@extends('admin.layouts.admin')

@section('content')
@php
    $defaultLocale = config('app.locale');
    $baseUrl = rtrim(url('/'), '/');
@endphp

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mt-4">
    <div>
        <h1 class="h4 mb-1">{{ __('cms.pages.create') }}</h1>
        <p class="text-muted mb-0">{{ __('cms.pages.create_description') }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>{{ __('cms.pages.back_to_index') }}
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-3" role="alert">
        <strong>{{ __('cms.pages.validation_error_title') }}</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="pageForm" action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
    @csrf

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 mb-2">{{ __('cms.pages.form_section_overview') }}</h2>
                    <p class="text-muted small mb-4">{{ __('cms.pages.form_section_overview_help') }}</p>

                    <input type="hidden" name="status" id="page-status-input" value="{{ old('status', 1) ? 1 : 0 }}">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="page-status-toggle" {{ old('status', 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="page-status-toggle">{{ __('cms.pages.form_status_label') }}</label>
                    </div>
                    <p class="text-muted small mt-2" id="page-status-help">
                        {{ old('status', 1) ? __('cms.pages.form_status_help_active') : __('cms.pages.form_status_help_inactive') }}
                    </p>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('cms.pages.slug_preview_label') }}</label>
                        <div class="input-group">
                            <span class="input-group-text text-muted">{{ $baseUrl }}/</span>
                            <span class="form-control bg-light text-muted" data-slug-preview>{{ __('cms.pages.slug_preview_placeholder') }}</span>
                        </div>
                        <p class="text-muted small mt-2">{{ __('cms.pages.slug_preview_hint') }}</p>
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
                                        <span class="badge bg-primary-subtle text-primary ms-1">{{ __('cms.pages.form_default_badge') }}</span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-4">
                        @foreach($activeLanguages as $language)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lang-{{ $language->code }}">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">{{ __('cms.pages.form_title', ['code' => strtoupper($language->code)]) }}</label>
                                    <input
                                        type="text"
                                        name="translations[{{ $language->code }}][title]"
                                        value="{{ old('translations.'.$language->code.'.title') }}"
                                        class="form-control @error('translations.'.$language->code.'.title') is-invalid @enderror"
                                        data-language-code="{{ $language->code }}"
                                        data-translation-title
                                    >
                                    @error('translations.'.$language->code.'.title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">{{ __('cms.pages.form_content', ['code' => strtoupper($language->code)]) }}</label>
                                    <textarea
                                        id="content_{{ $language->code }}"
                                        name="translations[{{ $language->code }}][content]"
                                        class="form-control ck-editor-multi-languages @error('translations.'.$language->code.'.content') is-invalid @enderror"
                                    >{{ old('translations.'.$language->code.'.content') }}</textarea>
                                    @error('translations.'.$language->code.'.content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
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
                                           value="{{ old('translations.'.$language->code.'.image_base64') }}">

                                    <div class="mt-3" id="image_preview_{{ $language->code }}"
                                         style="{{ old('translations.'.$language->code.'.image_base64') ? '' : 'display:none;' }}">
                                        <img id="image_preview_img_{{ $language->code }}"
                                             src="{{ old('translations.'.$language->code.'.image_base64') ?: '#' }}"
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
                <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">{{ __('cms.pages.back_to_index') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('cms.pages.form_save') }}</button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js')
<script>
document.addEventListener("DOMContentLoaded", function () {
    @if ($errors->any())
        var firstErrorElement = document.querySelector('.is-invalid');
        if (firstErrorElement) {
            var tabPane = firstErrorElement.closest('.tab-pane');
            if (tabPane) {
                var tabId = tabPane.getAttribute('id');
                var triggerEl = document.querySelector(`button[data-bs-target="#${tabId}"]`);
                if (triggerEl) {
                    var tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            }
        }
    @endif
});
</script>

<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    const LANG_CODES = {!! json_encode($activeLanguages->pluck('code')) !!};
    const CKEDITORS = {};
    const DEFAULT_LOCALE = "{{ $defaultLocale }}";

    document.querySelectorAll('.ck-editor-multi-languages').forEach((el) => {
        const id = el.id;
        ClassicEditor.create(el)
            .then(editor => { CKEDITORS[id] = editor; })
            .catch(error => { console.error('CKEditor init error', error); });
    });

    window.previewImage = function(input, langCode) {
        var file = input.files[0];
        var previewElement = document.getElementById('image_preview_' + langCode);
        var previewImage = document.getElementById('image_preview_img_' + langCode);
        var hiddenInput = document.getElementById('image_base64_' + langCode);

        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewElement.style.display = 'block';
                previewImage.src = e.target.result;
                hiddenInput.value = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            previewElement.style.display = 'none';
            previewImage.src = '';
            hiddenInput.value = '';
        }
    }

    function base64ToFile(dataurl, baseName) {
        if (!dataurl || dataurl.indexOf(',') === -1) return null;
        var arr = dataurl.split(',');
        var mimeMatch = arr[0].match(/data:(.*);base64/);
        if (!mimeMatch) return null;
        var mime = mimeMatch[1];
        var ext = mime.split('/')[1].split('+')[0];
        if (ext === 'jpeg') ext = 'jpg';
        var bstr = atob(arr[1]);
        var n = bstr.length;
        var u8arr = new Uint8Array(n);
        for (var i = 0; i < n; i++) {
            u8arr[i] = bstr.charCodeAt(i);
        }
        var filename = baseName + '.' + ext;
        return new File([u8arr], filename, { type: mime });
    }

    document.getElementById('pageForm').addEventListener('submit', function (e) {
        for (const code of LANG_CODES) {
            const textareaId = 'content_' + code;
            const editor = CKEDITORS[textareaId];
            if (editor) {
                const textarea = document.getElementById(textareaId);
                if (textarea) textarea.value = editor.getData();
            }
        }

        for (const code of LANG_CODES) {
            const fileInput = document.getElementById('image_file_' + code);
            const base64Input = document.getElementById('image_base64_' + code);

            if (fileInput && fileInput.files.length === 0 && base64Input && base64Input.value) {
                try {
                    const f = base64ToFile(base64Input.value, 'image_' + code);
                    if (f) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(f);
                        fileInput.files = dataTransfer.files;
                    }
                } catch (err) {
                    console.error('base64 -> File conversion failed for', code, err);
                }
            }
        }
    });

    const statusToggle = document.getElementById('page-status-toggle');
    const statusInput = document.getElementById('page-status-input');
    const statusHelp = document.getElementById('page-status-help');

    if (statusToggle && statusInput) {
        statusToggle.addEventListener('change', function () {
            const isActive = statusToggle.checked;
            statusInput.value = isActive ? 1 : 0;
            if (statusHelp) {
                statusHelp.textContent = isActive
                    ? "{{ __('cms.pages.form_status_help_active') }}"
                    : "{{ __('cms.pages.form_status_help_inactive') }}";
            }
        });
    }

    function slugify(text) {
        return text
            .toString()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    const slugPreview = document.querySelector('[data-slug-preview]');
    if (slugPreview) {
        const defaultTitleInput = document.querySelector('[name="translations[' + DEFAULT_LOCALE + '][title]"]');

        const updateSlugPreview = () => {
            if (!defaultTitleInput) return;
            const slug = slugify(defaultTitleInput.value || '');
            slugPreview.textContent = slug || '{{ __('cms.pages.slug_preview_placeholder') }}';
        };

        if (defaultTitleInput) {
            defaultTitleInput.addEventListener('input', updateSlugPreview);
            updateSlugPreview();
        }
    }
});
</script>
@endsection
