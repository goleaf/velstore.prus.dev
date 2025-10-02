
@extends('admin.layouts.admin')

@section('content')
<div class="card mt-4">
    <div class="card-header  card-header-bg text-white">
        <h6 class="d-flex align-items-center mb-0 dt-heading">{{ __('cms.menu_items.heading') }}</h6>
    </div>
    <div class="card-body">
        <!-- Add Menu Item Button (aligned to the right) -->
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('admin.menus.items.create', $menu->id) }}" class="btn btn-primary mt-2">{{ __('cms.menu_items.add_new') }}</a>
        </div>

        <!-- Menu Items Table -->
        <table id="menu-items-table" class="table">
            <thead>
                <tr>
                    <th>{{ __('cms.menu_items.id') }}</th>
                    <th>{{ __('cms.menu_items.slug') }}</th>
                    <th>{{ __('cms.menu_items.order_number') }}</th>
                    <th>{{ __('cms.menu_items.actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<!-- Delete Menu Item Modal -->
<div class="modal fade" id="deleteMenuItemModal" tabindex="-1" aria-labelledby="deleteMenuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteMenuItemModalLabel">{{ __('cms.menu_items.massage_confirm') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">  {{ __('cms.menu_items.confirm_delete') }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cms.menu_items.massage_cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteMenuItem">{{ __('cms.menu_items.massage_delete') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- End Delete Menu Item Modal -->
@endsection

@section('js')
@php
    $datatableLang = __('cms.datatables');
@endphp

<script>
$(document).ready(function() {
    var menuId = {{ $menu->id }}; // Pass the menu ID from Blade to JavaScript
    $('#menu-items-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.menus.item.getData') }}", // Ensure quotes around route
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}" // Ensure CSRF token is sent
            },
            error: function(xhr, error, thrown) {
                console.log("AJAX Error:", xhr.responseText); // Log full response
                alert("DataTables AJAX Error: Check console for details.");
            }
        },
        columns: [
                { data: 'id', name: 'id' },
                { data: 'slug', name: 'slug' },
                { data: 'order_number', name: 'order_number' },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var editUrl = '{{ route('admin.items.edit', ':id') }}'.replace(':id', row.id);
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-edit-menu-item" data-url="${editUrl}">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-delete-menu-item" data-id="${row.id}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        `;
                    }
                }
        ],
        pageLength: 10,
        language: @json($datatableLang) // Optional: datatables language translations if any
    });

    $(document).on('click', '.btn-edit-menu-item', function() {
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    });

    $(document).on('click', '.btn-delete-menu-item', function() {
        menuItemToDeleteId = $(this).data('id');
        $('#deleteMenuItemModal').modal('show');
    });
});

let menuItemToDeleteId = null;

$('#confirmDeleteMenuItem').off('click').on('click', function() {
    if (menuItemToDeleteId !== null) {
        $.ajax({
            url: '{{ route('admin.items.destroy', ':id') }}'.replace(':id', menuItemToDeleteId),
            method: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                if (response.success) {
                    $('#menu-items-table').DataTable().ajax.reload();
                    toastr.error(response.message, "Success", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });

                    $('#deleteMenuItemModal').modal('hide');
                } else {
                    toastr.error(response.message, "Error", {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000
                    });
                }
            },
            error: function() {
                toastr.error("Error deleting menu item! Please try again.", "Error", {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: 5000
                });
                $('#deleteMenuItemModal').modal('hide');
            }
        });
    }
});

</script>
@endsection
