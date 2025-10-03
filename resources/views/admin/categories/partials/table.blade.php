@php
    $categories = $categories ?? [];
@endphp

<x-admin.table :columns="[
    __('cms.categories.id'),
    __('cms.categories.name'),
    __('cms.categories.subcategories'),
    __('cms.categories.products_count'),
    __('cms.categories.status'),
    __('cms.categories.action'),
]">
    @forelse ($categories as $node)
        @include('admin.categories.partials.table-row', [
            'node' => $node,
        ])
    @empty
        <tr>
            <td colspan="6" class="table-cell text-center text-gray-500 py-6">
                {{ __('cms.categories.empty_state') }}
            </td>
        </tr>
    @endforelse
</x-admin.table>
