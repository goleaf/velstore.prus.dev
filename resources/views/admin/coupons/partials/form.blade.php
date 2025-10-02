@php
    $formMethod = strtoupper($method ?? 'POST');
    $couponModel = $coupon ?? null;

    $expiresAtValue = old('expires_at');

    if ($expiresAtValue) {
        $expiresAtValue = \Illuminate\Support\Carbon::parse($expiresAtValue)->format('Y-m-d\\TH:i');
    } elseif ($couponModel && $couponModel->expires_at) {
        $expiresAtValue = $couponModel->expires_at->format('Y-m-d\\TH:i');
    } else {
        $expiresAtValue = '';
    }

    $typeValue = old('type', optional($couponModel)->type);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST">
    @csrf
    @if (!in_array($formMethod, ['GET', 'POST']))
        @method($formMethod)
    @endif

    <div class="mb-3">
        <label for="code" class="form-label">{{ __('cms.coupons.code') }}</label>
        <input
            type="text"
            name="code"
            id="code"
            class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', optional($couponModel)->code) }}"
            required
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="discount" class="form-label">{{ __('cms.coupons.discount') }}</label>
        <input
            type="number"
            name="discount"
            id="discount"
            class="form-control @error('discount') is-invalid @enderror"
            value="{{ old('discount', optional($couponModel)->discount) }}"
            step="0.01"
            min="0"
            required
        >
        @error('discount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">{{ __('cms.coupons.discount_hint') }}</small>
    </div>

    <div class="mb-3">
        <label for="type" class="form-label">{{ __('cms.coupons.type') }}</label>
        <select
            name="type"
            id="type"
            class="form-select @error('type') is-invalid @enderror"
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
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="expires_at" class="form-label">{{ __('cms.coupons.expires_at') }}</label>
        <input
            type="datetime-local"
            name="expires_at"
            id="expires_at"
            class="form-control @error('expires_at') is-invalid @enderror"
            value="{{ $expiresAtValue }}"
        >
        @error('expires_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">{{ __('cms.coupons.expiry_hint') }}</small>
    </div>

    <button type="submit" class="btn btn-primary">
        {{ $submitLabel ?? __('cms.coupons.save') }}
    </button>
</form>
