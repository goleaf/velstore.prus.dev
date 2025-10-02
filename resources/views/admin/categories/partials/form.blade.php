@php
    use Illuminate\Support\Str;

    $isEdit = isset($category) && $category;
    $formAction = $formAction ?? ($isEdit ? route('admin.categories.update', $category) : route('admin.categories.store'));
    $formMethod = strtoupper($formMethod ?? ($isEdit ? 'PUT' : 'POST'));
    $languages = $activeLanguages ?? collect();
    $languageCodes = $languages->pluck('code')->filter()->values();

    $statusValue = old('status', $isEdit ? (int) $category->status : 1);
    $statusBoolean = in_array($statusValue, [1, '1', true, 'true', 'on'], true);

    $parentValue = old(
        'parent_category_id',
        $isEdit ? $category->parent_category_id : ($selectedParent ?? null)
    );
    $parentValue = $parentValue === '' ? null : $parentValue;

    $errorLanguages = collect(optional($errors)->getMessages() ?? [])
        ->keys()
        ->map(function ($key) {
            if (Str::startsWith($key, 'translations.')) {
                $segments = explode('.', $key);
                return $segments[1] ?? null;
            }

            return null;
        })
        ->filter()
        ->unique()
        ->values();

    $translations = [];
    $imagePreviews = [];

    foreach ($languages as $language) {
        $code = $language->code;
        $existingTranslation = $isEdit
            ? optional($category->translations->firstWhere('language_code', $code))
            : null;

        $imageUrl = $existingTranslation && $existingTranslation->image_url
            ? (Str::startsWith($existingTranslation->image_url, ['http://', 'https://'])
                ? $existingTranslation->image_url
                : asset('storage/' . ltrim($existingTranslation->image_url, '/')))
            : null;

        $translations[$code] = [
            'name' => old("translations.$code.name", $existingTranslation?->name ?? ''),
            'description' => old("translations.$code.description", $existingTranslation?->description ?? ''),
            'image_base64' => old("translations.$code.image_base64"),
            'image_url' => $imageUrl,
        ];

        if ($translations[$code]['image_base64']) {
            $imagePreviews[$code] = $translations[$code]['image_base64'];
        } elseif ($imageUrl) {
            $imagePreviews[$code] = $imageUrl;
        }
    }

    $initialTab = $languageCodes->first();
@endphp

<form
    method="POST"
    action="{{ $formAction }}"
    enctype="multipart/form-data"
    x-data="categoryForm({
        languages: @json($languageCodes),
        initialTab: @json($initialTab),
        errorTabs: @json($errorLanguages->all()),
        storageKey: 'admin_categories_active_tab',
        initialStatus: {{ $statusBoolean ? 'true' : 'false' }},
        previews: @json($imagePreviews),
        statusActiveLabel: @json(__('cms.products.status_active')),
        statusInactiveLabel: @json(__('cms.products.status_inactive')),
    })"
    x-on:submit="beforeSubmit"
    class="space-y-6"
