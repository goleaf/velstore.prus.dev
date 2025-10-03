@extends('admin.layouts.admin')

@php
    $datatableLang = __('cms.datatables');
@endphp

@section('content')
    <x-admin.page-header
        :title="__('cms.product_reviews.title_manage')"
        :description="__('cms.product_reviews.index_description')"
    />

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card
            icon="fa-solid fa-star"
            :value="number_format($metrics['average_rating'], 2)"
            :label="__('cms.product_reviews.average_rating')"
            metric-key="average-rating"
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
>>>>>>> origin/codex/refactor-admin-reviews-and-integrate-features
        />
        <x-admin.stat-card
            icon="fa-solid fa-comments"
            :value="$metrics['total']"
            :label="__('cms.product_reviews.total_reviews')"
            metric-key="total-reviews"
        />
        <x-admin.stat-card
            icon="fa-solid fa-circle-check"
            :value="$metrics['approved']"
            :label="__('cms.product_reviews.approved_reviews')"
            theme="success"
            metric-key="approved-reviews"
        />
        <x-admin.stat-card
            icon="fa-solid fa-hourglass-half"
            :value="$metrics['pending']"
            :label="__('cms.product_reviews.pending_reviews')"
            theme="warning"
            metric-key="pending-reviews"
        />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid flex-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label for="reviews-status-filter" class="form-label">{{ __('cms.product_reviews.status_filter_label') }}</label>
                        <select id="reviews-status-filter" class="form-select">
                            <option value="">{{ __('cms.product_reviews.status_filter_all') }}</option>
                            <option value="approved">{{ __('cms.product_reviews.approved') }}</option>
                            <option value="pending">{{ __('cms.product_reviews.pending') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="reviews-rating-min" class="form-label">{{ __('cms.product_reviews.rating_min_label') }}</label>
                        <input type="number" min="1" max="5" id="reviews-rating-min" class="form-input" placeholder="1">
                    </div>
                    <div>
                        <label for="reviews-rating-max" class="form-label">{{ __('cms.product_reviews.rating_max_label') }}</label>
                        <input type="number" min="1" max="5" id="reviews-rating-max" class="form-input" placeholder="5">
                    </div>
                    <div>
                        <label for="reviews-product-name" class="form-label">{{ __('cms.product_reviews.product_filter_label') }}</label>
                        <input type="text" id="reviews-product-name" class="form-input" placeholder="{{ __('cms.product_reviews.product_filter_placeholder') }}">
                    </div>
                    <div>
                        <label for="reviews-date-from" class="form-label">{{ __('cms.product_reviews.date_from_label') }}</label>
                        <input type="date" id="reviews-date-from" class="form-input">
                    </div>
                    <div>
                        <label for="reviews-date-to" class="form-label">{{ __('cms.product_reviews.date_to_label') }}</label>
                        <input type="date" id="reviews-date-to" class="form-input">
                    </div>
                    <label class="flex items-center gap-2 self-end text-sm">
                        <input type="checkbox" id="reviews-has-text" class="form-checkbox">
                        <span>{{ __('cms.product_reviews.with_comments_only') }}</span>
                    </label>
                </div>
                <div class="flex gap-3">
                    <button type="button" id="reviews-reset-filters" class="btn btn-outline">{{ __('cms.product_reviews.reset_filters') }}</button>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex gap-2">
                    <button type="button" class="btn btn-primary" data-bulk-action="approve">{{ __('cms.product_reviews.bulk_approve') }}</button>
                    <button type="button" class="btn btn-outline-primary" data-bulk-action="unapprove">{{ __('cms.product_reviews.bulk_unapprove') }}</button>
                    <button type="button" class="btn btn-outline-danger" data-bulk-action="delete">{{ __('cms.product_reviews.bulk_delete') }}</button>
                </div>
                <div class="max-w-xs">
                    <label for="reviews-search" class="form-label">{{ __('cms.product_reviews.search_label') }}</label>
                    <input type="search" id="reviews-search" class="form-input" placeholder="{{ __('cms.product_reviews.search_placeholder') }}">
                </div>
            </div>

            <x-admin.table
                id="reviews-table"
                :columns="[
                    __('cms.product_reviews.select'),
                    __('cms.product_reviews.review_id'),
                    __('cms.product_reviews.customer_name'),
                    __('cms.product_reviews.product_name'),
                    __('cms.product_reviews.rating'),
                    __('cms.product_reviews.status'),
                    __('cms.product_reviews.review_excerpt'),
                    __('cms.product_reviews.submitted_at'),
                    __('cms.product_reviews.actions'),
                ]"
                class="mt-4"
            />
        </x-admin.card>

        <div class="grid gap-6">
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900">{{ __('cms.product_reviews.rating_distribution_title') }}</h3>
                <ul class="mt-4 space-y-3" id="rating-distribution">
                    @foreach ($metrics['rating_distribution'] as $rating => $count)
                        <li class="flex items-center gap-3">
                            <span class="w-10 text-sm font-medium">{{ $rating }}★</span>
                            <div class="relative h-2 flex-1 overflow-hidden rounded bg-gray-100">
                                <div class="absolute inset-y-0 left-0 bg-primary-500" style="width: {{ $metrics['total'] > 0 ? ($count / max($metrics['total'], 1)) * 100 : 0 }}%"></div>
                            </div>
                            <span class="w-10 text-right text-sm text-gray-600">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>

            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900">{{ __('cms.product_reviews.top_products_title') }}</h3>
                <ul class="mt-4 space-y-3" id="top-reviewed-products">
                    @forelse ($topProducts as $product)
                        <li class="rounded border border-gray-100 p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $product['product_name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ __('cms.product_reviews.reviews_count_label', ['count' => $product['reviews_count']]) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ $product['avg_rating'] }} ★</p>
                                    <p class="text-xs text-gray-500">{{ __('cms.product_reviews.approved_percentage_label', ['percentage' => $product['approved_percentage']]) }}</p>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">{{ __('cms.product_reviews.no_products_summary') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>

            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900">{{ __('cms.product_reviews.recent_reviews_title') }}</h3>
                <ul class="mt-4 space-y-4" id="recent-reviews">
                    @forelse ($recentReviews as $recent)
                        <li>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">#{{ $recent['id'] }} · {{ $recent['customer'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $recent['product'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ $recent['rating'] }} ★</p>
                                    <p class="text-xs text-gray-500">{{ $recent['created_at_human'] }}</p>
                                </div>
                            </div>
                            @php
                                $badgeClass = $recent['status'] === 'approved' ? 'badge badge-success' : 'badge badge-warning';
                            @endphp
                            <span class="mt-2 inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $badgeClass }}" data-status="{{ $recent['status'] }}">
                                {{ __('cms.product_reviews.status_badge_' . $recent['status']) }}
                            </span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">{{ __('cms.product_reviews.no_recent_reviews') }}</li>
                    @endforelse
                </ul>
            </x-admin.card>
        </div>
    </div>

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
<<<<<<< HEAD
                bulkSuccess: @json(__('cms.product_reviews.bulk_action_success', ['count' => ':count'])),
                bulkEmpty: @json(__('cms.product_reviews.bulk_action_empty')),
                bulkError: @json(__('cms.product_reviews.bulk_action_error')),
=======
                toggleApprove: @json(__('cms.product_reviews.toggle_approval')),
                markApproved: @json(__('cms.product_reviews.mark_as_approved')),
                markPending: @json(__('cms.product_reviews.mark_as_pending')),
                statusUpdated: @json(__('cms.product_reviews.success_status_update')),
                statusUpdateError: @json(__('cms.product_reviews.error_status_update')),
>>>>>>> origin/codex/refactor-admin-reviews-and-integrate-features
            };

            const statusLabels = {
                approved: @json(__('cms.product_reviews.approved')),
                pending: @json(__('cms.product_reviews.pending')),
            };

            const routes = {
                show: @json(route('admin.reviews.show', ['review' => '__REVIEW__'])),
                edit: @json(route('admin.reviews.edit', ['review' => '__REVIEW__'])),
                destroy: @json(route('admin.reviews.destroy', ['review' => '__REVIEW__'])),
<<<<<<< HEAD
                metrics: @json(route('admin.reviews.metrics')),
                bulk: @json(route('admin.reviews.bulk-action')),
            };

            const statusFilter = document.getElementById('reviews-status-filter');
            const ratingMin = document.getElementById('reviews-rating-min');
            const ratingMax = document.getElementById('reviews-rating-max');
            const productName = document.getElementById('reviews-product-name');
            const dateFrom = document.getElementById('reviews-date-from');
            const dateTo = document.getElementById('reviews-date-to');
            const hasText = document.getElementById('reviews-has-text');
            const resetFilters = document.getElementById('reviews-reset-filters');
            const searchInput = document.getElementById('reviews-search');

            const bulkButtons = document.querySelectorAll('[data-bulk-action]');
