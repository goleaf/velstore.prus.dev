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
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 lg:max-w-4xl lg:grid-cols-3">
                <div>
                    <label for="reviews-search" class="form-label">{{ __('cms.product_reviews.search_label') }}</label>
                    <input id="reviews-search" type="search" class="form-input" placeholder="{{ __('cms.product_reviews.search_placeholder') }}">
                </div>

                <div>
                    <label for="reviews-status-filter" class="form-label">{{ __('cms.product_reviews.status_filter_label') }}</label>
                    <select id="reviews-status-filter" class="form-select">
                        <option value="">{{ __('cms.product_reviews.status_filter_all') }}</option>
                        <option value="approved">{{ __('cms.product_reviews.approved') }}</option>
                        <option value="pending">{{ __('cms.product_reviews.pending') }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="reviews-rating-min" class="form-label">{{ __('cms.product_reviews.rating_min_label') }}</label>
                        <select id="reviews-rating-min" class="form-select">
                            <option value="0">{{ __('cms.product_reviews.rating_any') }}</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ trans_choice('cms.product_reviews.rating_value', $i, ['value' => $i]) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="reviews-rating-max" class="form-label">{{ __('cms.product_reviews.rating_max_label') }}</label>
                        <select id="reviews-rating-max" class="form-select">
                            <option value="5">{{ __('cms.product_reviews.rating_any') }}</option>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ trans_choice('cms.product_reviews.rating_value', $i, ['value' => $i]) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div>
                    <label for="reviews-date-from" class="form-label">{{ __('cms.product_reviews.submitted_from_label') }}</label>
                    <input id="reviews-date-from" type="date" class="form-input">
                </div>

                <div>
                    <label for="reviews-date-to" class="form-label">{{ __('cms.product_reviews.submitted_to_label') }}</label>
                    <input id="reviews-date-to" type="date" class="form-input">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="reviews-clear-filters" type="button" class="btn btn-outline">
                    {{ __('cms.product_reviews.clear_filters') }}
                </button>
            </div>
        </div>

        <x-admin.table
            id="reviews-table"
            :columns="[
                __('cms.product_reviews.review_id'),
                __('cms.product_reviews.customer_name'),
                __('cms.product_reviews.product_name'),
                __('cms.product_reviews.rating'),
                __('cms.product_reviews.review_excerpt_column'),
                __('cms.product_reviews.submitted_at'),
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
                toggleApprove: @json(__('cms.product_reviews.toggle_approval')),
                markApproved: @json(__('cms.product_reviews.mark_as_approved')),
                markPending: @json(__('cms.product_reviews.mark_as_pending')),
                statusUpdated: @json(__('cms.product_reviews.success_status_update')),
                statusUpdateError: @json(__('cms.product_reviews.error_status_update')),
            };

            const statusTemplates = {
                approved: `<span class="badge badge-success">{{ __('cms.product_reviews.approved') }}</span>`,
                pending: `<span class="badge badge-warning">{{ __('cms.product_reviews.pending') }}</span>`,
            };

            const routes = {
                show: @json(route('admin.reviews.show', ['review' => '__REVIEW__'])),
                edit: @json(route('admin.reviews.edit', ['review' => '__REVIEW__'])),
                destroy: @json(route('admin.reviews.destroy', ['review' => '__REVIEW__'])),
                approval: @json(route('admin.reviews.approval', ['review' => '__REVIEW__'])),
            };

            const statusFilter = document.getElementById('reviews-status-filter');
            const searchInput = document.getElementById('reviews-search');
            const ratingMin = document.getElementById('reviews-rating-min');
            const ratingMax = document.getElementById('reviews-rating-max');
            const dateFrom = document.getElementById('reviews-date-from');
            const dateTo = document.getElementById('reviews-date-to');
            const clearFiltersButton = document.getElementById('reviews-clear-filters');
            let searchDebounce;

            const dataTable = $table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.reviews.data') }}",
                    type: 'GET',
                    data: function(params) {
                        params.status = statusFilter?.value ?? '';
                        params.keyword = searchInput?.value ?? '';
                        params.rating_min = ratingMin?.value ?? '';
                        params.rating_max = ratingMax?.value ?? '';
                        params.submitted_from = dateFrom?.value ?? '';
                        params.submitted_to = dateTo?.value ?? '';
                    },
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'rating', name: 'rating' },
                    { data: 'review_excerpt', name: 'review', orderable: false, searchable: false },
                    { data: 'submitted_at', name: 'submitted_at' },
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
                        render: function(id, type, row) {
                            const isApproved = Boolean(row.is_approved);
                            const approvalLabel = isApproved ? translations.markPending : translations.markApproved;
                            return `
                                <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:justify-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="${routes.show.replace('__REVIEW__', id)}" class="btn btn-outline btn-sm">
                                            ${translations.viewLabel}
                                        </a>
                                        <a href="${routes.edit.replace('__REVIEW__', id)}" class="btn btn-outline-primary btn-sm">
                                            ${translations.editLabel}
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm btn-toggle-approval" data-id="${id}" data-approved="${isApproved ? 1 : 0}">
                                            ${approvalLabel}
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-review" data-id="${id}">
                                            ${translations.deleteLabel}
                                        </button>
                                    </div>
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

            ratingMin?.addEventListener('change', () => {
                dataTable.ajax.reload();
            });

            ratingMax?.addEventListener('change', () => {
                dataTable.ajax.reload();
            });

            dateFrom?.addEventListener('change', () => {
                dataTable.ajax.reload();
            });

            dateTo?.addEventListener('change', () => {
                dataTable.ajax.reload();
            });

            searchInput?.addEventListener('input', () => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => {
                    dataTable.ajax.reload();
                }, 300);
            });

            clearFiltersButton?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (statusFilter) statusFilter.value = '';
                if (ratingMin) ratingMin.value = '0';
                if (ratingMax) ratingMax.value = '5';
                if (dateFrom) dateFrom.value = '';
                if (dateTo) dateTo.value = '';
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

            $(document).on('click', '.btn-toggle-approval', (event) => {
                const button = event.currentTarget;
                const reviewId = button.getAttribute('data-id');
                const currentApproved = button.getAttribute('data-approved') === '1';

                if (! reviewId) {
                    return;
                }

                button.disabled = true;

                $.ajax({
                    url: routes.approval.replace('__REVIEW__', reviewId),
                    method: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_approved: currentApproved ? 0 : 1,
                    },
                    success: (response) => {
                        if (response?.success) {
                            toastr.success(response.message ?? translations.statusUpdated, translations.successTitle);
                            dataTable.ajax.reload(null, false);
                        } else {
                            toastr.error(translations.statusUpdateError, translations.errorTitle);
                        }
                    },
                    error: () => {
                        toastr.error(translations.statusUpdateError, translations.errorTitle);
                    },
                    complete: () => {
                        button.disabled = false;
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
