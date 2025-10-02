@php
    $activeLanguageTab ??= $languages->first()->code ?? null;
@endphp

@if ($languages->count())
    <nav class="nav-tabs" role="tablist">
        @foreach ($languages as $language)
            @php
                $langCode = $language->code;
                $hasErrors = isset($errors) ? $errors->has("translations.$langCode.*") : false;
                $checklist = $translationChecklist[$langCode] ?? null;
                $isComplete = (bool) ($checklist['complete'] ?? false);
            @endphp
            <button
                type="button"
                @click="activeTab = '{{ $langCode }}'"
                @class([
                    'relative focus:outline-none transition-colors duration-200 nav-tab',
                    'nav-tab-active' => $activeLanguageTab === $langCode,
                    'nav-tab-inactive' => $activeLanguageTab !== $langCode,
                ])
                :class="{
                    'nav-tab-active': activeTab === '{{ $langCode }}',
                    'nav-tab-inactive': activeTab !== '{{ $langCode }}',
                }"
            >
                <span class="inline-flex items-center gap-2">
                    <span class="uppercase text-xs font-semibold text-gray-500">{{ $langCode }}</span>
                    <span>{{ ucwords($language->name ?? $langCode) }}</span>
                </span>

                @if ($hasErrors || $isComplete)
                    <span class="sr-only">
                        {{ $hasErrors
                            ? __('cms.products.translation_status_missing_fields')
                            : __('cms.products.translation_status_complete') }}
                    </span>
                    <span
                        aria-hidden="true"
                        @class([
                            'absolute -top-1 -right-1 block h-2.5 w-2.5 rounded-full',
                            'bg-danger-500' => $hasErrors,
                            'bg-success-500' => ! $hasErrors && $isComplete,
                        ])
                    ></span>
                @endif
            </button>
        @endforeach
    </nav>
@endif
