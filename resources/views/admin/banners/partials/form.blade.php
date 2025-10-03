@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $languageCodes = $languages->pluck('code')->toArray();
    $storageKey = 'admin_banners_active_tab';
    $previews = [];
    $errorTabs = [];
    $languageFields = [];
    $locationOptions = [
        'home' => __('cms.banners.location_home'),
        'shop' => __('cms.banners.location_shop'),
        'category' => __('cms.banners.location_category'),
        'product' => __('cms.banners.location_product'),
        'global' => __('cms.banners.location_global'),
    ];
    $displayLocation = old('display_location', $banner?->display_location ?? 'home');
    $priorityValue = old('priority', $banner?->priority ?? 0);
    $startsAtValue = old('starts_at', optional($banner?->starts_at)->format('Y-m-d\TH:i'));
    $endsAtValue = old('ends_at', optional($banner?->ends_at)->format('Y-m-d\TH:i'));

    $resolveImageUrl = static function (?string $path) {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = Str::startsWith($path, 'public/') ? Str::after($path, 'public/') : $path;

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::disk('public')->url($normalized);
        }

        if (Storage::exists($normalized)) {
            return Storage::url($normalized);
        }

        return asset($normalized);
    };

    foreach ($languages as $index => $language) {
        $code = $language->code;
        $translation = $isEdit ? ($translations[$code] ?? null) : null;

        $inputPrefix = $isEdit ? "languages[$index]" : "languages[$code]";
        $oldPrefix = $isEdit ? "languages.$index" : "languages.$code";

        $titleValue = old("$oldPrefix.title", $translation->title ?? '');
        $descriptionValue = old("$oldPrefix.description", $translation->description ?? '');
        $base64Value = old("$oldPrefix.image_base64");

        $existingImageUrl = $translation?->image_url ? $resolveImageUrl($translation->image_url) : null;
        $previewUrl = $base64Value ?: $existingImageUrl;

        if (
            $errors->has("$oldPrefix.title") ||
            $errors->has("$oldPrefix.description") ||
            $errors->has("$oldPrefix.image") ||
            $errors->has("$oldPrefix.button_text") ||
            $errors->has("$oldPrefix.button_url")
        ) {
            $errorTabs[] = $code;
        }

        if ($previewUrl) {
            $previews[$code] = $previewUrl;
        }

        $languageFields[$code] = [
            'index' => $index,
            'input_prefix' => $inputPrefix,
            'old_prefix' => $oldPrefix,
            'language_code' => $translation->language_code ?? $code,
            'title' => $titleValue,
            'description' => $descriptionValue,
            'base64' => $base64Value,
            'button_text' => old("$oldPrefix.button_text", $translation->button_text ?? ''),
            'button_url' => old("$oldPrefix.button_url", $translation->button_url ?? ''),
        ];
    }

    $initialTab = old('active_tab');
    if (! $initialTab) {
        $initialTab = $errorTabs[0] ?? ($languageCodes[0] ?? 'en');
    }
@endphp

<x-admin.page-header :title="$pageTitle">
    <x-admin.button-link href="{{ route('admin.banners.index') }}" class="btn-outline">
        {{ __('cms.banners.back_to_list') }}
    </x-admin.button-link>
</x-admin.page-header>

