@props([
    'action',
    'method' => 'POST',
    'statusOptions' => [],
    'defaultStatus' => 'active',
    'cancelRoute' => null,
])

@php
    $selectedStatus = old('status', $defaultStatus);
    $cancelRoute ??= route('admin.vendors.index');
@endphp

<x-admin.card class="mt-6">
    <form action="{{ $action }}" method="POST" class="grid gap-6">
        @csrf
        @unless (in_array(strtoupper($method), ['GET', 'POST'], true))
            @method($method)
        @endunless

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="space-y-5">
                <header class="space-y-1">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('cms.vendors.vendor_details_heading') }}</h3>
                </header>

                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <label for="name" class="form-label">{{ __('cms.vendors.vendor_name') }}</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            maxlength="255"
                            minlength="2"
                            data-trim
                            class="form-control @error('name') is-invalid @enderror"
                            autocomplete="name"
                            required
                        >
                        @error('name')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-2">
                        <label for="email" class="form-label">{{ __('cms.vendors.vendor_email') }}</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            maxlength="255"
                            data-trim
                            class="form-control @error('email') is-invalid @enderror"
                            autocomplete="email"
                            inputmode="email"
                            required
                        >
                        @error('email')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-2">
                        <label for="phone" class="form-label">{{ __('cms.vendors.phone_optional') }}</label>
                        <input
                            id="phone"
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            maxlength="20"
                            data-trim
                            pattern="^[0-9+()\\s-]{7,20}$"
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
            </section>

            <section class="space-y-5">
                <header class="space-y-1">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('cms.vendors.account_security_heading') }}</h3>
                </header>

                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <label for="password" class="form-label">{{ __('cms.vendors.password') }}</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            minlength="8"
                            pattern="^(?=.*[\\W_]).{8,}$"
                            title="{{ __('cms.vendors.password_requirements') }}"
                            required
                        >
                        <p id="password-help" class="form-text text-xs text-gray-500 mt-1">
                            {{ __('cms.vendors.password_requirements') }}
                        </p>
                        @error('password')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-2">
                        <label for="password_confirmation" class="form-label">{{ __('cms.vendors.confirm_password') }}</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            autocomplete="new-password"
                            minlength="8"
                            pattern="^(?=.*[\\W_]).{8,}$"
                            title="{{ __('cms.vendors.password_requirements') }}"
                            required
                        >
                        @error('password_confirmation')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-2">
                        <label for="status" class="form-label">{{ __('cms.vendors.status_label') }}</label>
                        <select
                            id="status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required
                        >
                            <option value="" disabled {{ $selectedStatus ? '' : 'selected' }}>{{ __('cms.vendors.status_placeholder') }}</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-sm text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <button type="submit" class="btn btn-primary">
                {{ __('cms.vendors.register_button') }}
            </button>
            <x-admin.button-link href="{{ $cancelRoute }}" class="btn-outline">
                {{ __('cms.vendors.cancel_button') }}
            </x-admin.button-link>
        </div>
    </form>
</x-admin.card>
