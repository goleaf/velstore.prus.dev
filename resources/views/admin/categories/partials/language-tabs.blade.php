<nav class="flex flex-wrap gap-2" role="tablist">
    @foreach ($languages as $language)
        @php($code = $language->code)
        <button
            type="button"
            @click="setActiveTab('{{ $code }}')"
            :class="activeTab === '{{ $code }}' ? 'nav-tab-active' : 'nav-tab-inactive'"
            class="nav-tab"
        >
            <span class="inline-flex items-center gap-2">
                <span class="text-xs font-semibold uppercase text-gray-500">{{ strtoupper($code) }}</span>
                <span class="text-sm font-medium text-gray-700">{{ ucwords($language->name ?? $code) }}</span>
            </span>
        </button>
    @endforeach
</nav>
