@extends('admin.layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 mb-0">{{ __('cms.payment_gateways.edit_title', ['name' => $paymentGateway->name ?? __('cms.payment_gateways.not_available')]) }}</h1>
        <p class="text-muted mb-0">{{ __('cms.payment_gateways.edit_description') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('cms.payment_gateways.back_to_index') }}
        </a>
        <form action="{{ route('admin.payment-gateways.destroy', $paymentGateway) }}" method="POST" class="d-inline" id="delete-gateway-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger" data-confirm="true">
                <i class="bi bi-trash"></i> {{ __('cms.payment_gateways.delete') }}
            </button>
        </form>
    </div>
</div>

@include('admin.payment_gateways.partials.form', [
    'paymentGateway' => $paymentGateway,
    'action' => route('admin.payment-gateways.update', $paymentGateway),
    'method' => 'PUT',
    'submitLabel' => __('cms.payment_gateways.update_button'),
])
@endsection

@push('scripts')
    @include('admin.payment_gateways.partials.scripts')
    <script>
        (function () {
            'use strict';

            const deleteForm = document.getElementById('delete-gateway-form');

            if (!deleteForm) {
                return;
            }

            deleteForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (!confirm(@json(__('cms.payment_gateways.delete_message')))) {
                    return;
                }

                const formData = new FormData(deleteForm);

                fetch(deleteForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': formData.get('_token'),
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams({
                        '_method': 'DELETE',
                        '_token': formData.get('_token'),
                    }),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data && data.success) {
                            toastr.success(data.message, @json(__('cms.payment_gateways.success')));
                            window.location.href = @json(route('admin.payment-gateways.index'));
                        } else {
                            toastr.error(data?.message || @json(__('cms.payment_gateways.delete_error')), @json(__('cms.payment_gateways.error')));
                        }
                    })
                    .catch(() => {
                        toastr.error(@json(__('cms.payment_gateways.delete_error')), @json(__('cms.payment_gateways.error')));
                    });
            });
        })();
    </script>
@endpush