=======
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
>>>>>>> origin/codex/refactor-admin-reviews-and-integrate-features

            const dataTable = $table.DataTable({
                processing: true,
                serverSide: true,
                order: [[7, 'desc']],
                ajax: {
                    url: "{{ route('admin.reviews.data') }}",
                    type: 'GET',
                    data: function(params) {
                        params.status = statusFilter?.value ?? '';
<<<<<<< HEAD
                        params.rating_min = ratingMin?.value ?? '';
                        params.rating_max = ratingMax?.value ?? '';
                        params.product_name = productName?.value ?? '';
                        params.date_from = dateFrom?.value ?? '';
                        params.date_to = dateTo?.value ?? '';
                        params.has_review = hasText?.checked ? 1 : '';
=======
                        params.keyword = searchInput?.value ?? '';
                        params.rating_min = ratingMin?.value ?? '';
                        params.rating_max = ratingMax?.value ?? '';
                        params.submitted_from = dateFrom?.value ?? '';
                        params.submitted_to = dateTo?.value ?? '';
>>>>>>> origin/codex/refactor-admin-reviews-and-integrate-features
                    },
                },
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(id) {
                            return `<input type="checkbox" class="form-checkbox" data-review-checkbox value="${id}">`;
                        },
                    },
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
                            const badgeClass = data === 'approved' ? 'badge badge-success' : 'badge badge-warning';

                            return `<span class="${badgeClass}">${statusLabels[data] ?? data}</span>`;
                        },
                    },
                    {
                        data: 'review_excerpt',
                        name: 'review',
                        orderable: false,
                        searchable: false,
                    },
                    { data: 'created_at', name: 'created_at' },
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

