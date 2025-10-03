@extends('admin.layouts.admin')

@section('title', __('cms.banners.all_banners'))

@section('content')
    <x-admin.page-header :title="__('cms.banners.all_banners')">
        <x-admin.button-link href="{{ route('admin.banners.create') }}" class="btn-primary">
            {{ __('cms.banners.add_new') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <x-admin.card class="mt-6" :title="__('cms.banners.all_banners')">
        <div class="overflow-x-auto">
            <table id="banners-table" class="table w-full">
                <thead class="table-header">
                    <tr>
                        <th scope="col" class="table-header-cell">{{ __('cms.banners.id') }}</th>
                        <th scope="col" class="table-header-cell">{{ __('cms.banners.title') }}</th>
                        <th scope="col" class="table-header-cell">{{ __('cms.banners.banner_type') }}</th>
                        <th scope="col" class="table-header-cell">{{ __('cms.banners.image') }}</th>
                        <th scope="col" class="table-header-cell">{{ __('cms.banners.status') }}</th>
                        <th scope="col" class="table-header-cell">{{ __('cms.banners.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-body"></tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

@push('scripts')
    @php
        $datatableLang = __('cms.datatables');
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableElement = $('#banners-table');

            const messages = {
                confirmDelete: @json(__('cms.banners.confirm_delete')),
                successTitle: @json(__('cms.notifications.success')),
                errorTitle: @json(__('cms.notifications.error')),
                deleteErrorMessage: @json(__('cms.banners.error_delete')),
                statusLabel: @json(__('cms.banners.status')),
                statusSuccessMessage: @json(__('cms.banners.status_updated')),
                statusErrorMessage: 'Error updating banner status.',
                noImage: @json(__('cms.banners.no_image')),
                imageLabel: @json(__('cms.banners.image')),
                edit: @json(__('cms.banners.edit')),
                delete: @json(__('cms.banners.delete')),
            };

            const toggleButtonClasses = {
                base: 'toggle-status inline-flex h-6 w-11 items-center rounded-full px-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500',
                active: 'bg-primary-500 justify-end',
                inactive: 'bg-gray-300 justify-start',
            };

            const updateToggleState = (button, isActive) => {
                button.attr('data-active', isActive ? 1 : 0);
                button.removeClass(`${toggleButtonClasses.active} ${toggleButtonClasses.inactive}`);
                button.addClass(isActive ? toggleButtonClasses.active : toggleButtonClasses.inactive);
            };

            const dataTable = tableElement.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('admin.banners.data')),
                    type: 'POST',
                    data: function (d) {
                        d._token = @json(csrf_token());
                    },
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'banner_translations.title', defaultContent: '' },
                    {
                        data: 'type_badge',
                        name: 'type',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return data || '';
                        },
                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            if (!data) {
                                return `<span class="text-sm text-gray-500">${messages.noImage}</span>`;
                            }

                            if (/<img/i.test(data)) {
                                return `<div class="flex items-center justify-center">${data}</div>`;
                            }

                            return `
                                <div class="flex items-center justify-center">
                                    <img src="${data}" alt="${messages.imageLabel}"
                                         class="h-12 w-20 rounded-md object-cover border border-gray-200" />
                                </div>
                            `;
                        },
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const isActive = ['1', 'true', 'active'].includes(String(data).toLowerCase());

                            return `
                                <button type="button"
                                    class="${toggleButtonClasses.base} ${isActive ? toggleButtonClasses.active : toggleButtonClasses.inactive}"
                                    data-id="${row.id}"
                                    data-active="${isActive ? 1 : 0}">
                                    <span class="sr-only">${messages.statusLabel}</span>
                                    <span class="h-5 w-5 rounded-full bg-white shadow transition-transform"></span>
                                </button>
                            `;
                        },
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const editUrl = @json(route('admin.banners.edit', ':id')).replace(':id', row.id);

                            return `
                                <div class="flex items-center gap-2">
                                    <button type="button" class="btn btn-outline btn-sm" data-url="${editUrl}">${messages.edit}</button>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-banner" data-id="${row.id}">${messages.delete}</button>
                                </div>
                            `;
                        },
                    },
                ],
                createdRow: function (row) {
                    $(row).addClass('table-row');
                    $('td', row).addClass('table-cell align-middle text-sm text-gray-700');
                },
                language: @json($datatableLang),
                pageLength: 10,
            });

            tableElement.on('click', '.toggle-status', function () {
                const button = $(this);
                const bannerId = button.data('id');
                const currentState = Number(button.attr('data-active')) === 1;
                const nextState = currentState ? 0 : 1;

                button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

                $.ajax({
                    url: @json(route('admin.banners.updateStatus')),
                    method: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        id: bannerId,
                        status: nextState,
                    },
                })
                    .done(function (response) {
                        if (response.success) {
                            updateToggleState(button, Boolean(nextState));
                            toastr.success(response.message || messages.statusSuccessMessage, messages.successTitle, {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 4000,
                            });
                        } else {
                            toastr.error(response.message || messages.statusErrorMessage, messages.errorTitle, {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 4000,
                            });
                        }
                    })
                    .fail(function () {
                        toastr.error(messages.statusErrorMessage, messages.errorTitle, {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-top-right',
                            timeOut: 4000,
                        });
                    })
                    .always(function () {
                        button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    });
            });

            tableElement.on('click', '.btn-delete-banner', function () {
                const bannerId = $(this).data('id');

                if (!window.confirm(messages.confirmDelete)) {
                    return;
                }

                $.ajax({
                    url: @json(route('admin.banners.destroy', ':id')).replace(':id', bannerId),
                    method: 'DELETE',
                    data: {
                        _token: @json(csrf_token()),
                    },
                })
                    .done(function (response) {
                        if (response.success) {
                            dataTable.ajax.reload(null, false);
                            toastr.success(response.message, messages.successTitle, {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 4000,
                            });
                        } else {
                            toastr.error(response.message || messages.deleteErrorMessage, messages.errorTitle, {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 4000,
                            });
                        }
                    })
                    .fail(function () {
                        toastr.error(messages.deleteErrorMessage, messages.errorTitle, {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-top-right',
                            timeOut: 4000,
                        });
                    });
            });
        });
    </script>
@endpush
