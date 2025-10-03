<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'code' => 'paypal',
                'name' => 'PayPal',
                'description' => 'Accept PayPal payments via Express Checkout.',
                'is_active' => true,
                'configs' => [
                    [
                        'key_name' => 'client_id',
                        'key_value' => 'your-paypal-sandbox-client-id',
                        'environment' => 'sandbox',
                        'is_encrypted' => false,
                    ],
                    [
                        'key_name' => 'client_secret',
                        'key_value' => 'your-paypal-sandbox-secret',
                        'environment' => 'sandbox',
                        'is_encrypted' => true,
                    ],
                    [
                        'key_name' => 'client_id',
                        'key_value' => 'your-paypal-live-client-id',
                        'environment' => 'production',
                        'is_encrypted' => false,
                    ],
                    [
                        'key_name' => 'client_secret',
                        'key_value' => 'your-paypal-live-secret',
                        'environment' => 'production',
                        'is_encrypted' => true,
                    ],
                ],
            ],
            [
                'code' => 'stripe',
                'name' => 'Stripe',
                'description' => 'Card payments and wallets powered by Stripe.',
                'is_active' => true,
                'configs' => [
                    [
                        'key_name' => 'public_key',
                        'key_value' => 'pk_test_replace_me',
                        'environment' => 'sandbox',
                        'is_encrypted' => false,
                    ],
                    [
                        'key_name' => 'secret_key',
                        'key_value' => 'sk_test_replace_me',
                        'environment' => 'sandbox',
                        'is_encrypted' => true,
                    ],
                    [
                        'key_name' => 'public_key',
                        'key_value' => 'pk_live_replace_me',
                        'environment' => 'production',
                        'is_encrypted' => false,
                    ],
                    [
                        'key_name' => 'secret_key',
                        'key_value' => 'sk_live_replace_me',
                        'environment' => 'production',
                        'is_encrypted' => true,
                    ],
                ],
            ],
            [
                'code' => 'manual_bank_transfer',
                'name' => 'Manual Bank Transfer',
                'description' => 'Provide customers with bank transfer instructions.',
                'is_active' => false,
                'configs' => [
                    [
                        'key_name' => 'account_name',
                        'key_value' => 'Velstore LLC',
                        'environment' => 'production',
                        'is_encrypted' => false,
                    ],
                    [
                        'key_name' => 'account_number',
                        'key_value' => '000111222333',
                        'environment' => 'production',
                        'is_encrypted' => false,
                    ],
                    [
                        'key_name' => 'routing_number',
                        'key_value' => '123456789',
                        'environment' => 'production',
                        'is_encrypted' => false,
                    ],
                ],
            ],
        ];

        foreach ($gateways as $gatewayData) {
            $configs = $gatewayData['configs'];
            unset($gatewayData['configs']);

            $gateway = PaymentGateway::updateOrCreate(
                ['code' => $gatewayData['code']],
                $gatewayData
            );

            foreach ($configs as $config) {
                PaymentGatewayConfig::updateOrCreate(
                    [
                        'gateway_id' => $gateway->id,
                        'key_name' => $config['key_name'],
                        'environment' => $config['environment'],
                    ],
                    [
                        'key_value' => $config['key_value'],
                        'is_encrypted' => $config['is_encrypted'],
                    ]
                );
            }
        }
    }
}
