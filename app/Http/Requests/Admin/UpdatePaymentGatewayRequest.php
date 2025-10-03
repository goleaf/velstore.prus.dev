<?php

namespace App\Http\Requests\Admin;

use App\Models\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $configs = collect($this->input('configs', []))
            ->map(function ($config) {
                if (! is_array($config)) {
                    return $config;
                }

                $config['is_encrypted'] = filter_var($config['is_encrypted'] ?? false, FILTER_VALIDATE_BOOLEAN);

                return $config;
            })
            ->toArray();

        $this->merge([
            'is_active' => filter_var($this->input('is_active', false), FILTER_VALIDATE_BOOLEAN),
            'configs' => $configs,
        ]);
    }

    public function rules(): array
    {
        $gateway = $this->route('paymentGateway');
        $gatewayId = $gateway instanceof PaymentGateway ? $gateway->getKey() : $gateway;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_gateways', 'code')->ignore($gatewayId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'configs' => ['nullable', 'array'],
            'configs.*.id' => ['nullable', 'integer', 'exists:payment_gateway_configs,id'],
            'configs.*.key_name' => ['nullable', 'string', 'max:100'],
            'configs.*.key_value' => ['nullable', 'string'],
            'configs.*.environment' => ['nullable', Rule::in(['sandbox', 'production'])],
            'configs.*.is_encrypted' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('configs', []) as $index => $config) {
                $keyName = $config['key_name'] ?? null;
                $environment = $config['environment'] ?? null;

                if ($keyName === null && $environment === null) {
                    continue;
                }

                if ($keyName === null) {
                    $validator->errors()->add(
                        "configs.$index.key_name",
                        __('validation.required', ['attribute' => __('cms.payment_gateways.key_name')])
                    );
                }

                if ($environment === null) {
                    $validator->errors()->add(
                        "configs.$index.environment",
                        __('validation.required', ['attribute' => __('cms.payment_gateways.environment')])
                    );
                }
            }
        });
    }
}
