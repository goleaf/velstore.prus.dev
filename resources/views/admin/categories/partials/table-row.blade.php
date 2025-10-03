@php
    $category = $node['category'];
    $isActive = (bool) $category->status;
    $depth = (int) $node['depth'];
    $childrenCount = (int) $node['children_count'];
@endphp

<tr>
    <td class="table-cell align-top text-sm text-gray-500">#{{ $category->id }}</td>
    <td class="table-cell align-top">
        <div class="flex flex-col" style="--tree-depth: {{ $depth }}; padding-left: calc(var(--tree-depth) * 1.5rem);">
            <span class="font-medium text-gray-900">{{ $node['name'] }}</span>
            <span class="text-xs text-gray-500">{{ $category->slug }}</span>
        </div>
    </td>
    <td class="table-cell align-top text-sm text-gray-700">{{ number_format($childrenCount) }}</td>
    <td class="table-cell align-top text-sm text-gray-700">{{ number_format($category->products_count ?? 0) }}</td>
    <td class="table-cell align-top">
        <span class="badge {{ $isActive ? 'badge-success' : 'badge-danger' }}">
            {{ $isActive ? __('cms.products.status_active') : __('cms.products.status_inactive') }}
        </span>
    </td>
    <td class="table-cell align-top">
        @include('admin.categories.partials.table-row-actions', [
            'category' => $category,
            'isActive' => $isActive,
        ])
    </td>
</tr>
