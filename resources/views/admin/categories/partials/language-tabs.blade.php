@php
    $languages = isset($languages) ? collect($languages) : collect();
    $errorLanguages = collect($errorLanguages ?? [])
        ->map(fn ($code) => (string) $code)
        ->filter()
        ->values()
        ->all();
    $initialActive = old('active_language') ?? ($languages->first()->code ?? null);
@endphp

@if ($languages->count())
    <nav
        class="flex flex-wrap gap-2 border-b border-gray-200 pb-2"
        role="tablist"
        aria-label="{{ __('cms.categories.language_tabs_label') }}"
    >
        @foreach ($languages as $language)
            @php
                $code = (string) $language->code;
                $tabId = \Illuminate\Support\Str::slug($code);
                $hasErrors = in_array($code, $errorLanguages, true);
                $isInitiallyActive = $initialActive === $code;
            @endphp
            <button
                type="button"
                id="category-language-tab-{{ $tabId }}"
                role="tab"
                aria-controls="category-language-panel-{{ $tabId }}"
                @class([
                    'relative nav-tab transition-colors duration-200 focus:outline-none',
                    'nav-tab-active' => $isInitiallyActive,
                    'nav-tab-inactive' => ! $isInitiallyActive,
                    '!text-danger-600' => $hasErrors && ! $isInitiallyActive,
                    '!text-danger-700' => $hasErrors && $isInitiallyActive,
                ])
                :class="{
                    'nav-tab-active': activeTab === '{{ $code }}',
                    'nav-tab-inactive': activeTab !== '{{ $code }}',
                    '!text-danger-600': errorTabs.includes('{{ $code }}') && activeTab !== '{{ $code }}',
                    '!text-danger-700': errorTabs.includes('{{ $code }}') && activeTab === '{{ $code }}',
                }"
                aria-selected="{{ $isInitiallyActive ? 'true' : 'false' }}"
                :aria-selected="activeTab === '{{ $code }}'"
                @click="setActiveTab('{{ $code }}')"
            >
                <span class="inline-flex items-center gap-2">
                    <span
                        @class([
                            'text-xs font-semibold uppercase transition-colors duration-200',
                            'text-gray-500' => ! $hasErrors,
                            '!text-danger-600' => $hasErrors,
                        ])
                        :class="{
                            '!text-danger-600': errorTabs.includes('{{ $code }}'),
                            'text-gray-500': ! errorTabs.includes('{{ $code }}'),
                        }"
                    >
                        {{ strtoupper($code) }}
                    </span>
                    <span
                        @class([
                            'text-sm font-medium transition-colors duration-200',
                            'text-gray-700' => ! $hasErrors,
                            '!text-danger-600' => $hasErrors,
                        ])
                        :class="{
                            '!text-danger-600': errorTabs.includes('{{ $code }}'),
                            'text-gray-700': ! errorTabs.includes('{{ $code }}'),
                        }"
                    >
                        {{ ucwords($language->name ?? $code) }}
                    </span>
                </span>

                <span class="sr-only">
                    {{ __('cms.categories.language_tab_button', ['language' => ucwords($language->name ?? $code)]) }}
                </span>

                <span
                    @class([
                        'absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-danger-500',
                        'hidden' => ! $hasErrors,
                    ])
                    x-show="errorTabs.includes('{{ $code }}')"
                    x-cloak
                    aria-hidden="true"
                ></span>
            </button>
        @endforeach
    </nav>
@endif
