<?php

namespace Tests\Feature;

use App\Models\Vendor;
use Database\Seeders\VendorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminVendorManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_vendor_create_form(): void
    {
        $response = $this->get(route('admin.vendors.create'));

        $response->assertOk();
        $response->assertViewIs('admin.vendors.create');
        $response->assertViewHas('statusOptions', function ($options) {
            return is_array($options)
                && array_key_exists('active', $options)
                && array_key_exists('inactive', $options)
                && array_key_exists('banned', $options);
        });
    }

    /** @test */
    public function admin_can_store_vendor_with_valid_payload(): void
    {
        $payload = [
            'name' => ' Example Vendor ',
            'email' => 'EXAMPLE@Example.COM ',
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
            'phone' => '+1 555 123 1234',
            'status' => 'active',
        ];

        $response = $this->post(route('admin.vendors.store'), $payload);

        $response->assertRedirect(route('admin.vendors.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vendors', [
            'name' => 'Example Vendor',
            'email' => 'example@example.com',
            'phone' => '+1 555 123 1234',
            'status' => 'active',
        ]);

        $vendor = Vendor::where('email', 'example@example.com')->first();
        $this->assertNotNull($vendor);
        $this->assertTrue(Hash::check('Str0ng!Pass', $vendor->password));
    }

    /** @test */
    public function vendor_store_request_requires_valid_data(): void
    {
        $payload = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
            'phone' => 'abc123',
            'status' => 'unknown',
        ];

        $response = $this->from(route('admin.vendors.create'))
            ->post(route('admin.vendors.store'), $payload);

        $response->assertRedirect(route('admin.vendors.create'));
        $response->assertSessionHasErrors(['name', 'email', 'password', 'phone', 'status']);
    }

    /** @test */
    public function vendor_datatable_endpoint_supports_status_filtering(): void
    {
        Vendor::factory()->create(['status' => 'active', 'name' => 'Active Vendor']);
        Vendor::factory()->create(['status' => 'inactive', 'name' => 'Inactive Vendor']);

        $response = $this->getJson(route('admin.vendors.data', [
            'draw' => 1,
            'columns[0][data]' => 'id',
            'columns[1][data]' => 'name',
            'columns[2][data]' => 'email',
            'columns[3][data]' => 'phone',
            'columns[4][data]' => 'registered_at',
            'columns[5][data]' => 'status',
            'columns[6][data]' => 'action',
            'order[0][column]' => 0,
            'order[0][dir]' => 'desc',
            'start' => 0,
            'length' => 10,
            'status' => 'inactive',
        ]));

        $response->assertOk();

        $payload = $response->json();
        $this->assertNotEmpty($payload['data']);
        $this->assertSame('Inactive Vendor', $payload['data'][0]['name']);
        $this->assertEquals(1, (int) $payload['recordsFiltered']);
    }

    /** @test */
    public function vendor_seeder_creates_vendors_for_each_status(): void
    {
        $this->seed(VendorSeeder::class);

        foreach (Vendor::STATUSES as $status) {
            $this->assertDatabaseHas('vendors', ['status' => $status]);
        }
    }
}
