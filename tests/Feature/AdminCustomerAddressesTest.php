<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerAddressesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_address_for_customer(): void
    {
        $admin = User::factory()->create();
        $customer = Customer::factory()->create();
        $this->actingAs($admin);

        $payload = [
            'name' => 'Office',
            'phone' => '987654321',
            'address' => 'Street 2',
            'city' => 'Town',
            'postal_code' => '2000',
            'country' => 'Country',
            'is_default' => true,
        ];

        $this
            ->post(route('admin.customers.addresses.store', $customer), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id,
            'name' => 'Office',
            'is_default' => 1,
        ]);
    }

    public function test_admin_can_set_default_for_customer(): void
    {
        $admin = User::factory()->create();
        $customer = Customer::factory()->create();
        $this->actingAs($admin);

        $a = CustomerAddress::factory()->create(['customer_id' => $customer->id, 'is_default' => false]);
        $b = CustomerAddress::factory()->create(['customer_id' => $customer->id, 'is_default' => false]);

        $this
            ->post(route('admin.customers.addresses.default', [$customer, $b]))
            ->assertRedirect();

        $this->assertTrue($b->fresh()->is_default);
        $this->assertFalse($a->fresh()->is_default);
    }
}
