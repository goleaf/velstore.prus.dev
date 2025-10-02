@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('cms.coupons.create_title') }}</h6>
            <button type="button" class="btn btn-light btn-sm"
                    data-url="{{ route('admin.coupons.index') }}">{{ __('cms.coupons.back_to_list') }}</button>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $expiresValue = old('expires_at') ? \Carbon\Carbon::parse(old('expires_at'))->format('Y-m-d\TH:i') : '';
            @endphp

            <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="code" class="form-label">{{ __('cms.coupons.code') }}</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label for="discount" class="form-label">{{ __('cms.coupons.discount') }}</label>
                    <input type="number" name="discount" id="discount" class="form-control" value="{{ old('discount') }}"
                           step="0.01" min="0" required>
                    <small class="text-muted">{{ __('cms.coupons.discount_hint') }}</small>
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">{{ __('cms.coupons.type') }}</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>
                            {{ __('cms.coupons.type_labels.percentage') }}</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>
                            {{ __('cms.coupons.type_labels.fixed') }}</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="expires_at" class="form-label">{{ __('cms.coupons.expires_at') }}</label>
                    <input type="datetime-local" name="expires_at" id="expires_at" class="form-control"
                           value="{{ $expiresValue }}">
                    <small class="text-muted">{{ __('cms.coupons.expiry_hint') }}</small>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('cms.coupons.save') }}</button>
            </form>
        </div>
    </div>
@endsection
