@php
    $isEdit = isset($attribute);

    $values = old('values');
    if (is_array($values)) {
        $values = array_values($values);
    } elseif ($isEdit) {
        $values = $attribute->values->pluck('value')->toArray();
    } else {
        $values = [];
    }

    if (empty($values)) {
        $values = [''];
    }

    $translations = [];
    foreach ($languages as $language) {
        $code = $language->code;
        $languageTranslations = old("translations.{$code}");

        if (is_array($languageTranslations)) {
            $languageTranslations = array_values($languageTranslations);
        } elseif ($isEdit) {
            $languageTranslations = $attribute->values->map(function ($value) use ($code) {
                return optional($value->translations->firstWhere('language_code', $code))->translated_value ?? '';
            })->toArray();
        } else {
            $languageTranslations = [];
        }

        if (count($languageTranslations) > count($values)) {
            $languageTranslations = array_slice($languageTranslations, 0, count($values));
        }

        $translations[$code] = array_pad($languageTranslations, count($values), '');
    }

    $formMethod = strtoupper($method ?? 'POST');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <x-admin.card :title="__('cms.attributes.attribute_name')">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="name" class="form-label">{{ __('cms.attributes.attribute_name') }}</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $attribute->name ?? '') }}"
                    class="form-control @error('name') is-invalid @enderror"
                >
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </x-admin.card>

    <x-admin.card :title="__('cms.attributes.attribute_values')">
        <div id="attribute-values-container" class="space-y-4">
            @foreach ($values as $index => $value)
                @php
                    $rowId = 'attribute-value-row-' . $index;
                @endphp
                <div class="attribute-value-row space-y-4 rounded-lg border border-gray-200 p-4" data-row-id="{{ $rowId }}">
                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:gap-3">
                        <div class="flex-1">
                            <input
                                type="text"
                                name="values[]"
                                value="{{ $value }}"
                                class="form-control @error('values.' . $index) is-invalid @enderror"
                            >
                            @error('values.' . $index)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="button" class="btn btn-outline-danger attribute-value-remove self-start">
                            {{ __('cms.attributes.remove_value') }}
                        </button>
                    </div>

                    <div class="translation-section">
                        <ul class="nav nav-tabs attribute-language-tabs" id="{{ $rowId }}-tabs" role="tablist">
                            @foreach ($languages as $language)
                                @php
                                    $code = $language->code;
                                @endphp
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link {{ $loop->first ? 'active' : '' }}"
                                        id="{{ $rowId }}-{{ $code }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#{{ $rowId }}-{{ $code }}-panel"
                                        type="button"
                                        role="tab"
                                        aria-controls="{{ $rowId }}-{{ $code }}-panel"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                    >
                                        {{ ucwords($language->name) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tab-content mt-3" id="{{ $rowId }}-tab-content">
                            @foreach ($languages as $language)
                                @php
                                    $code = $language->code;
                                    $translationValue = $translations[$code][$index] ?? '';
                                @endphp
                                <div
                                    class="tab-pane fade show {{ $loop->first ? 'active' : '' }}"
                                    id="{{ $rowId }}-{{ $code }}-panel"
                                    role="tabpanel"
                                    aria-labelledby="{{ $rowId }}-{{ $code }}-tab"
                                >
                                    <input
                                        type="text"
                                        name="translations[{{ $code }}][]"
                                        value="{{ $translationValue }}"
                                        class="form-control @error('translations.' . $code . '.' . $index) is-invalid @enderror"
                                    >
                                    @error('translations.' . $code . '.' . $index)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-attribute-value" class="btn btn-outline-primary mt-3">
            {{ __('cms.attributes.add_value') }}
        </button>
    </x-admin.card>

    <div class="flex flex-col-reverse gap-3 md:flex-row md:items-center md:justify-end">
        <x-admin.button-link href="{{ route('admin.attributes.index') }}" class="btn-outline">
            {{ __('cms.attributes.cancel') }}
        </x-admin.button-link>
        <button type="submit" class="btn btn-success">
            {{ $submitLabel }}
        </button>
    </div>
</form>
