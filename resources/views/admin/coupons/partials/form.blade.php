@php
    use Illuminate\Support\Carbon;

    $couponModel = $coupon ?? null;
    $cancelUrl = $cancelUrl ?? route('admin.coupons.index');

    $timezone = config('app.timezone', 'UTC');
    $oldInput = session()->getOldInput() ?? [];
    $hasOldExpiresInput = array_key_exists('expires_at', $oldInput);
    $rawExpiresAt = $hasOldExpiresInput ? $oldInput['expires_at'] : null;

    if ($expiresAtValue) {
        try {
            $expiresAtValue = \Illuminate\Support\Carbon::parse($expiresAtValue)->format('Y-m-d\\TH:i');
        } catch (\Throwable $exception) {
            $expiresAtValue = $expiresAtValue;
        }
    } elseif ($couponModel && $couponModel->expires_at) {
        $expiresAtValue = $couponModel->expires_at->timezone($timezone)->format('Y-m-d\\TH:i');
        $shouldShowExpiry = true;
    } else {
        $expiresAtValue = '';
        $shouldShowExpiry = false;
    }

    $typeValue = old('type', optional($couponModel)->type ?? 'percentage');
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-md border border-danger-200 bg-danger-50 p-4 text-sm text-danger-700">
        <p class="font-semibold">{{ __('cms.notifications.validation_error') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="space-y-6" novalidate>
    @csrf
    @if (! in_array($formMethod, ['GET', 'POST']))
        @method($formMethod)
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label for="code" class="form-label">{{ __('cms.coupons.code') }}</label>
            <input
                type="text"
                name="code"
                id="code"
                @class([
                    'form-control',
                    'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('code'),
                ])
                value="{{ old('code', optional($couponModel)->code) }}"
                required
            >
            @error('code')
                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="type" class="form-label">{{ __('cms.coupons.type') }}</label>
            <select
                name="type"
                id="type"
                @class([
                    'form-select',
                    'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('type'),
                ])
                required
            >
                <option value="percentage" {{ $typeValue === 'percentage' ? 'selected' : '' }}>
                    {{ __('cms.coupons.type_labels.percentage') }}
                </option>
                <option value="fixed" {{ $typeValue === 'fixed' ? 'selected' : '' }}>
                    {{ __('cms.coupons.type_labels.fixed') }}
                </option>
            </select>
            @error('type')
                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="discount" class="form-label">{{ __('cms.coupons.discount') }}</label>
            <input
                type="number"
                name="discount"
                id="discount"
                step="0.01"
                min="0"
                @class([
                    'form-control',
                    'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('discount'),
                ])
                value="{{ old('discount', optional($couponModel)->discount) }}"
                required
            >
            @error('discount')
                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500">{{ __('cms.coupons.discount_hint') }}</p>
        </div>

        <div>
            <label for="expires_at" class="form-label">{{ __('cms.coupons.expires_at') }}</label>
            <input
                type="datetime-local"
                name="expires_at"
                id="expires_at"
                @class([
                    'form-control',
                    'border-danger-300 focus:border-danger-500 focus:ring-danger-500' => $errors->has('expires_at'),
                ])
                value="{{ $expiresAtValue }}"
            >
            @error('expires_at')
                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500">{{ __('cms.coupons.expiry_hint') }}</p>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
        <x-admin.button-link href="{{ $cancelUrl }}" class="btn-outline">
            {{ __('cms.coupons.back_to_list') }}
        </x-admin.button-link>
        <button type="submit" class="btn btn-primary">
            {{ $submitLabel ?? __('cms.coupons.save') }}
        </button>
    </div>
</form>
