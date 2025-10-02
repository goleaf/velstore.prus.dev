@extends('themes.xylo.layouts.master')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold mb-6">{{ __('cms.customers.addresses') }}</h1>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            @forelse($addresses as $addr)
                <div class="border rounded p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium">{{ $addr->name }}</p>
                            <p class="text-sm text-gray-600">{{ $addr->phone ?? __('cms.customers.not_available') }}</p>
                            <p class="mt-1">{{ $addr->address ?? __('cms.customers.not_available') }}</p>
                            @php
                                $location = collect([$addr->city, $addr->postal_code, $addr->country])->filter()->implode(', ');
                            @endphp
                            <p class="text-sm text-gray-600">{{ $location !== '' ? $location : __('cms.customers.not_available') }}</p>
                        </div>
                        @if ($addr->is_default)
                            <span
                                  class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ __('cms.customers.default') }}</span>
                        @endif
                    </div>
                    <div class="flex gap-2 mt-3">
                        <form method="POST" action="{{ route('customer.addresses.default', $addr) }}">
                            @csrf
                            <button class="px-3 py-1 border rounded">{{ __('cms.customers.set_default') }}</button>
                        </form>
                        <form method="POST" action="{{ route('customer.addresses.destroy', $addr) }}"
                              onsubmit="return confirm('{{ __('cms.customers.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button
                                    class="px-3 py-1 border border-red-600 text-red-700 rounded">{{ __('cms.customers.delete') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-600">{{ __('cms.customers.no_addresses') }}</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('customer.addresses.store') }}"
              class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <input class="border rounded px-3 py-2" name="name" placeholder="{{ __('cms.customers.address_name') }}"
                   required>
            <input class="border rounded px-3 py-2" name="phone" placeholder="{{ __('cms.customers.phone') }}" required>
            <input class="border rounded px-3 py-2 md:col-span-2" name="address"
                   placeholder="{{ __('cms.customers.address') }}" required>
            <input class="border rounded px-3 py-2" name="city" placeholder="{{ __('cms.customers.city') }}" required>
            <input class="border rounded px-3 py-2" name="postal_code" placeholder="{{ __('cms.customers.postal_code') }}"
                   required>
            <input class="border rounded px-3 py-2" name="country" placeholder="{{ __('cms.customers.country') }}"
                   required>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" class="w-4 h-4">
                <span>{{ __('cms.customers.set_as_default') }}</span>
            </label>
            <div>
                <button class="px-4 py-2 bg-black text-white rounded">{{ __('cms.customers.add_address') }}</button>
            </div>
        </form>
    </div>
@endsection
