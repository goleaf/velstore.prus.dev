@extends('admin.layouts.admin')

@php
    $datatableLang = __('cms.datatables');

    $stats = $stats ?? [
        'total' => 0,
        'breakdown' => [
            'active' => 0,
            'inactive' => 0,
            'banned' => 0,
        ],
        'percentages' => [
            'active' => 0,
            'inactive' => 0,
            'banned' => 0,
        ],
    ];

    $breakdown = $stats['breakdown'] ?? ['active' => 0, 'inactive' => 0, 'banned' => 0];
    $percentages = $stats['percentages'] ?? ['active' => 0, 'inactive' => 0, 'banned' => 0];
    $filters = $filters ?? ['status' => '', 'search' => ''];
@endphp

@section('content')
<x-admin.page-header
    :title="__('cms.vendors.title_manage')"
    :description="__('cms.vendors.index_description')"
>
    <x-admin.button-link href="{{ route('admin.vendors.create') }}" class="btn-primary">
        {{ __('cms.vendors.add_vendor') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card>
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card
            :label="__('cms.vendors.total_vendors')"
            :value="number_format($stats['total'] ?? 0)"
            variant="slate"
        />
        <x-admin.stat-card
            :label="__('cms.vendors.active_vendors')"
            :value="number_format($breakdown['active'] ?? 0)"
            variant="emerald"
            :percentage="$percentages['active'] ?? null"
        />
        <x-admin.stat-card
            :label="__('cms.vendors.inactive_vendors')"
            :value="number_format($breakdown['inactive'] ?? 0)"
            variant="amber"
            :percentage="$percentages['inactive'] ?? null"
        />
        <x-admin.stat-card
            :label="__('cms.vendors.banned_vendors')"
            :value="number_format($breakdown['banned'] ?? 0)"
            variant="rose"
            :percentage="$percentages['banned'] ?? null"
        />
    </div>
</x-admin.card>

<x-admin.card class="mt-6" :title="__('cms.vendors.table_title')">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between mb-4">
        <div class="grid gap-3 w-full lg:grid-cols-2">
            <div class="w-full">
                <label for="vendorSearch" class="form-label text-xs uppercase tracking-wide text-slate-600">
                    {{ __('cms.messages.search') }}
                </label>
                <input
                    type="search"
                    id="vendorSearch"
                    class="form-control"
                    placeholder="{{ __('cms.sidebar.search_placeholder') }}"
                    value="{{ $filters['search'] ?? '' }}"
                    autocomplete="off"
                >
            </div>

            <div class="w-full lg:w-56">
                <label for="statusFilter" class="form-label text-xs uppercase tracking-wide text-slate-600">
                    {{ __('cms.vendors.filter_status_label') }}
                </label>
                <select id="statusFilter" class="form-select">
                    <option value="">{{ __('cms.vendors.filter_status_all') }}</option>
                    @foreach ($statusOptions ?? [] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="button" class="btn btn-outline-secondary" id="resetVendorFilters">
                {{ __('cms.categories.reset_filters') }}
            </button>
        </div>
    </div>

    <x-admin.table id="vendors-table" :columns="[
        __('cms.vendors.id'),
        __('cms.vendors.name'),
        __('cms.vendors.email'),
        __('cms.vendors.phone'),
        __('cms.vendors.registered_at'),
        __('cms.vendors.status'),
        __('cms.vendors.actions'),
    ]">
    </x-admin.table>
</x-admin.card>

<div class="modal fade" id="deleteVendorModal" tabindex="-1" aria-labelledby="deleteVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteVendorModalLabel">{{ __('cms.vendors.modal_confirm_delete_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0 text-gray-600">{{ __('cms.vendors.modal_confirm_delete_body') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('cms.vendors.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteVendor">{{ __('cms.vendors.confirm_delete_button') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const routes = {
                data: '{{ route('admin.vendors.data') }}',
                destroy: '{{ route('admin.vendors.destroy', ['id' => '__ID__']) }}',
            };

            const statusFilter = document.getElementById('statusFilter');
            const searchInput = document.getElementById('vendorSearch');
            const resetFiltersButton = document.getElementById('resetVendorFilters');

            const createDebounce = (callback, wait = 300) => {
                let timeoutId;
                return (value) => {
                    window.clearTimeout(timeoutId);
                    timeoutId = window.setTimeout(() => callback(value), wait);
                };
            };

            const datatableLanguage = {
                ...@json($datatableLang),
                emptyTable: '{{ __('cms.vendors.empty_state') }}',
            };

            const table = $('#vendors-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: routes.data,
                    type: 'GET',
                    data: function (d) {
                        d.status = statusFilter?.value ?? '';
                    },
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone', defaultContent: '—' },
                    { data: 'registered_at', name: 'registered_at', orderable: false, searchable: false, defaultContent: '—' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false },
                ],
                pageLength: 10,
                order: [[0, 'desc']],
                language: datatableLanguage,
                search: {
                    search: (searchInput?.value ?? '').trim(),
                },
            });

            const debouncedSearch = createDebounce((value) => {
                table.search(value.trim()).draw();
            });

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    debouncedSearch(searchInput.value);
                });

                searchInput.addEventListener('search', () => {
                    searchInput.value = searchInput.value.trim();
                    table.search(searchInput.value).draw();
                });
            }

            statusFilter?.addEventListener('change', () => {
                table.ajax.reload();
            });

            resetFiltersButton?.addEventListener('click', () => {
                if (statusFilter) {
                    statusFilter.value = '';
                }

                if (searchInput) {
                    searchInput.value = '';
                    table.search('');
                }

                table.ajax.reload();
            });

            const deleteModalElement = document.getElementById('deleteVendorModal');
            const deleteModal = deleteModalElement ? new bootstrap.Modal(deleteModalElement) : null;
            let vendorToDelete = null;

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-action="delete-vendor"]');
                if (! trigger) {
                    return;
                }

                vendorToDelete = trigger.getAttribute('data-vendor-id');
                if (vendorToDelete && deleteModal) {
                    deleteModal.show();
                }
            });

            const confirmDeleteButton = document.getElementById('confirmDeleteVendor');
            if (confirmDeleteButton) {
                confirmDeleteButton.addEventListener('click', () => {
                    if (! vendorToDelete) {
                        return;
                    }

                    $.ajax({
                        url: routes.destroy.replace('__ID__', vendorToDelete),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            if (response && response.success) {
                                table.ajax.reload(null, false);
                                toastr.success(response.message || '{{ __('cms.vendors.success_delete') }}');
                            } else {
                                toastr.error((response && response.message) || '{{ __('cms.vendors.error_delete') }}');
                            }

                            vendorToDelete = null;
                            deleteModal?.hide();
                        },
                        error: function() {
                            toastr.error('{{ __('cms.vendors.error_delete') }}');
                            vendorToDelete = null;
                            deleteModal?.hide();
                        }
                    });
                });
            }

            deleteModalElement?.addEventListener('hidden.bs.modal', () => {
                vendorToDelete = null;
            });
        });
    </script>
@endpush
