<?php

namespace Tests\Feature\Admin;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesAdminTestData;
use Tests\TestCase;

class PaymentGatewayManagementTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAdminTestData;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');

        $admin = \App\Models\User::factory()->create();
        $this->actingAs($admin);

        $this->seedAdminReferenceData();
    }

    public function test_index_page_displays_management_controls(): void
    {
        $response = $this->get(route('admin.payment-gateways.index'));

        $response->assertOk();
        $response->assertSeeText(__('cms.payment_gateways.quick_stats_title'));
        $response->assertSeeText(__('cms.payment_gateways.filters_title'));
        $response->assertSeeText(__('cms.payment_gateways.create_button'));
    }

    public function test_datatable_endpoint_filters_gateways_by_status(): void
    {
        $active = PaymentGateway::factory()->create(['is_active' => true]);
        $inactive = PaymentGateway::factory()->create(['is_active' => false]);

        $response = $this->getJson(route('admin.payment-gateways.getData') . '?draw=1&length=10&status=inactive');

        $response->assertOk();
        $this->assertEquals(1, $response->json('recordsFiltered'));

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertSame($inactive->id, $data[0]['id']);
        $this->assertSame($inactive->name, $data[0]['name']);

        $stats = $response->json('stats');
        $this->assertIsArray($stats);
        $this->assertEquals(1, $stats['inactive']);
        $this->assertGreaterThanOrEqual(1, $stats['active']);
    }

    public function test_admin_can_create_payment_gateway_with_multiple_configs(): void
    {
        $payload = [
            'name' => 'Mollie',
            'code' => 'mollie',
            'description' => 'Mollie payments',
            'is_active' => '1',
            'configs' => [
                [
                    'key_name' => 'api_key',
                    'key_value' => 'test_xxx',
                    'environment' => 'sandbox',
                    'is_encrypted' => '0',
                ],
                [
                    'key_name' => 'api_key',
                    'key_value' => 'live_xxx',
                    'environment' => 'production',
                    'is_encrypted' => '1',
                ],
            ],
        ];

        $response = $this->post(route('admin.payment-gateways.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('cms.payment_gateways.created'));

        $gateway = PaymentGateway::where('code', 'mollie')->first();

        $this->assertNotNull($gateway);
        $this->assertTrue($gateway->is_active);
        $this->assertCount(2, $gateway->configs);
        $this->assertSame('test_xxx', $gateway->configs->firstWhere('environment', 'sandbox')->key_value);
        $this->assertTrue((bool) $gateway->configs->firstWhere('environment', 'production')->is_encrypted);
    }

    public function test_admin_can_update_gateway_and_sync_configuration_rows(): void
    {
        $gateway = PaymentGateway::factory()->create(['code' => 'adyen', 'is_active' => true]);
        $existing = PaymentGatewayConfig::factory()->create([
            'gateway_id' => $gateway->id,
            'key_name' => 'merchant_account',
            'environment' => 'sandbox',
            'is_encrypted' => false,
        ]);
        PaymentGatewayConfig::factory()->create([
            'gateway_id' => $gateway->id,
            'key_name' => 'api_key',
            'environment' => 'production',
            'is_encrypted' => true,
        ]);

        $payload = [
            'name' => 'Adyen Updated',
            'code' => 'adyen',
            'description' => 'Updated description',
            'is_active' => '0',
            'configs' => [
                [
                    'id' => $existing->id,
                    'key_name' => 'merchant_account',
                    'key_value' => 'updated-account',
                    'environment' => 'sandbox',
                    'is_encrypted' => '0',
                ],
                [
                    'key_name' => 'checkout_api_key',
                    'key_value' => 'new-secret',
                    'environment' => 'production',
                    'is_encrypted' => '1',
                ],
            ],
        ];

        $response = $this->put(route('admin.payment-gateways.update', $gateway), $payload);

        $response->assertRedirect(route('admin.payment-gateways.edit', $gateway));
        $response->assertSessionHas('success', __('cms.payment_gateways.updated'));

        $gateway->refresh()->load('configs');
        $this->assertFalse($gateway->is_active);
        $this->assertSame('Adyen Updated', $gateway->name);
        $this->assertCount(2, $gateway->configs);
        $this->assertNull($gateway->configs()->where('key_name', 'api_key')->first());
        $this->assertSame('updated-account', $gateway->configs()->where('key_name', 'merchant_account')->first()->key_value);
        $this->assertTrue((bool) $gateway->configs()->where('key_name', 'checkout_api_key')->first()->is_encrypted);
    }

    public function test_admin_can_toggle_gateway_status(): void
    {
        $gateway = PaymentGateway::factory()->create(['is_active' => false]);

        $response = $this->patchJson(route('admin.payment-gateways.toggle', $gateway));

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $gateway->refresh();
        $this->assertTrue($gateway->is_active);
    }

    public function test_admin_can_delete_gateway_and_configs(): void
    {
        $gateway = PaymentGateway::factory()->has(PaymentGatewayConfig::factory()->count(2))->create();

        $response = $this->deleteJson(route('admin.payment-gateways.destroy', $gateway));

        $response->assertOk();
        $this->assertDatabaseMissing('payment_gateways', ['id' => $gateway->id]);
        $this->assertDatabaseMissing('payment_gateway_configs', ['gateway_id' => $gateway->id]);
    }
}
