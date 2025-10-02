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
                                        id="tab-{{ $language->code }}"
                                        data-bs-toggle="tab"
                                        data-bs-target="#tab-pane-{{ $language->code }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="tab-pane-{{ $language->code }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ ucwords($language->name) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-3" id="languageTabContent">
                        @foreach($activeLanguages as $language)
                            @php
                                $translation = $category->translations->firstWhere('language_code', $language->code);
                                $oldBase64 = old('translations.' . $language->code . '.image_base64');
                                $defaultPreviewSrc = $translation && $translation->image_url ? asset('storage/' . $translation->image_url) : '';
                                $initialPreviewSrc = $oldBase64 ?: $defaultPreviewSrc;
                                $hasPreview = !empty($initialPreviewSrc);
                                $previewSrc = $hasPreview ? $initialPreviewSrc : '#';
                            @endphp
                            <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}"
                                 id="tab-pane-{{ $language->code }}"
                                 role="tabpanel"
                                 aria-labelledby="tab-{{ $language->code }}">

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

                                <input type="hidden"
                                       id="image_base64_{{ $language->code }}"
                                       name="translations[{{ $language->code }}][image_base64]"
                                       value="{{ $oldBase64 ?? '' }}">

                                <div id="image_preview_{{ $language->code }}" class="mt-2" style="{{ $hasPreview ? 'display: block;' : 'display: none;' }}">
                                    <img id="image_preview_img_{{ $language->code }}"
                                         src="{{ $previewSrc }}"
                                         data-default-src="{{ $defaultPreviewSrc }}"
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
            const base64Input = document.getElementById('image_base64_' + langCode);

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (previewElement && previewImage) {
                        previewElement.style.display = 'block';
                        previewImage.src = e.target.result;
                    }

                    if (base64Input) {
                        base64Input.value = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                if (base64Input) {
                    base64Input.value = '';
                }

                if (previewImage) {
                    const defaultSrc = previewImage.dataset ? previewImage.dataset.defaultSrc : '';
                    if (defaultSrc) {
                        previewImage.src = defaultSrc;
                        if (previewElement) {
                            previewElement.style.display = 'block';
                        }
                    } else {
                        previewImage.src = '#';
                        if (previewElement) {
                            previewElement.style.display = 'none';
                        }
                    }
                } else if (previewElement) {
                    previewElement.style.display = 'none';
                }
            }
        }

        function base64ToFile(dataurl, baseName) {
            if (!dataurl || dataurl.indexOf(',') === -1) throw new Error('Invalid base64 data');
            const arr = dataurl.split(',');
            const mimeMatch = arr[0].match(/data:(.*);base64/);
            if (!mimeMatch) throw new Error('Invalid mime in base64 data');
            const mime = mimeMatch[1];
            let ext = mime.split('/')[1].split('+')[0];
            if (ext === 'jpeg') ext = 'jpg';
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            const filename = baseName + '.' + ext;
            return new File([u8arr], filename, { type: mime });
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

            for (const code of LANG_CODES) {
                const fileInput = document.getElementById('image_file_' + code);
                const base64Input = document.getElementById('image_base64_' + code);

                if (fileInput && fileInput.files.length === 0 && base64Input && base64Input.value) {
                    try {
                        const file = base64ToFile(base64Input.value, 'image_' + code);
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fileInput.files = dataTransfer.files;
                    } catch (error) {
                        console.error('base64 -> File conversion failed for', code, error);
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
