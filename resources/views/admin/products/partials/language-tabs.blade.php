@php
    $first = $languages->first();
@endphp
<nav class="flex gap-3" role="tablist">
    @foreach ($languages as $language)
        <button type="button"
                @click="activeTab = '{{ $language->code }}'"
                :class="activeTab === '{{ $language->code }}' ? 'nav-tab-active' : 'nav-tab-inactive'"
                class="nav-tab border-transparent">
            <span class="inline-flex items-center gap-2">
                <span class="uppercase text-xs font-semibold text-gray-500">{{ $language->code }}</span>
                <span>{{ ucwords($language->name) }}</span>
            </span>
        </button>
    @endforeach
</nav>
