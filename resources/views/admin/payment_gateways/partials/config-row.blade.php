@php($rowId = $config['id'] ?? null)
<div class="card mb-3 config-row" data-config-index="{{ $index }}">
    <div class="card-body">
        <input type="hidden" name="configs[{{ $index }}][id]" value="{{ $rowId }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="config-key-{{ $index }}">{{ __('cms.payment_gateways.key_name') }}</label>
                <input
                    type="text"
                    class="form-control @error("configs.$index.key_name") is-invalid @enderror"
                    id="config-key-{{ $index }}"
                    name="configs[{{ $index }}][key_name]"
                    value="{{ old("configs.$index.key_name", $config['key_name'] ?? '') }}"
                    placeholder="{{ __('cms.payment_gateways.key_name_placeholder') }}"
                >
                @error("configs.$index.key_name")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="config-value-{{ $index }}">{{ __('cms.payment_gateways.key_value') }}</label>
                <input
                    type="text"
                    class="form-control @error("configs.$index.key_value") is-invalid @enderror"
                    id="config-value-{{ $index }}"
                    name="configs[{{ $index }}][key_value]"
                    value="{{ old("configs.$index.key_value", $config['key_value'] ?? '') }}"
                    placeholder="{{ __('cms.payment_gateways.key_value_placeholder') }}"
                >
                <div class="form-text">{{ __('cms.payment_gateways.key_value_hint') }}</div>
                @error("configs.$index.key_value")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-2">
                <label class="form-label" for="config-environment-{{ $index }}">{{ __('cms.payment_gateways.environment') }}</label>
                <select
                    class="form-select @error("configs.$index.environment") is-invalid @enderror"
                    id="config-environment-{{ $index }}"
                    name="configs[{{ $index }}][environment]"
                >
                    <option value="">{{ __('cms.payment_gateways.environment_placeholder') }}</option>
                    <option value="sandbox" @selected(old("configs.$index.environment", $config['environment'] ?? '') === 'sandbox')>{{ __('cms.payment_gateways.sandbox') }}</option>
                    <option value="production" @selected(old("configs.$index.environment", $config['environment'] ?? '') === 'production')>{{ __('cms.payment_gateways.production') }}</option>
                </select>
                @error("configs.$index.environment")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-2">
                <div class="form-check mb-2">
                    <input
                        type="hidden"
                        name="configs[{{ $index }}][is_encrypted]"
                        value="0"
                    >
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="config-encrypted-{{ $index }}"
                        name="configs[{{ $index }}][is_encrypted]"
                        value="1"
                        @checked((bool) old("configs.$index.is_encrypted", $config['is_encrypted'] ?? false))
                    >
                    <label class="form-check-label" for="config-encrypted-{{ $index }}">{{ __('cms.payment_gateways.encrypted') }}</label>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm w-100 btn-remove-config">{{ __('cms.payment_gateways.remove_configuration') }}</button>
            </div>
        </div>
    </div>
</div>
