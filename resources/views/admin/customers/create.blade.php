@extends('admin.layouts.admin')

@section('content')
    <x-admin.page-header
        :title="__('cms.customers.create_title')"
        :description="__('cms.customers.create_description')"
    >
        <x-admin.button-link href="{{ route('admin.customers.index') }}" class="btn-outline">
            {{ __('cms.customers.back_to_list') }}
        </x-admin.button-link>
    </x-admin.page-header>

    <x-admin.card class="mt-6">
        <form action="{{ route('admin.customers.store') }}" method="POST" class="grid gap-6">
            @csrf

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('cms.customers.form_section_profile') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('cms.customers.form_section_profile_hint') }}
                        </p>
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label for="name" class="form-label">{{ __('cms.customers.name') }}</label>
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
                            <label for="email" class="form-label">{{ __('cms.customers.email') }}</label>
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
                            <label for="phone" class="form-label">{{ __('cms.customers.phone') }}</label>
                            <input
                                id="phone"
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                                maxlength="20"
                                class="form-control @error('phone') is-invalid @enderror"
                                autocomplete="tel"
                                placeholder="+1 555 0100"
                            >
                            @error('phone')
                                <p class="text-sm text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            {{ __('cms.customers.form_section_account') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('cms.customers.form_section_account_hint') }}
                        </p>
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label for="password" class="form-label">{{ __('cms.customers.password') }}</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                minlength="6"
                                required
                            >
                            @error('password')
                                <p class="text-sm text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="form-label">{{ __('cms.customers.status') }}</label>
                            <select
                                id="status"
                                name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required
                            >
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

                        <div>
                            <label for="loyalty_tier" class="form-label">{{ __('cms.customers.loyalty_tier') }}</label>
                            <select
                                id="loyalty_tier"
                                name="loyalty_tier"
                                class="form-select @error('loyalty_tier') is-invalid @enderror"
                                required
                            >
                                @foreach ($loyaltyTierOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('loyalty_tier', 'bronze') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('cms.customers.loyalty_tier_help') }}</p>
                            @error('loyalty_tier')
                                <p class="text-sm text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address" class="form-label">{{ __('cms.customers.address') }}</label>
                            <textarea
                                id="address"
                                name="address"
                                rows="4"
                                maxlength="500"
                                class="form-textarea @error('address') is-invalid @enderror"
                                placeholder="{{ __('cms.customers.address_placeholder') }}"
                            >{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-sm text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <label class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="marketing_opt_in"
                                    value="1"
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    @checked(old('marketing_opt_in', false))
                                >
                                <span class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">{{ __('cms.customers.marketing_opt_in_label') }}</span>
                                    <span class="text-xs text-gray-500">{{ __('cms.customers.marketing_opt_in_help') }}</span>
                                </span>
                            </label>
                            @error('marketing_opt_in')
                                <p class="text-sm text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="notes" class="form-label">{{ __('cms.customers.notes') }}</label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                maxlength="1000"
                                class="form-textarea @error('notes') is-invalid @enderror"
                                placeholder="{{ __('cms.customers.notes_placeholder') }}"
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-sm text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="submit" class="btn btn-primary">
                    {{ __('cms.customers.create_button') }}
                </button>
                <x-admin.button-link href="{{ route('admin.customers.index') }}" class="btn-outline">
                    {{ __('cms.customers.cancel_button') }}
                </x-admin.button-link>
            </div>
        </form>
    </x-admin.card>
@endsection
