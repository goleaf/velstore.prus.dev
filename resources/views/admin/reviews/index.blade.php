@extends('admin.layouts.admin')

@php
    $datatableLang = __('cms.datatables');
@endphp

@section('content')
    <x-admin.page-header
        :title="__('cms.product_reviews.title_manage')"
        :description="__('cms.product_reviews.index_description')"
    />

    <x-admin.card>
        <x-admin.table
            id="reviews-table"
            :columns="[
                __('cms.product_reviews.review_id'),
                __('cms.product_reviews.customer_name'),
                __('cms.product_reviews.product_name'),
                __('cms.product_reviews.rating'),
                __('cms.product_reviews.status'),
                __('cms.product_reviews.actions'),
            ]"
            class="mt-4"
        />
    </x-admin.card>

    <div id="deleteReviewDialog" class="fixed inset-0 z-50 hidden bg-gray-900/60 p-4">
        <div class="mx-auto flex h-full max-w-md items-center justify-center">
            <div class="w-full overflow-hidden rounded-lg bg-white shadow-xl">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        {{ __('cms.product_reviews.confirm_delete') }}
                    </h2>
                </div>
                <div class="px-6 py-4 text-sm text-gray-600">
                    {{ __('cms.product_reviews.delete_message') }}
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
                    <button type="button" class="btn btn-outline" data-dialog-close>
                        {{ __('cms.product_reviews.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" data-dialog-confirm>
                        {{ __('cms.product_reviews.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const $table = $('#reviews-table');

            if (! $table.length) {
                return;
            }

            const translations = {
                deleteLabel: @json(__('cms.product_reviews.delete')),
                successTitle: @json(__('cms.product_reviews.success')),
                successDelete: @json(__('cms.product_reviews.success_delete')),
                errorTitle: @json(__('cms.product_reviews.error')),
                errorDelete: @json(__('cms.product_reviews.error_delete')),
            };

            const statusTemplates = {
                active: `<span class="badge badge-success">{{ __('cms.product_reviews.active') }}</span>`,
                inactive: `<span class="badge badge-danger">{{ __('cms.product_reviews.inactive') }}</span>`,
            };

            const dataTable = $table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.reviews.data') }}",
                    type: 'GET',
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'rating', name: 'rating' },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data) {
                            return statusTemplates[data] ?? data;
                        },
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(id) {
                            return `
                                <div class="flex items-center justify-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-review" data-id="${id}">
                                        ${translations.deleteLabel}
                                    </button>
                                </div>
                            `;
                        },
                    },
                ],
                pageLength: 10,
                language: @json($datatableLang),
            });

            const dialog = document.getElementById('deleteReviewDialog');
            const cancelButton = dialog?.querySelector('[data-dialog-close]');
            const confirmButton = dialog?.querySelector('[data-dialog-confirm]');
            let reviewToDeleteId = null;

            $(document).on('click', '.btn-delete-review', (event) => {
                reviewToDeleteId = event.currentTarget.getAttribute('data-id');
                openDialog();
            });

            cancelButton?.addEventListener('click', closeDialog);

            dialog?.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    closeDialog();
                }
            });

            confirmButton?.addEventListener('click', () => {
                if (! reviewToDeleteId) {
                    return;
                }

                confirmButton.disabled = true;

                $.ajax({
                    url: '{{ route('admin.reviews.destroy', ':id') }}'.replace(':id', reviewToDeleteId),
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: (response) => {
                        if (response.success) {
                            dataTable.ajax.reload(null, false);
                            toastr.success(response.message ?? translations.successDelete, translations.successTitle);
                            closeDialog();
                        } else {
                            toastr.error(response.message ?? translations.errorDelete, translations.errorTitle);
                        }
                    },
                    error: () => {
                        toastr.error(translations.errorDelete, translations.errorTitle);
                    },
                    complete: () => {
                        confirmButton.disabled = false;
                    },
                });
            });

            function openDialog() {
                if (! dialog) {
                    return;
                }

                dialog.classList.remove('hidden');
                dialog.classList.add('flex');
            }

            function closeDialog() {
                if (! dialog) {
                    return;
                }

                dialog.classList.add('hidden');
                dialog.classList.remove('flex');
                reviewToDeleteId = null;
            }
        });
    </script>
@endsection
