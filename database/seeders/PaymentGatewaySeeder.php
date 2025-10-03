<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $paypal = PaymentGateway::updateOrCreate(
            ['code' => 'paypal'],
            [
                'name' => 'PayPal',
                'description' => 'PayPal payment gateway',
                'is_active' => true,
            ]
        );

        PaymentGatewayConfig::updateOrCreate(
            [
                'gateway_id' => $paypal->id,
                'key_name' => 'client_id',
                'environment' => 'sandbox',
            ],
            [
                'key_value' => 'your-paypal-client-id',
                'is_encrypted' => true,
            ]
        );

        PaymentGatewayConfig::updateOrCreate(
            [
                'gateway_id' => $paypal->id,
                'key_name' => 'client_secret',
                'environment' => 'sandbox',
            ],
            [
                'key_value' => 'your-paypal-client-secret',
                'is_encrypted' => true,
            ]
        );

        $stripe = PaymentGateway::updateOrCreate(
            ['code' => 'stripe'],
            [
                'name' => 'Stripe',
                'description' => 'Stripe payment gateway',
                'is_active' => true,
            ]
        );

        PaymentGatewayConfig::updateOrCreate(
            [
                'gateway_id' => $stripe->id,
                'key_name' => 'public_key',
                'environment' => 'sandbox',
            ],
            [
                'key_value' => 'your-stripe-public-key',
                'is_encrypted' => false,
            ]
        );

        PaymentGatewayConfig::updateOrCreate(
            [
                'gateway_id' => $stripe->id,
                'key_name' => 'secret_key',
                'environment' => 'sandbox',
            ],
            [
                'key_value' => 'your-stripe-secret-key',
                'is_encrypted' => true,
            ]
        );
    }
}
