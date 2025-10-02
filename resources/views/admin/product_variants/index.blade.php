{{--
@extends('admin.layouts.admin')

@section('content')
<div class="container">
    <h2>Product Variants</h2>

    <!-- Display success message if available -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-url="{{ route('admin.product_variants.create') }}">Create New Variant</button>
    </div>

    <!-- Product Variants Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Variant Name</th>
                <th>Variant Value</th>
                <th>Price</th>
                <th>Stock</th>
                <th>SKU</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productVariants as $productVariant)
                <tr>
                    <td>
                        {{ $productVariant->product->translations->first()->name ?? 'Unknown Product' }} <!-- Display the product's name translation -->
                    </td>
                    <td>
                        @foreach ($languages as $language)
                            @php
                                $translation = $productVariant->translations->where('locale', $language->code)->first();
                            @endphp
                            <strong>{{ ucfirst($language->code) }}:</strong> 
                            {{ $translation->name ?? 'N/A' }}<br>
                        @endforeach
                    </td>
                    <td>{{ $productVariant->value }}</td>
                    <td>{{ $productVariant->price }}</td>
                    <td>{{ $productVariant->stock }}</td>
                    <td>{{ $productVariant->SKU }}</td>
                    <td>
                        <a href="{{ route('admin.product_variants.edit', $productVariant->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        
                        <!-- Delete button with confirmation -->
                        <form action="{{ route('admin.product_variants.destroy', $productVariant->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this variant?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    {{ $productVariants->links() }}
</div>
@endsection

--}}





@extends('admin.layouts.admin')

@section('content')
    <div class="container">
        <h2>Product Variants</h2>

        <!-- Display success message if available -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-url="{{ route('admin.product_variants.create') }}">Create New
                Variant</button>
        </div>

        <!-- Product Variants Table -->
        <table id="product-variants-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th> <!-- Add the ID column -->
                    <th>Product</th>
                    <th>Variant Name</th>
                    <th>Variant Value</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>SKU</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated via DataTables -->
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="deleteVariantModal" tabindex="-1" aria-labelledby="deleteVariantModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteVariantModalLabel">Delete Product Variant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this product variant?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteVariant">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#product-variants-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.product_variants.data') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'variant_name',
                        name: 'variant_name'
                    },
                    {
                        data: 'value',
                        name: 'value'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'stock',
                        name: 'stock'
                    },
                    {
                        data: 'SKU',
                        name: 'SKU'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        $(document).on('click', '.btn-edit-variant', function() {
            const url = $(this).data('url');
            if (url) {
                window.location.href = url;
            }
        });

        let variantToDeleteId = null;

        $(document).on('click', '.btn-delete-variant', function() {
            variantToDeleteId = $(this).data('id');
            $('#deleteVariantModal').modal('show');
        });

        $(document).on('click', '#confirmDeleteVariant', function() {
            if (variantToDeleteId === null) {
                return;
            }

            $.ajax({
                url: '{{ route('admin.product_variants.destroy', ':id') }}'.replace(':id',
                    variantToDeleteId),
                method: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        $('#product-variants-table').DataTable().ajax.reload();
                        toastr.error(response.message, "Deleted", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                    } else {
                        toastr.error(response.message || 'Error deleting product variant.', "Error", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-top-right",
                            timeOut: 5000
                        });
                    }

                    $('#deleteVariantModal').modal('hide');
                },
                error: function() {
                    toastr.error('Error deleting product variant.', "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                    $('#deleteVariantModal').modal('hide');
                }
            });
        });
    </script>
@endsection
