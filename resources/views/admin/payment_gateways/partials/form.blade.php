@php
    $configs = collect(old('configs', $paymentGateway->configs->map(fn ($config) => [
        'id' => $config->id,
        'key_name' => $config->key_name,
        'key_value' => $config->key_value,
        'environment' => $config->environment,
        'is_encrypted' => $config->is_encrypted,
    ])->toArray()))->values();

    if ($configs->isEmpty()) {
        $configs = collect([[
            'id' => null,
            'key_name' => '',
            'key_value' => '',
            'environment' => '',
            'is_encrypted' => false,
        ]]);
    }
@endphp

<form action="{{ $action }}" method="POST" novalidate>
    @csrf
    @isset($method)
        @method($method)
    @endisset

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>{{ __('cms.errors.whoops') }}</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="gateway-name">{{ __('cms.payment_gateways.gateway_name') }}</label>
                    <input
                        type="text"
                        id="gateway-name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $paymentGateway->name) }}"
                        placeholder="{{ __('cms.payment_gateways.name_placeholder') }}"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="gateway-code">{{ __('cms.payment_gateways.code') }}</label>
                    <input
                        type="text"
                        id="gateway-code"
                        name="code"
                        class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code', $paymentGateway->code) }}"
                        placeholder="{{ __('cms.payment_gateways.code_placeholder') }}"
                        required
                    >
                    <div class="form-text">{{ __('cms.payment_gateways.code_help') }}</div>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="gateway-description">{{ __('cms.payment_gateways.description') }}</label>
                    <textarea
                        id="gateway-description"
                        name="description"
                        rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="{{ __('cms.payment_gateways.description_placeholder') }}"
                    >{{ old('description', $paymentGateway->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="gateway-active"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', $paymentGateway->is_active))
                        >
                        <label class="form-check-label" for="gateway-active">{{ __('cms.payment_gateways.active_label') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ __('cms.payment_gateways.configurations') }}</h5>
        <button type="button" id="add-configuration" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> {{ __('cms.payment_gateways.add_configuration') }}
        </button>
    </div>

    <div id="gateway-configurations">
        @foreach ($configs as $index => $config)
            @include('admin.payment_gateways.partials.config-row', ['index' => $index, 'config' => $config])
        @endforeach
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
            {{ $submitLabel ?? __('cms.payment_gateways.save_changes') }}
        </button>
    </div>
</form>

<template id="config-row-template">
    @include('admin.payment_gateways.partials.config-row', ['index' => '__INDEX__', 'config' => [
        'id' => null,
        'key_name' => '',
        'key_value' => '',
        'environment' => '',
        'is_encrypted' => false,
    ]])
</template>
