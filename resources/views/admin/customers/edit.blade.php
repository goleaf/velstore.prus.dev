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
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $customer->status) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="loyalty_tier" class="form-label">{{ __('cms.customers.loyalty_tier') }}</label>
                    <select name="loyalty_tier" id="loyalty_tier"
                            class="form-select @error('loyalty_tier') is-invalid @enderror" required>
                        @foreach ($loyaltyTierOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('loyalty_tier', $customer->loyalty_tier) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">{{ __('cms.customers.loyalty_tier_help') }}</small>
                    @error('loyalty_tier')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <div class="form-check mt-4 pt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="marketing_opt_in"
                               name="marketing_opt_in" {{ old('marketing_opt_in', $customer->marketing_opt_in) ? 'checked' : '' }}>
                        <label class="form-check-label" for="marketing_opt_in">
                            <span class="d-block fw-semibold">{{ __('cms.customers.marketing_opt_in_label') }}</span>
                            <span class="text-muted small">{{ __('cms.customers.marketing_opt_in_help') }}</span>
                        </label>
                    </div>
                    @error('marketing_opt_in')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">{{ __('cms.customers.notes') }}</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="form-control @error('notes') is-invalid @enderror"
                              maxlength="1000"
                              placeholder="{{ __('cms.customers.notes_placeholder') }}">{{ old('notes', $customer->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success">{{ __('cms.customers.update_button') }}</button>
                    <button type="button" class="btn btn-secondary"
                            data-url="{{ route('admin.customers.index') }}">{{ __('cms.customers.cancel_button') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
