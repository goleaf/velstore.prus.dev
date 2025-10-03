@extends('admin.layouts.admin')

@section('content')
<x-admin.page-header
    :title="__('cms.vendors.title_create')"
    :description="__('cms.vendors.create_description')"
>
    <x-admin.button-link href="{{ route('admin.vendors.index') }}" class="btn-outline">
        {{ __('cms.vendors.back_to_index') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card class="mt-6">
    <form action="{{ route('admin.vendors.store') }}" method="POST" class="grid gap-6">
        @csrf

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('cms.vendors.vendor_details_heading') }}</h3>

                <div class="grid gap-4">
                    <div>
                        <label for="name" class="form-label">{{ __('cms.vendors.vendor_name') }}</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            maxlength="255"
                            class="form-control @error('name') is-invalid @enderror"
                            autocomplete="name"
                            required
                        >
                        @error('name')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="form-label">{{ __('cms.vendors.vendor_email') }}</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            maxlength="255"
                            class="form-control @error('email') is-invalid @enderror"
                            autocomplete="email"
                            required
                        >
                        @error('email')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="form-label">{{ __('cms.vendors.phone_optional') }}</label>
                        <input
                            id="phone"
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            maxlength="20"
                            class="form-control @error('phone') is-invalid @enderror"
                            autocomplete="tel"
                            inputmode="tel"
                            placeholder="+1 555 123 4567"
                            aria-describedby="phone-help"
                        >
                        <p id="phone-help" class="form-text text-xs text-gray-500 mt-1">
                            {{ __('cms.vendors.phone_format_hint') }}
                        </p>
                        @error('phone')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('cms.vendors.account_security_heading') }}</h3>

                <div class="grid gap-4">
                    <div>
                        <label for="password" class="form-label">{{ __('cms.vendors.password') }}</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            required
                            aria-describedby="password-help"
                        >
                        <p id="password-help" class="form-text text-xs text-gray-500 mt-1">
                            {{ __('cms.vendors.password_requirements') }}
                        </p>
                        @error('password')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">{{ __('cms.vendors.confirm_password') }}</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            autocomplete="new-password"
                            required
                        >
                        @error('password_confirmation')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="form-label">{{ __('cms.vendors.status_label') }}</label>
                        <select
                            id="status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required
                        >
                            <option value="" disabled {{ old('status') ? '' : 'selected' }}>{{ __('cms.vendors.status_placeholder') }}</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'active') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <button type="submit" class="btn btn-primary">
                {{ __('cms.vendors.register_button') }}
            </button>
            <x-admin.button-link href="{{ route('admin.vendors.index') }}" class="btn-outline">
                {{ __('cms.vendors.cancel_button') }}
            </x-admin.button-link>
        </div>
    </form>
</x-admin.card>
@endsection
