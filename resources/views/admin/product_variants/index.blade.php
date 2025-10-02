@extends('admin.layouts.admin')

@php
    $datatableLang = __('cms.datatables');
@endphp

@section('content')
    <x-admin.page-header
        :title="__('cms.product_variants.title_manage')"
        :description="__('cms.product_variants.index_description')"
    >
        <x-admin.button-link href="{{ route('admin.product_variants.create') }}" class="btn-primary">
            {{ __('cms.product_variants.add_new') }}
        </x-admin.button-link>
    </x-admin.page-header>

    @if (session('success'))
        <div class="mt-6">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <x-admin.card class="mt-6">
        <x-admin.table
            id="product-variants-table"
            :columns="[
                __('cms.product_variants.table_id'),
                __('cms.product_variants.table_product'),
                __('cms.product_variants.table_name'),
                __('cms.product_variants.table_value'),
                __('cms.product_variants.table_price'),
                __('cms.product_variants.table_stock'),
                __('cms.product_variants.table_sku'),
                __('cms.product_variants.table_actions'),
            ]"
        >
        </x-admin.table>
    </x-admin.card>

    <div class="modal fade" id="deleteVariantModal" tabindex="-1" aria-labelledby="deleteVariantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-lg shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-lg font-semibold text-gray-900" id="deleteVariantModalLabel">
                        {{ __('cms.product_variants.delete_confirm_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-gray-600">
                    {{ __('cms.product_variants.delete_confirm_message') }}
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">
                        {{ __('cms.product_variants.delete_confirm_cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteVariant">
                        {{ __('cms.product_variants.delete_confirm_accept') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const table = $('#product-variants-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.product_variants.data') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'product', name: 'product' },
                    { data: 'variant_name', name: 'variant_name' },
                    { data: 'value', name: 'value' },
                    { data: 'price', name: 'price' },
                    { data: 'stock', name: 'stock' },
                    { data: 'SKU', name: 'SKU' },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: @json($datatableLang)
            });

            $(document).on('click', '.btn-edit-variant', function () {
                const url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });

            let variantToDeleteId = null;
            const deleteModal = $('#deleteVariantModal');

            $(document).on('click', '.btn-delete-variant', function () {
                variantToDeleteId = $(this).data('id');
                deleteModal.modal('show');
            });

            $('#confirmDeleteVariant').on('click', function () {
                if (!variantToDeleteId) {
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.product_variants.destroy', ':id') }}'.replace(':id', variantToDeleteId),
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            table.ajax.reload(null, false);
                            toastr.success(
                                response.message || '{{ __('cms.product_variants.delete_success_message') }}',
                                "{{ __('cms.product_variants.success_title') }}",
                                {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-top-right",
                                    timeOut: 5000
                                }
                            );
                        } else {
                            toastr.error(
                                response.message || '{{ __('cms.product_variants.delete_error_message') }}',
                                "{{ __('cms.product_variants.error_title') }}",
                                {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: "toast-top-right",
                                    timeOut: 5000
                                }
                            );
                        }

                        deleteModal.modal('hide');
                        variantToDeleteId = null;
                    },
                    error: function () {
                        toastr.error(
                            '{{ __('cms.product_variants.delete_error_message') }}',
                            "{{ __('cms.product_variants.error_title') }}",
                            {
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-top-right",
                                timeOut: 5000
                            }
                        );
                        deleteModal.modal('hide');
                        variantToDeleteId = null;
                    }
                });
            });

            deleteModal.on('hidden.bs.modal', function () {
                variantToDeleteId = null;
            });
        });
    </script>
@endsection
