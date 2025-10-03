@php
    $isEdit = isset($attribute);

    $values = old('values');
    if (! is_array($values)) {
        $values = $isEdit ? $attribute->values->pluck('value')->toArray() : [];
    }
    $values = array_values(array_filter($values, fn ($value) => $value !== null));
    if (empty($values)) {
        $values = [''];
    }

    $translations = [];
    foreach ($languages as $language) {
        $code = $language->code;
        $languageTranslations = old("translations.{$code}");

        if (! is_array($languageTranslations) && $isEdit) {
            $languageTranslations = $attribute->values->map(function ($value) use ($code) {
                return optional($value->translations->firstWhere('language_code', $code))->translated_value ?? '';
            })->toArray();
        }

        if (! is_array($languageTranslations)) {
            $languageTranslations = [];
        }

        $translations[$code] = array_pad(array_values($languageTranslations), count($values), '');
    }

    $formMethod = strtoupper($method ?? 'POST');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <x-admin.card :title="__('cms.attributes.attribute_name')">
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <label for="name" class="form-label">{{ __('cms.attributes.attribute_name') }}</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $attribute->name ?? '') }}"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="{{ __('cms.attributes.attribute_name_placeholder') }}"
                >
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </x-admin.card>

    <x-admin.card :title="__('cms.attributes.attribute_values')" :description="__('cms.attributes.attribute_values_help')">
        <div id="attribute-values-container" class="space-y-4">
            @foreach ($values as $index => $value)
                <div class="attribute-value-row rounded border border-gray-200 p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-lg-5">
                            <label class="form-label" data-value-label>{{ __('cms.attributes.value_label', ['number' => $loop->iteration]) }}</label>
                            <input
                                type="text"
                                name="values[]"
                                value="{{ $value }}"
                                class="form-control @error('values.' . $index) is-invalid @enderror"
                                placeholder="{{ __('cms.attributes.value_placeholder', ['number' => $loop->iteration]) }}"
                            >
                            @error('values.' . $index)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 d-flex gap-2">
                            <button type="button" class="btn btn-outline-danger attribute-value-remove">
                                {{ __('cms.attributes.remove_value') }}
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        @foreach ($languages as $language)
                            @php
                                $code = $language->code;
                                $translationValue = $translations[$code][$index] ?? '';
                            @endphp
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" data-translation-label data-language="{{ $code }}">
                                    {{ __('cms.attributes.translation_label', ['language' => ucwords($language->name)]) }}
                                </label>
                                <input
                                    type="text"
                                    name="translations[{{ $code }}][]"
                                    value="{{ $translationValue }}"
                                    class="form-control @error('translations.' . $code . '.' . $index) is-invalid @enderror"
                                    placeholder="{{ __('cms.attributes.translation_placeholder', ['language' => ucwords($language->name)]) }}"
                                >
                                @error('translations.' . $code . '.' . $index)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-attribute-value" class="btn btn-outline-primary mt-3">
            {{ __('cms.attributes.add_value') }}
        </button>
    </x-admin.card>

    <div class="d-flex flex-column flex-md-row gap-3 justify-content-end">
        <x-admin.button-link href="{{ route('admin.attributes.index') }}" class="btn-outline">
            {{ __('cms.attributes.cancel') }}
        </x-admin.button-link>
        <button type="submit" class="btn btn-success">
            {{ $submitLabel }}
        </button>
    </div>
</form>
