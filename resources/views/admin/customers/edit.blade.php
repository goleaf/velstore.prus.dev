@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('Edit Customer') }}</h6>
            <button type="button" class="btn btn-light btn-sm"
                    data-url="{{ route('admin.customers.index') }}">{{ __('cms.customers.back_to_list') }}</button>
        </div>

        <div class="card-body">
            <p class="text-muted mb-4">{{ __('Update the customer details below.') }}</p>

            <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                @php
                    $selectedShopIds = collect(old('shop_ids', $customer->shops->pluck('id')->all()))
                        ->map(fn ($id) => (int) $id)
                        ->all();
                @endphp

                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('cms.customers.name') }}</label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $customer->name) }}" maxlength="255" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">{{ __('cms.customers.email') }}</label>
                    <input type="email" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $customer->email) }}" maxlength="255" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">{{ __('cms.customers.password') }}</label>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror" minlength="6">
                    <small class="text-muted">{{ __('Leave blank to keep the current password.') }}</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">{{ __('cms.customers.phone') }}</label>
                    <input type="text" name="phone" id="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $customer->phone) }}" maxlength="20">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label">{{ __('cms.customers.address') }}</label>
                    <textarea name="address" id="address" rows="3"
                              class="form-control @error('address') is-invalid @enderror"
                              maxlength="500">{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">{{ __('cms.customers.status') }}</label>
                    <select name="status" id="status"
                            class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>
                            {{ __('cms.customers.active') }}</option>
                        <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>
                            {{ __('cms.customers.inactive') }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <p class="form-label mb-2">{{ __('cms.customers.form_section_shops') }}</p>
                    <div class="row g-2">
                        @forelse ($shops as $shop)
                            <div class="col-md-4">
                                <div class="form-check border rounded p-3 h-100">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="shop_ids[]"
                                        value="{{ $shop->id }}"
                                        id="shop-{{ $shop->id }}"
                                        {{ in_array($shop->id, $selectedShopIds, true) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label ms-2" for="shop-{{ $shop->id }}">
                                        <span class="d-block fw-semibold">{{ $shop->name }}</span>
                                        <span class="badge {{ $shop->status === 'active' ? 'bg-success' : 'bg-secondary' }} mt-2">
                                            {{ $shop->status === 'active' ? __('cms.customers.shop_status_active') : __('cms.customers.shop_status_inactive') }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info mb-0">{{ __('cms.customers.no_shops_available') }}</div>
                            </div>
                        @endforelse
                    </div>
                    @error('shop_ids')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                    @error('shop_ids.*')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success">{{ __('Update Customer') }}</button>
                    <button type="button" class="btn btn-secondary"
                            data-url="{{ route('admin.customers.index') }}">{{ __('cms.customers.cancel_button') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
