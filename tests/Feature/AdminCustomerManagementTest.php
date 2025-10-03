<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        config(['app.locale' => 'en']);
    }

    public function test_admin_can_view_customer_index_with_filters(): void
    {
        $admin = User::factory()->create();
        $shop = Shop::factory()->create();
        $matchingCustomer = Customer::factory()->create([
            'name' => 'Alice Active',
            'email' => 'alice@example.com',
            'status' => 'active',
            'marketing_opt_in' => true,
            'loyalty_tier' => 'gold',
        ]);
        $matchingCustomer->shops()->sync([$shop->id]);
        Customer::factory()->inactive()->create([
            'name' => 'Ivan Idle',
            'email' => 'ivan@example.com',
            'marketing_opt_in' => false,
            'loyalty_tier' => 'bronze',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.customers.index', [
            'search' => 'Alice',
            'status' => 'active',
            'tier' => 'gold',
            'marketing' => 'opted_in',
            'shop_id' => $shop->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('admin.customers.index');
        $response->assertSee('Alice Active');
        $response->assertDontSee('Ivan Idle');
        $response->assertSeeText(__('cms.customers.metric_active'));
        $response->assertSeeText((string) $matchingCustomer->id);
        $response->assertSeeText(__('cms.customers.loyalty_tier_gold'));
        $response->assertSeeText(__('cms.customers.marketing_opted_in'));
        $response->assertSee($shop->name);
    }

    public function test_admin_can_view_create_form(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->get(route('admin.customers.create'));

        $response->assertOk();
        $response->assertViewIs('admin.customers.create');
        $response->assertSeeText(__('cms.customers.create_title'));
        $response->assertSeeText(__('cms.customers.form_section_profile'));
        $response->assertSeeText(__('cms.customers.loyalty_tier'));
        $response->assertSeeText(__('cms.customers.form_section_shops'));
    }

    public function test_admin_can_create_customer(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $shop = Shop::factory()->create();

        $payload = [
            'name' => 'Test Customer',
            'email' => 'test.customer@example.com',
            'password' => 'securePass123',
            'phone' => '+1 555 0200',
            'address' => '100 Demo Street, Test City',
            'status' => 'active',
            'loyalty_tier' => 'platinum',
            'marketing_opt_in' => '1',
            'notes' => 'Joined from in-store event.',
            'shop_ids' => [$shop->id],
        ];

        $response = $this->post(route('admin.customers.store'), $payload);

        $response->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseHas('customers', [
            'email' => 'test.customer@example.com',
            'status' => 'active',
            'loyalty_tier' => 'platinum',
            'marketing_opt_in' => true,
        ]);

        $createdCustomer = Customer::where('email', 'test.customer@example.com')->first();
        $this->assertNotNull($createdCustomer);

        $this->assertDatabaseHas('customer_shop', [
            'customer_id' => $createdCustomer->id,
            'shop_id' => $shop->id,
        ]);
    }

    public function test_customer_email_must_be_unique(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        Customer::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $payload = [
            'name' => 'Another Customer',
            'email' => 'duplicate@example.com',
            'password' => 'securePass123',
            'phone' => '+1 555 0201',
            'address' => '200 Demo Street, Test City',
            'status' => 'active',
            'loyalty_tier' => 'silver',
            'marketing_opt_in' => '0',
            'notes' => 'Duplicate email test.',
        ];

        $response = $this
            ->from(route('admin.customers.create'))
            ->post(route('admin.customers.store'), $payload);

        $response->assertRedirect(route('admin.customers.create'));
        $response->assertSessionHasErrors('email');
    }
}
