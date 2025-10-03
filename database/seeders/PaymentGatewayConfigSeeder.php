<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Database\Seeder;

class PaymentGatewayConfigSeeder extends Seeder
{
    public function run(): void
    {
        $paypal = PaymentGateway::where('code', 'paypal')->first();
        $stripe = PaymentGateway::where('code', 'stripe')->first();

        if ($paypal) {
            PaymentGatewayConfig::updateOrCreate(
                ['gateway_id' => $paypal->id, 'key_name' => 'client_id', 'environment' => 'sandbox'],
                ['key_value' => env('PAYPAL_CLIENT_ID', 'your-paypal-sandbox-client-id'), 'is_encrypted' => false]
            );

            PaymentGatewayConfig::updateOrCreate(
                ['gateway_id' => $paypal->id, 'key_name' => 'client_secret', 'environment' => 'sandbox'],
                ['key_value' => env('PAYPAL_CLIENT_SECRET', 'your-paypal-sandbox-secret'), 'is_encrypted' => true]
            );
        }

        if ($stripe) {
            PaymentGatewayConfig::updateOrCreate(
                ['gateway_id' => $stripe->id, 'key_name' => 'secret_key', 'environment' => 'sandbox'],
                ['key_value' => env('STRIPE_SECRET', 'sk_test_replace_me'), 'is_encrypted' => true]
            );

            PaymentGatewayConfig::updateOrCreate(
                ['gateway_id' => $stripe->id, 'key_name' => 'public_key', 'environment' => 'sandbox'],
                ['key_value' => env('STRIPE_PUBLIC', 'pk_test_replace_me'), 'is_encrypted' => false]
            );
        }
    }
}
