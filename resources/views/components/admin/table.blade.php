@props([
    'columns' => [],
])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'table w-full']) }}>
        <thead class="table-header">
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="table-header-cell">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="table-body">
            {{ $slot }}
        </tbody>
    </table>
</div>