<<<<<<< HEAD
            [ratingMin, ratingMax, productName, dateFrom, dateTo].forEach((element) => {
                element?.addEventListener('change', () => dataTable.ajax.reload());
            });

            hasText?.addEventListener('change', () => dataTable.ajax.reload());

            resetFilters?.addEventListener('click', () => {
                statusFilter.value = '';
                ratingMin.value = '';
                ratingMax.value = '';
                productName.value = '';
                dateFrom.value = '';
                dateTo.value = '';
                if (hasText) {
                    hasText.checked = false;
                }

                dataTable.search('').draw();
                dataTable.ajax.reload();
            });

            searchInput?.addEventListener('input', (event) => {
                dataTable.search(event.target.value).draw();
            });

            bulkButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const action = button.getAttribute('data-bulk-action');
                    const selected = Array.from(document.querySelectorAll('[data-review-checkbox]:checked'))
                        .map((checkbox) => checkbox.value);

                    if (! selected.length) {
                        toastr.info(translations.bulkEmpty, translations.errorTitle);

                        return;
                    }

                    button.disabled = true;

                    $.ajax({
                        url: routes.bulk,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            action,
                            review_ids: selected,
                        },
                        success: (response) => {
                            if (response.success) {
                                dataTable.ajax.reload(null, false);
                                updateMetrics();
                                toastr.success(translations.bulkSuccess.replace(':count', response.updated), translations.successTitle);
                            } else {
                                toastr.error(translations.bulkError, translations.errorTitle);
                            }
                        },
                        error: () => {
                            toastr.error(translations.bulkError, translations.errorTitle);
                        },
                        complete: () => {
                            button.disabled = false;
                        },
                    });
                });
