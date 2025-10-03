<div class="flex items-center gap-2">
    <button type="button"
            class="btn btn-outline btn-sm"
            data-url="{{ route('admin.orders.show', $order) }}">
        {{ __('cms.customers.view_button') }}
        <span class="sr-only">{{ __('cms.orders.id') }} #{{ $order->id }}</span>
    </button>
    <button type="button"
            class="btn btn-outline-danger btn-sm"
            data-order-delete="{{ $order->id }}"
            data-order-label="{{ __('cms.orders.id') }} #{{ $order->id }}">
        {{ __('cms.orders.delete_button') }}
        <span class="sr-only">{{ __('cms.orders.id') }} #{{ $order->id }}</span>
    </button>
</div>
