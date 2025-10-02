<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAddressesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_addresses_page(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer, 'customer');

        $response = $this->get(route('customer.addresses.index'));
        $response->assertStatus(200);
    }

    public function test_customer_can_create_address_and_set_default(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer, 'customer');

        $payload = [
            'name' => 'Home',
            'phone' => '123456789',
            'address' => 'Street 1',
            'city' => 'City',
            'postal_code' => '1000',
            'country' => 'Country',
            'is_default' => true,
        ];

        $this
            ->post(route('customer.addresses.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id,
            'name' => 'Home',
            'is_default' => 1,
        ]);
    }

    public function test_customer_can_update_address_and_switch_default(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer, 'customer');

        $a = CustomerAddress::factory()->create(['customer_id' => $customer->id, 'is_default' => true]);
        $b = CustomerAddress::factory()->create(['customer_id' => $customer->id, 'is_default' => false]);

        $this->put(route('customer.addresses.update', $b), [
            'name' => $b->name,
            'phone' => $b->phone,
            'address' => $b->address,
            'city' => $b->city,
            'postal_code' => $b->postal_code,
            'country' => $b->country,
            'is_default' => true,
        ])->assertRedirect();

        $this->assertTrue($b->fresh()->is_default);
        $this->assertFalse($a->fresh()->is_default);
    }

    public function test_customer_can_delete_address(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer, 'customer');

        $a = CustomerAddress::factory()->create(['customer_id' => $customer->id]);

        $this
            ->delete(route('customer.addresses.destroy', $a))
            ->assertRedirect();

        $this->assertDatabaseMissing('customer_addresses', ['id' => $a->id]);
    }
}
