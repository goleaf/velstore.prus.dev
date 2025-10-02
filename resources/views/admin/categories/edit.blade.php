@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white">
            <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.categories.heading') }}</h6>
        </div>

        <div class="card-body">
            <form id="categoryEditForm" action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('cms.categories.parent_category') }}</label>
                        <select name="parent_category_id" class="form-select @error('parent_category_id') is-invalid @enderror">
                            <option value="">{{ __('cms.categories.parent_none') }}</option>
                            @foreach($parentOptions as $option)
                                <option value="{{ $option['id'] }}" @selected(old('parent_category_id', $category->parent_category_id) == $option['id'])>
                                    {{ $option['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">{{ __('cms.categories.status') }}</label>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="status" value="0">
                            @php $initialStatus = old('status', $category->status); @endphp
                            <input class="form-check-input" type="checkbox" id="statusToggle" name="status" value="1" {{ $initialStatus ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusToggle" id="statusToggleLabel">
                                {{ $initialStatus ? __('cms.products.status_active') : __('cms.products.status_inactive') }}
                            </label>
                        </div>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mt-4">
                    <ul class="nav nav-tabs" id="languageTabs" role="tablist">
                        @foreach($activeLanguages as $language)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                        id="{{ $language->name }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#{{ $language->name }}"
                                        type="button"
                                        role="tab">
                                    {{ ucwords($language->name) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-3" id="languageTabContent">
                        @foreach($activeLanguages as $language)
                            @php
                                $translation = $category->translations->firstWhere('language_code', $language->code);
                            @endphp
                            <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}"
                                 id="{{ $language->name }}"
                                 role="tabpanel">

                                <label class="form-label">{{ __('cms.categories.name') }} ({{ $language->code }})</label>
                                <input type="text"
                                       name="translations[{{ $language->code }}][name]"
                                       class="form-control @error('translations.'.$language->code.'.name') is-invalid @enderror"
                                       value="{{ old('translations.' . $language->code . '.name', $translation->name ?? '') }}"
                                       required>
                                @error('translations.'.$language->code.'.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label class="form-label mt-2">{{ __('cms.categories.description') }} ({{ $language->code }})</label>
                                <textarea id="description_{{ $language->code }}"
                                          name="translations[{{ $language->code }}][description]"
                                          class="form-control ck-editor-multi-languages @error('translations.'.$language->code.'.description') is-invalid @enderror">{{ old('translations.' . $language->code . '.description', $translation->description ?? '') }}</textarea>
                                @error('translations.'.$language->code.'.description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label class="form-label mt-2">{{ __('cms.categories.image') }} ({{ $language->code }})</label>
                                <div class="custom-file">
                                    <label class="btn btn-primary" for="image_file_{{ $language->code }}">{{ __('cms.categories.choose_file') }}</label>
                                    <input type="file"
                                           id="image_file_{{ $language->code }}"
                                           name="translations[{{ $language->code }}][image]"
                                           accept="image/*"
                                           class="form-control d-none @error('translations.'.$language->code.'.image') is-invalid @enderror"
                                           onchange="previewImage(this, '{{ $language->code }}')">
                                </div>

                                <div id="image_preview_{{ $language->code }}" class="mt-2" style="{{ $translation && $translation->image_url ? 'display: block;' : 'display: none;' }}">
                                    <img id="image_preview_img_{{ $language->code }}"
                                         src="{{ $translation && $translation->image_url ? asset('storage/' . $translation->image_url) : '#' }}"
                                         alt="{{ __('cms.categories.image_preview') }}"
                                         class="img-thumbnail"
                                         style="max-width: 200px;">
                                </div>

                                @error('translations.'.$language->code.'.image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="mt-3 btn btn-primary">{{ cms_translate('categories.button') }}</button>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script>
        const LANG_CODES = {!! json_encode($activeLanguages->pluck('code')) !!};
        const CKEDITORS = {};

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.ck-editor-multi-languages').forEach((el) => {
                const id = el.id;
                ClassicEditor.create(el)
                    .then(editor => {
                        CKEDITORS[id] = editor;
                    })
                    .catch(error => {
                        console.error('CKEditor init error', error);
                    });
            });

            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                const tabPane = firstInvalid.closest('.tab-pane');
                if (tabPane) {
                    const triggerEl = document.querySelector(`button[data-bs-target="#${tabPane.id}"]`);
                    if (triggerEl) {
                        const tab = new bootstrap.Tab(triggerEl);
                        tab.show();
                    }
                }
            }
        });

        function previewImage(input, langCode) {
            const file = input.files[0];
            const previewElement = document.getElementById('image_preview_' + langCode);
            const previewImage = document.getElementById('image_preview_img_' + langCode);

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewElement.style.display = 'block';
                    previewImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                previewElement.style.display = 'none';
            }
        }

        document.getElementById('categoryEditForm').addEventListener('submit', function () {
            for (const code of LANG_CODES) {
                const textareaId = 'description_' + code;
                const editor = CKEDITORS[textareaId];
                if (editor) {
                    const textarea = document.getElementById(textareaId);
                    if (textarea) {
                        textarea.value = editor.getData();
                    }
                }
            }
        });

        const statusToggle = document.getElementById('statusToggle');
        if (statusToggle) {
            statusToggle.addEventListener('change', function () {
                const label = document.getElementById('statusToggleLabel');
                if (!label) {
                    return;
                }

                label.textContent = this.checked
                    ? @json(__('cms.products.status_active'))
                    : @json(__('cms.products.status_inactive'));
            });
        }
    </script>
@endsection