>
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <p class="font-semibold mb-2">{{ __('cms.notifications.validation_error') }}</p>
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-admin.card :title="__('cms.categories.details_section_title')">
        <p class="text-sm text-gray-500 mb-6">{{ __('cms.categories.details_section_description') }}</p>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="form-label" for="parent_category_id">{{ __('cms.categories.parent_category') }}</label>
                <select
                    id="parent_category_id"
                    name="parent_category_id"
                    class="form-select @error('parent_category_id') is-invalid @enderror"
                >
                    <option value="">{{ __('cms.categories.parent_none') }}</option>
                    @foreach($parentOptions as $option)
                        <option value="{{ $option['id'] }}" @selected((string) $parentValue === (string) $option['id'])>
                            {{ $option['name'] }}
                        </option>
                    @endforeach
                </select>
                @error('parent_category_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <span class="form-label">{{ __('cms.categories.status') }}</span>
                <p class="text-xs text-gray-500 mb-3">{{ __('cms.categories.status_help') }}</p>
                <input type="hidden" name="status" value="0">
                <label for="statusToggle" class="form-check">
                    <input
                        type="checkbox"
                        id="statusToggle"
                        name="status"
                        value="1"
                        class="form-check-input"
                        :checked="status"
                        @change="status = $event.target.checked"
                    >
                    <span id="statusToggleLabel" class="text-sm font-medium text-gray-700" x-text="status ? statusActiveLabel : statusInactiveLabel"></span>
                </label>
                @error('status')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </x-admin.card>

    <x-admin.card
        :title="__('cms.categories.translations_section_title')"
        :actions="view('admin.categories.partials.language-tabs', ['languages' => $languages])"
    >
        <p class="text-sm text-gray-500 mb-6">{{ __('cms.categories.translations_section_description') }}</p>

        @foreach($languages as $language)
            @php($code = $language->code)
            <section x-show="activeTab === '{{ $code }}'" x-cloak class="space-y-6">
                <div>
                    <label class="form-label" for="category-name-{{ $code }}">
                        {{ __('cms.categories.name') }} ({{ strtoupper($code) }})
                    </label>
                    <input
                        type="text"
                        id="category-name-{{ $code }}"
                        name="translations[{{ $code }}][name]"
                        value="{{ $translations[$code]['name'] ?? '' }}"
                        class="form-control @error('translations.' . $code . '.name') is-invalid @enderror"
                    >
                    @error('translations.' . $code . '.name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="form-label" for="description_{{ $code }}">
                        {{ __('cms.categories.description') }} ({{ strtoupper($code) }})
                    </label>
                    <textarea
                        id="description_{{ $code }}"
                        name="translations[{{ $code }}][description]"
                        class="form-control js-category-editor @error('translations.' . $code . '.description') is-invalid @enderror"
                    >{{ $translations[$code]['description'] ?? '' }}</textarea>
                    <p class="text-xs text-gray-500 mt-2">{{ __('cms.categories.description_helper') }}</p>
                    @error('translations.' . $code . '.description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="form-label" for="image_file_{{ $code }}">
                        {{ __('cms.categories.image') }} ({{ strtoupper($code) }})
                    </label>
                    <div class="flex flex-wrap items-center gap-3">
                        <label for="image_file_{{ $code }}" class="btn btn-outline-primary">
                            {{ __('cms.categories.image_upload') }}
                        </label>
                        <button
                            type="button"
                            class="btn btn-outline-danger"
                            x-show="hasPreview('{{ $code }}')"
                            x-cloak
                            @click="clearImage('{{ $code }}')"
                        >
                            {{ __('cms.categories.image_remove') }}
                        </button>
                        <input
                            type="file"
                            id="image_file_{{ $code }}"
                            name="translations[{{ $code }}][image]"
                            accept="image/*"
                            class="hidden"
                            @change="handleFileChange($event, '{{ $code }}')"
                        >
                    </div>
                    <input
                        type="hidden"
                        id="image_base64_{{ $code }}"
                        name="translations[{{ $code }}][image_base64]"
                        value="{{ $translations[$code]['image_base64'] ?? '' }}"
                    >
                    <p class="text-xs text-gray-500">{{ __('cms.categories.image_helper') }}</p>
                    <div class="mt-4" x-show="hasPreview('{{ $code }}')" x-cloak>
                        <div class="flex h-32 w-48 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                            <img :src="previewFor('{{ $code }}')" alt="{{ __('cms.categories.image_preview') }}" class="max-h-32 w-full object-cover">
                        </div>
                    </div>
                    @error('translations.' . $code . '.image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </section>
        @endforeach
    </x-admin.card>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <x-admin.button-link href="{{ route('admin.categories.index') }}" class="btn-outline">
            {{ __('cms.categories.back_to_index') }}
        </x-admin.button-link>
        <button type="submit" class="btn btn-primary">
            {{ __('cms.categories.button') }}
        </button>
    </div>
</form>

@push('scripts')
    @once
        <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
        <script>
            window.categoryEditors = window.categoryEditors || {};

            function base64ToFile(dataUrl, filename) {
                if (!dataUrl || dataUrl.indexOf(',') === -1) {
                    throw new Error('Invalid base64 data');
                }

                const [meta, content] = dataUrl.split(',');
                const mimeMatch = meta.match(/data:(.*);base64/);

                if (!mimeMatch) {
                    throw new Error('Invalid mime type in base64 data');
                }

                const mime = mimeMatch[1];
                const binary = atob(content);
                const array = new Uint8Array(binary.length);

                for (let i = 0; i < binary.length; i += 1) {
                    array[i] = binary.charCodeAt(i);
                }

                return new File([array], filename, { type: mime });
            }

            function categoryForm(config) {
                return {
                    activeTab: config.initialTab || null,
                    languages: Array.isArray(config.languages) ? config.languages : [],
                    errorTabs: Array.isArray(config.errorTabs) ? config.errorTabs : [],
                    storageKey: config.storageKey || 'admin_categories_active_tab',
                    status: Boolean(config.initialStatus),
                    statusActiveLabel: config.statusActiveLabel || 'Active',
                    statusInactiveLabel: config.statusInactiveLabel || 'Inactive',
                    previews: Object.assign({}, config.previews || {}),

                    init() {
                        if (this.errorTabs.length && this.languages.includes(this.errorTabs[0])) {
                            this.activeTab = this.errorTabs[0];
                        } else if (!this.activeTab && this.storageKey) {
                            const stored = window.localStorage.getItem(this.storageKey);
                            if (stored && this.languages.includes(stored)) {
                                this.activeTab = stored;
                            }
                        }

                        if (!this.activeTab && this.languages.length) {
                            this.activeTab = this.languages[0];
                        }

                        this.$watch('activeTab', (value) => {
                            if (value && this.storageKey) {
                                window.localStorage.setItem(this.storageKey, value);
                            }
                        });

                        this.initializeEditors();
                    },

                    setActiveTab(language) {
                        if (this.languages.includes(language)) {
                            this.activeTab = language;
                        }
                    },

                    hasPreview(language) {
                        return Boolean(this.previews[language]);
                    },

                    previewFor(language) {
                        return this.previews[language] || '';
                    },

                    handleFileChange(event, language) {
                        const [file] = event.target.files || [];

                        if (!file) {
                            this.clearImage(language);
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previews = { ...this.previews, [language]: e.target.result };
                            const hidden = document.getElementById(`image_base64_${language}`);
                            if (hidden) {
                                hidden.value = e.target.result;
                            }
                        };
                        reader.readAsDataURL(file);
                    },

                    clearImage(language) {
                        const fileInput = document.getElementById(`image_file_${language}`);
                        if (fileInput) {
                            fileInput.value = '';
                        }

                        const hidden = document.getElementById(`image_base64_${language}`);
                        if (hidden) {
                            hidden.value = '';
                        }

                        if (this.previews[language]) {
                            const previews = { ...this.previews };
                            delete previews[language];
                            this.previews = previews;
                        }
                    },

                    initializeEditors() {
                        if (!window.ClassicEditor) {
                            console.warn('ClassicEditor not loaded');
                            return;
                        }

                        this.languages.forEach((language) => {
                            const textarea = document.getElementById(`description_${language}`);
                            if (textarea && !textarea.dataset.editorInitialized) {
                                ClassicEditor.create(textarea)
                                    .then((editor) => {
                                        window.categoryEditors[language] = editor;
                                        textarea.dataset.editorInitialized = 'true';
                                    })
                                    .catch((error) => {
                                        console.error('CKEditor init error', error);
                                    });
                            }
                        });
                    },

                    beforeSubmit() {
                        if (window.categoryEditors) {
                            Object.entries(window.categoryEditors).forEach(([language, editor]) => {
                                const textarea = document.getElementById(`description_${language}`);
                                if (textarea && editor) {
                                    textarea.value = editor.getData();
                                }
                            });
                        }

                        this.languages.forEach((language) => {
                            const fileInput = document.getElementById(`image_file_${language}`);
                            const base64Input = document.getElementById(`image_base64_${language}`);

                            if (fileInput && base64Input && !fileInput.files.length && base64Input.value) {
                                try {
                                    const file = base64ToFile(base64Input.value, `category_${language}_${Date.now()}`);
                                    const dataTransfer = new DataTransfer();
                                    dataTransfer.items.add(file);
                                    fileInput.files = dataTransfer.files;
                                } catch (error) {
                                    console.warn('Unable to restore category image for', language, error);
                                }
                            }
                        });
                    },
                };
            }
        </script>
    @endonce
@endpush