<form
    x-data="bannerForm({
        languages: @json($languageCodes),
        initialTab: @json($initialTab),
        storageKey: @json($storageKey),
        previews: @json($previews),
        errorTabs: @json($errorTabs),
    })"
    @submit="beforeSubmit($event)"
    action="{{ $formAction }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-6 mt-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" name="active_tab" x-model="activeTab">

    <x-admin.card :title="__('cms.banners.form_title')">
        <div class="grid gap-6 md:grid-cols-2">
            <div class="md:col-span-1 space-y-6">
                <div>
                    <label class="form-label" for="banner_type">{{ __('cms.banners.banner_type') }}</label>
                    <select
                        id="banner_type"
                        name="type"
                        @class([
                            'form-select',
                            'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('type'),
                        ])
                    >
                        <option value="promotion" @selected(old('type', $banner?->type ?? 'promotion') === 'promotion')>{{ __('cms.banners.promotion') }}</option>
                        <option value="sale" @selected(old('type', $banner?->type ?? '') === 'sale')>{{ __('cms.banners.sale') }}</option>
                        <option value="seasonal" @selected(old('type', $banner?->type ?? '') === 'seasonal')>{{ __('cms.banners.seasonal') }}</option>
                        <option value="featured" @selected(old('type', $banner?->type ?? '') === 'featured')>{{ __('cms.banners.featured') }}</option>
                        <option value="announcement" @selected(old('type', $banner?->type ?? '') === 'announcement')>{{ __('cms.banners.announcement') }}</option>
                    </select>
                    @error('type')
                        <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label" for="banner_location">{{ __('cms.banners.display_location') }}</label>
                    <select
                        id="banner_location"
                        name="display_location"
                        @class([
                            'form-select',
                            'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('display_location'),
                        ])
                    >
                        @foreach ($locationOptions as $value => $label)
                            <option value="{{ $value }}" @selected($displayLocation === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">{{ __('cms.banners.display_location_help') }}</p>
                    @error('display_location')
                        <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="md:col-span-1 space-y-6">
                <div>
                    <label class="form-label" for="banner_status">{{ __('cms.banners.status') }}</label>
                    <select
                        id="banner_status"
                        name="status"
                        @class([
                            'form-select',
                            'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('status'),
                        ])
                    >
                        <option value="1" @selected(old('status', $banner?->status ?? 1) == 1)>{{ __('cms.banners.active') }}</option>
                        <option value="0" @selected(old('status', $banner?->status ?? 1) == 0)>{{ __('cms.banners.inactive') }}</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">{{ __('cms.banners.status_help') }}</p>
                    @error('status')
                        <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label" for="banner_priority">{{ __('cms.banners.priority') }}</label>
                    <input
                        type="number"
                        id="banner_priority"
                        name="priority"
                        min="0"
                        step="1"
                        value="{{ $priorityValue }}"
                        @class([
                            'form-control',
                            'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('priority'),
                        ])
                    >
                    <p class="text-xs text-gray-500 mt-1">{{ __('cms.banners.priority_help') }}</p>
                    @error('priority')
                        <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2 mt-6">
            <div>
                <label class="form-label" for="banner_starts_at">{{ __('cms.banners.starts_at') }}</label>
                <input
                    type="datetime-local"
                    id="banner_starts_at"
                    name="starts_at"
                    value="{{ $startsAtValue }}"
                    @class([
                        'form-control',
                        'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('starts_at'),
                    ])
                >
                <p class="text-xs text-gray-500 mt-1">{{ __('cms.banners.starts_at_help') }}</p>
                @error('starts_at')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label" for="banner_ends_at">{{ __('cms.banners.ends_at') }}</label>
                <input
                    type="datetime-local"
                    id="banner_ends_at"
                    name="ends_at"
                    value="{{ $endsAtValue }}"
                    @class([
                        'form-control',
                        'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('ends_at'),
                    ])
                >
                <p class="text-xs text-gray-500 mt-1">{{ __('cms.banners.ends_at_help') }}</p>
                @error('ends_at')
                    <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </x-admin.card>

    <x-admin.card :title="__('cms.banners.languages')" :actions="view('admin.banners.partials.language-tabs', ['languages' => $languages])">
        <div class="space-y-8">
            @foreach ($languages as $language)
                @php
                    $code = $language->code;
                    $field = $languageFields[$code];
                    $inputPrefix = $field['input_prefix'];
                    $oldPrefix = $field['old_prefix'];
                @endphp
                <section x-show="activeTab === '{{ $code }}'" x-cloak class="space-y-6">
                    <div>
                        <label class="form-label" for="title_{{ $code }}">{{ __('cms.banners.title') }} ({{ strtoupper($code) }})</label>
                        <input
                            id="title_{{ $code }}"
                            type="text"
                            name="{{ $inputPrefix }}[title]"
                            value="{{ $field['title'] }}"
                            @class([
                                'form-control',
                                'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has($oldPrefix . '.title'),
                            ])
                            required
                        >
                        @error($oldPrefix . '.title')
                            <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label" for="description_{{ $code }}">{{ __('cms.banners.description') }} ({{ strtoupper($code) }})</label>
                        <textarea
                            id="description_{{ $code }}"
                            name="{{ $inputPrefix }}[description]"
                            rows="4"
                            @class([
                                'form-control',
                                'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has($oldPrefix . '.description'),
                            ])
                        >{{ $field['description'] }}</textarea>
                        @error($oldPrefix . '.description')
                            <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="form-label" for="button_text_{{ $code }}">{{ __('cms.banners.button_text') }} ({{ strtoupper($code) }})</label>
                            <input
                                id="button_text_{{ $code }}"
                                type="text"
                                name="{{ $inputPrefix }}[button_text]"
                                value="{{ $field['button_text'] }}"
                                class="form-control"
                            >
                            @error($oldPrefix . '.button_text')
                                <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="button_url_{{ $code }}">{{ __('cms.banners.button_url') }} ({{ strtoupper($code) }})</label>
                            <input
                                id="button_url_{{ $code }}"
                                type="text"
                                name="{{ $inputPrefix }}[button_url]"
                                value="{{ $field['button_url'] }}"
                                @class([
                                    'form-control',
                                    'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has($oldPrefix . '.button_url'),
                                ])
                                placeholder="https://example.com or /collections"
                            >
                            <p class="text-xs text-gray-500 mt-1">{{ __('cms.banners.button_url_help') }}</p>
                            @error($oldPrefix . '.button_url')
                                <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-3 justify-between">
                            <div>
                                <label class="form-label mb-0">{{ __('cms.banners.image') }} ({{ strtoupper($code) }})</label>
                                <p class="text-xs text-gray-500 mt-1">{{ __('cms.banners.file_upload') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <label for="image_file_{{ $code }}" class="btn btn-outline btn-sm">
                                    {{ __('cms.banners.choose_file') }}
                                </label>
                                <input
                                    type="file"
                                    id="image_file_{{ $code }}"
                                    name="{{ $inputPrefix }}[image]"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleFileChange($event, '{{ $code }}')"
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    @click="clearImage('{{ $code }}')"
                                    x-show="hasPreview('{{ $code }}')"
                                    x-cloak
                                >
                                    {{ __('cms.banners.delete') }}
                                </button>
                            </div>
                        </div>

                        <input type="hidden" id="image_base64_{{ $code }}" name="{{ $inputPrefix }}[image_base64]" value="{{ $field['base64'] }}">
                        @if ($isEdit)
                            <input type="hidden" name="{{ $inputPrefix }}[language_code]" value="{{ $field['language_code'] }}">
                        @endif

                        <div class="mt-3" x-show="hasPreview('{{ $code }}')" x-cloak>
                            <div class="flex items-center gap-4">
                                <div class="flex h-24 w-40 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                    <img :src="previewFor('{{ $code }}')" alt="{{ __('cms.banners.image_preview') }}" class="max-h-24 w-full object-contain">
                                </div>
                            </div>
                        </div>

                        @error($oldPrefix . '.image')
                            <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </section>
            @endforeach
        </div>
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="btn btn-primary">
            {{ __('cms.banners.save') }}
        </button>
        <x-admin.button-link href="{{ route('admin.banners.index') }}" class="btn-outline">
            {{ __('cms.banners.back_to_list') }}
        </x-admin.button-link>
    </div>
</form>

@push('scripts')
    <script>
        function bannerForm(config) {
            return {
                activeTab: config.initialTab || (config.languages && config.languages[0]) || 'en',
                languages: config.languages || [],
                storageKey: config.storageKey || 'admin_banners_active_tab',
                previews: Object.assign({}, config.previews || {}),
                init() {
                    if (config.errorTabs && config.errorTabs.length > 0) {
                        this.activeTab = config.errorTabs[0];
                    } else {
                        const stored = window.localStorage.getItem(this.storageKey);
                        if (stored && this.languages.includes(stored)) {
                            this.activeTab = stored;
                        }
                    }

                    this.$watch('activeTab', (value) => {
                        window.localStorage.setItem(this.storageKey, value);
                    });
                },
                setActive(language) {
                    this.activeTab = language;
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

                    const previews = { ...this.previews };
                    delete previews[language];
                    this.previews = previews;
                },
                beforeSubmit() {
                    this.languages.forEach((language) => {
                        const fileInput = document.getElementById(`image_file_${language}`);
                        const base64Input = document.getElementById(`image_base64_${language}`);

                        if (fileInput && base64Input && !fileInput.files.length && base64Input.value) {
                            try {
                                const file = base64ToFile(base64Input.value, `banner_${language}_${Date.now()}`);
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(file);
                                fileInput.files = dataTransfer.files;
                            } catch (error) {
                                console.warn('Unable to restore banner image for', language, error);
                            }
                        }
                    });
                },
            };
        }

        function base64ToFile(dataUrl, filename) {
            if (!dataUrl || dataUrl.indexOf(',') === -1) {
                throw new Error('Invalid base64 data');
            }

            const [meta, content] = dataUrl.split(',');
            const mimeMatch = meta.match(/data:(.*);base64/);

            if (!mimeMatch) {
                throw new Error('Invalid mime type');
            }

            const mime = mimeMatch[1];
            const binary = atob(content);
            const buffer = new Uint8Array(binary.length);

            for (let i = 0; i < binary.length; i++) {
                buffer[i] = binary.charCodeAt(i);
            }

            const extension = mime.split('/')[1]?.split('+')[0] || 'png';
            const normalizedExtension = extension === 'jpeg' ? 'jpg' : extension;

            return new File([buffer], `${filename}.${normalizedExtension}`, { type: mime });
        }
    </script>
@endpush
