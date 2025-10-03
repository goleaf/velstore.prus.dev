@extends('admin.layouts.admin')

@section('content')
    <div class="card mt-4">
        <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('cms.customers.create_title') }}</h6>
            <button type="button" class="btn btn-light btn-sm"
                    data-url="{{ route('admin.customers.index') }}">{{ __('cms.customers.back_to_list') }}</button>
        </div>

        <div class="card-body">
            <p class="text-muted mb-4">{{ __('cms.customers.create_description') }}</p>

            <form action="{{ route('admin.customers.store') }}" method="POST" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('cms.customers.name') }}</label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" maxlength="255" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">{{ __('cms.customers.email') }}</label>
                    <input type="email" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" maxlength="255" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">{{ __('cms.customers.password') }}</label>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror" minlength="6" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">{{ __('cms.customers.phone') }}</label>
                    <input type="text" name="phone" id="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}" maxlength="20">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label">{{ __('cms.customers.address') }}</label>
                    <textarea name="address" id="address" rows="3"
                              class="form-control @error('address') is-invalid @enderror"
                              maxlength="500">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">{{ __('cms.customers.status') }}</label>
                    <select name="status" id="status"
                            class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                            {{ __('cms.customers.active') }}</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                            {{ __('cms.customers.inactive') }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success">{{ __('cms.customers.create_button') }}</button>
                    <button type="button" class="btn btn-secondary"
                            data-url="{{ route('admin.customers.index') }}">{{ __('cms.customers.cancel_button') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
