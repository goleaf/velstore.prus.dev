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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-xs">
                <label for="reviews-status-filter" class="form-label">{{ __('cms.product_reviews.status_filter_label') }}</label>
                <select id="reviews-status-filter" class="form-select">
                    <option value="">{{ __('cms.product_reviews.status_filter_all') }}</option>
                    <option value="approved">{{ __('cms.product_reviews.approved') }}</option>
                    <option value="pending">{{ __('cms.product_reviews.pending') }}</option>
                </select>
            </div>
        </div>

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
                viewLabel: @json(__('cms.product_reviews.view')),
                editLabel: @json(__('cms.product_reviews.edit')),
            };

            const statusTemplates = {
                approved: `<span class="badge badge-success">{{ __('cms.product_reviews.approved') }}</span>`,
                pending: `<span class="badge badge-warning">{{ __('cms.product_reviews.pending') }}</span>`,
            };

            const routes = {
                show: @json(route('admin.reviews.show', ['review' => '__REVIEW__'])),
                edit: @json(route('admin.reviews.edit', ['review' => '__REVIEW__'])),
                destroy: @json(route('admin.reviews.destroy', ['review' => '__REVIEW__'])),
            };

            const statusFilter = document.getElementById('reviews-status-filter');

            const dataTable = $table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.reviews.data') }}",
                    type: 'GET',
                    data: function(params) {
                        params.status = statusFilter?.value ?? '';
                    },
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
                                <div class="flex items-center justify-end gap-2">
                                    <a href="${routes.show.replace('__REVIEW__', id)}" class="btn btn-outline btn-sm">
                                        ${translations.viewLabel}
                                    </a>
                                    <a href="${routes.edit.replace('__REVIEW__', id)}" class="btn btn-outline-primary btn-sm">
                                        ${translations.editLabel}
                                    </a>
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

            statusFilter?.addEventListener('change', () => {
                dataTable.ajax.reload();
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
                    url: routes.destroy.replace('__REVIEW__', reviewToDeleteId),
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
