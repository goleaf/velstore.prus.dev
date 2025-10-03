@php
    use Illuminate\Support\Carbon;

    $couponModel = $coupon ?? null;
    $action = $formAction ?? ($action ?? route('admin.coupons.store'));
    $formMethod = strtoupper($formMethod ?? ($method ?? 'POST'));
    $submitLabel = $submitLabel ?? __('cms.coupons.save');
    $cancelUrl = $cancelUrl ?? route('admin.coupons.index');

    $codeValue = old('code', $couponModel->code ?? '');
    $discountValue = old('discount', $couponModel->discount ?? '');
    $typeValue = old('type', $couponModel->type ?? 'percentage');

    if (! in_array($typeValue, ['percentage', 'fixed'], true)) {
        $typeValue = 'percentage';
    }

    $timezone = config('app.timezone', 'UTC');
    $oldInput = session()->getOldInput() ?? [];
    $hasOldExpiresInput = array_key_exists('expires_at', $oldInput);
    $rawExpiresAt = $hasOldExpiresInput ? $oldInput['expires_at'] : null;

    $formatDateTime = static function ($value, $tz) {
        if (blank($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->timezone($tz)->format('Y-m-d\\TH:i');
        } catch (\Throwable) {
            return '';
        }
    };

    if ($hasOldExpiresInput) {
        $expiresAtValue = $formatDateTime($rawExpiresAt, $timezone);
        $shouldShowExpiry = filled($rawExpiresAt);
    } elseif ($couponModel && $couponModel->expires_at) {
        $expiresAtValue = $couponModel->expires_at->timezone($timezone)->format('Y-m-d\\TH:i');
        $shouldShowExpiry = true;
    } else {
        $expiresAtValue = '';
        $shouldShowExpiry = false;
    }
@endphp

<p class="mb-6 text-sm text-gray-500">{{ __('cms.coupons.form_description') }}</p>

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

<form
    action="{{ $action }}"
    method="POST"
    class="space-y-8"
    novalidate
    x-data="{
        type: @js($typeValue),
        showExpiry: @js($shouldShowExpiry),
        generateCode() {
            const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let generated = '';
            for (let index = 0; index < 10; index++) {
                generated += characters[Math.floor(Math.random() * characters.length)];
            }
            if (this.$refs.codeInput) {
                this.$refs.codeInput.value = generated;
                this.$refs.codeInput.focus();
            }
        },
    }"
>
    @csrf
    @if (! in_array($formMethod, ['GET', 'POST']))
        @method($formMethod)
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
            <label for="code" class="form-label">{{ __('cms.coupons.code') }}</label>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <input
                    type="text"
                    name="code"
                    id="code"
                    x-ref="codeInput"
                    value="{{ $codeValue }}"
                    class="form-control @error('code') is-invalid @enderror"
                    maxlength="255"
                    required
                >
                <button
                    type="button"
                    class="btn btn-secondary btn-sm whitespace-nowrap"
                    @click.prevent="generateCode()"
                >
                    {{ __('cms.coupons.generate_code') }}
                </button>
            </div>
            <p class="text-xs text-gray-500">{{ __('cms.coupons.generate_code_hint') }}</p>
            @error('code')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="type" class="form-label">{{ __('cms.coupons.type') }}</label>
            <select
                name="type"
                id="type"
                class="form-select @error('type') is-invalid @enderror"
                required
                @change="type = $event.target.value"
            >
                <option value="percentage" @selected($typeValue === 'percentage')>
                    {{ __('cms.coupons.type_labels.percentage') }}
                </option>
                <option value="fixed" @selected($typeValue === 'fixed')>
                    {{ __('cms.coupons.type_labels.fixed') }}
                </option>
            </select>
            <p class="text-xs text-gray-500" x-show="type === 'percentage'" x-cloak>
                {{ __('cms.coupons.discount_hint_percentage') }}
            </p>
            <p class="text-xs text-gray-500" x-show="type === 'fixed'" x-cloak>
                {{ __('cms.coupons.discount_hint_fixed') }}
            </p>
            @error('type')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="space-y-2">
        <label for="discount" class="form-label">{{ __('cms.coupons.discount') }}</label>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input
                type="number"
                name="discount"
                id="discount"
                class="form-control @error('discount') is-invalid @enderror"
                value="{{ $discountValue }}"
                step="0.01"
                min="0"
                required
            >
            <span
                class="inline-flex h-10 items-center rounded-md border border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-600"
                x-text="type === 'percentage' ? '%' : @js(__('cms.coupons.discount_fixed_suffix'))"
            ></span>
        </div>
        <p class="text-xs text-gray-500">{{ __('cms.coupons.discount_hint') }}</p>
        @error('discount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="space-y-4 border-t border-gray-200 pt-6">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">{{ __('cms.coupons.expiry_section_title') }}</h3>
            <p class="mt-1 text-xs text-gray-500">{{ __('cms.coupons.expiry_section_description') }}</p>
        </div>

        <label class="form-check inline-flex items-center gap-2">
            <input
                type="checkbox"
                class="form-check-input"
                x-model="showExpiry"
                @change="if (!showExpiry && $refs.expiresInput) { $refs.expiresInput.value = ''; }"
            >
            <span class="text-sm font-medium text-gray-700">{{ __('cms.coupons.expiry_toggle_label') }}</span>
        </label>
        <p class="text-xs text-gray-500">{{ __('cms.coupons.expiry_toggle_hint') }}</p>

        <div x-show="showExpiry" x-cloak class="space-y-2">
            <label for="expires_at" class="form-label">{{ __('cms.coupons.expires_at') }}</label>
            <input
                type="datetime-local"
                name="expires_at"
                id="expires_at"
                x-ref="expiresInput"
                class="form-control @error('expires_at') is-invalid @enderror"
                value="{{ $expiresAtValue }}"
            >
            @if ($timezone)
                <p class="text-xs text-gray-500">{{ __('cms.coupons.expiry_timezone_hint', ['timezone' => $timezone]) }}</p>
            @endif
            @error('expires_at')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
        <x-admin.button-link href="{{ $cancelUrl }}" class="btn-outline">
            {{ __('cms.coupons.back_to_list') }}
        </x-admin.button-link>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