=======
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
>>>>>>> origin/codex/refactor-admin-reviews-and-integrate-features
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
                            updateMetrics();
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

            function updateMetrics() {
                fetch(routes.metrics, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then((response) => response.json())
                    .then((payload) => {
                        if (! payload.metrics) {
                            return;
                        }

                        updateStatCards(payload.metrics);
                        refreshDistribution(payload.metrics);
                        refreshTopProducts(payload.top_products ?? []);
                        refreshRecentReviews(payload.recent_reviews ?? []);
                    })
                    .catch(() => {});
            }

            function updateStatCards(metrics) {
                const average = document.querySelector('[data-metric="average-rating"]');
                const total = document.querySelector('[data-metric="total-reviews"]');
                const approved = document.querySelector('[data-metric="approved-reviews"]');
                const pending = document.querySelector('[data-metric="pending-reviews"]');

                if (average) average.textContent = Number(metrics.average_rating ?? 0).toFixed(2);
                if (total) total.textContent = metrics.total ?? '0';
                if (approved) approved.textContent = metrics.approved ?? '0';
                if (pending) pending.textContent = metrics.pending ?? '0';
            }

            function refreshDistribution(metrics) {
                const container = document.getElementById('rating-distribution');

                if (! container) {
                    return;
                }

                container.querySelectorAll('li').forEach((item, index) => {
                    const rating = index + 1;
                    const total = metrics.rating_distribution?.[rating] ?? 0;
                    const width = metrics.total ? (total / metrics.total) * 100 : 0;
                    const bar = item.querySelector('.bg-primary-500');
                    const count = item.querySelector('span:last-child');

                    if (bar) {
                        bar.style.width = `${width}%`;
                    }

                    if (count) {
                        count.textContent = total;
                    }
                });
            }

            function refreshTopProducts(products) {
                const container = document.getElementById('top-reviewed-products');

                if (! container) {
                    return;
                }

                container.innerHTML = '';

                if (! products.length) {
                    container.innerHTML = `<li class="text-sm text-gray-500">{{ __('cms.product_reviews.no_products_summary') }}</li>`;

                    return;
                }

                products.forEach((product) => {
                    const element = document.createElement('li');
                    element.className = 'rounded border border-gray-100 p-3';
                    element.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">${product.product_name}</p>
                                <p class="text-xs text-gray-500">{{ __('cms.product_reviews.reviews_count_label', ['count' => ':count']) }}
                                    `.replace(':count', product.reviews_count ?? 0) + `</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">${product.avg_rating} ★</p>
                                <p class="text-xs text-gray-500">{{ __('cms.product_reviews.approved_percentage_label', ['percentage' => ':percentage']) }}
                                    `.replace(':percentage', product.approved_percentage ?? 0) + `</p>
                            </div>
                        </div>
                    `;
                    container.appendChild(element);
                });
            }

            function refreshRecentReviews(reviews) {
                const container = document.getElementById('recent-reviews');

                if (! container) {
                    return;
                }

                container.innerHTML = '';

                if (! reviews.length) {
                    container.innerHTML = `<li class="text-sm text-gray-500">{{ __('cms.product_reviews.no_recent_reviews') }}</li>`;

                    return;
                }

                reviews.forEach((review) => {
                    const element = document.createElement('li');
                    const badgeClass = review.status === 'approved' ? 'badge badge-success' : 'badge badge-warning';
                    const statusLabel = statusLabels[review.status] ?? review.status;

                    element.innerHTML = `
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">#${review.id} · ${review.customer}</p>
                                <p class="text-xs text-gray-500">${review.product}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">${review.rating} ★</p>
                                <p class="text-xs text-gray-500">${review.created_at_human ?? ''}</p>
                            </div>
                        </div>
                        <span class="mt-2 inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${badgeClass}" data-status="${review.status}">
                            ${statusLabel}
                        </span>
                    `;
                    container.appendChild(element);
                });
            }
        });
    </script>
@endsection
