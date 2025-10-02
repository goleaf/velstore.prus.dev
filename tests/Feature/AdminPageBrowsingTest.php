<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Feature\Concerns\CreatesAdminTestData;

class AdminPageBrowsingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAdminTestData;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        config(['app.locale' => 'en']);

        $admin = \App\Models\User::factory()->create();
        $this->actingAs($admin);

        $this->seedAdminReferenceData();
    }

    public function test_admin_page_routes_are_accessible(): void
    {
        $routes = [
            ['admin.dashboard', [], 'admin.home'],
            ['admin.categories.index', [], 'admin.categories.index'],
            ['admin.categories.create', [], 'admin.categories.create'],
            ['admin.categories.edit', ['category' => $this->category->id], 'admin.categories.edit'],
            ['admin.products.index', [], 'admin.products.index'],
            ['admin.products.create', [], 'admin.products.create'],
            ['admin.products.edit', ['product' => $this->product->id], 'admin.products.edit'],
            ['admin.brands.index', [], 'admin.brands.index'],
            ['admin.brands.create', [], 'admin.brands.create'],
            ['admin.brands.edit', ['brand' => $this->brand->id], 'admin.brands.edit'],
            ['admin.profile.show', [], 'admin.profile.show'],
            ['admin.menus.index', [], 'admin.menus.index'],
            ['admin.menus.create', [], 'admin.menus.create'],
            ['admin.menus.edit', ['menu' => $this->menu->id], 'admin.menus.edit'],
            ['admin.menus.items.index', ['menu' => $this->menu->id], 'admin.menu_items.index'],
            ['admin.menus.items.create', ['menu' => $this->menu->id], 'admin.menu_items.create'],
            ['admin.menus.items.edit', ['item' => $this->menuItem->id], 'admin.menu_items.edit'],
            ['admin.menus.item.index', [], 'admin.menu_items.index'],
            ['admin.banners.index', [], 'admin.banners.index'],
            ['admin.banners.create', [], 'admin.banners.create'],
            ['admin.banners.edit', ['banner' => $this->banner->id], 'admin.banners.edit'],
            ['admin.social-media-links.index', [], 'admin.social-media-links.index'],
            ['admin.social-media-links.create', [], 'admin.social-media-links.create'],
            ['admin.social-media-links.edit', ['social_media_link' => $this->socialLink->id], 'admin.social-media-links.edit'],
            ['admin.orders.index', [], 'admin.orders.index'],
            ['admin.orders.show', ['order' => $this->order->id], 'admin.orders.show'],
            ['admin.coupons.index', [], 'admin.coupons.index'],
            ['admin.coupons.create', [], 'admin.coupons.create'],
            ['admin.coupons.edit', ['coupon' => $this->coupon->id], 'admin.coupons.edit'],
            ['admin.product_variants.index', [], 'admin.product_variants.index'],
            ['admin.product_variants.create', [], 'admin.product_variants.create'],
            ['admin.product_variants.edit', ['product_variant' => $this->productVariant->id], 'admin.product_variants.edit'],
            ['admin.customers.index', [], 'admin.customers.index'],
            ['admin.customers.create', [], 'admin.customers.create'],
            ['admin.customers.edit', ['customer' => $this->customer->id], 'admin.customers.edit'],
            ['admin.customers.show', ['customer' => $this->customer->id], 'admin.customers.show'],
            ['admin.reviews.index', [], 'admin.reviews.index'],
            ['admin.reviews.show', ['review' => $this->review->id], 'admin.reviews.show'],
            ['admin.reviews.edit', ['review' => $this->review->id], 'admin.reviews.edit'],
            ['admin.attributes.index', [], 'admin.attributes.index'],
            ['admin.attributes.create', [], 'admin.attributes.create'],
            ['admin.attributes.edit', ['attribute' => $this->attribute->id], 'admin.attributes.edit'],
            ['admin.vendors.index', [], 'admin.vendors.index'],
            ['admin.vendors.create', [], 'admin.vendors.create'],
            ['admin.pages.index', [], 'admin.pages.index'],
            ['admin.pages.create', [], 'admin.pages.create'],
            ['admin.pages.edit', ['page' => $this->page->id], 'admin.pages.edit'],
            ['admin.payments.index', [], 'admin.payments.index'],
            ['admin.payments.show', ['payment' => $this->payment->id], 'admin.payments.show'],
            ['admin.refunds.index', [], 'admin.refunds.index'],
            ['admin.refunds.show', ['refund' => $this->refund->id], 'admin.refunds.show'],
            ['admin.payment-gateways.index', [], 'admin.payment_gateways.index'],
            ['admin.payment-gateways.edit', ['payment_gateway' => $this->paymentGateway->id], 'admin.payment_gateways.edit'],
            ['admin.site-settings.index', [], 'admin.site-settings.index'],
            ['admin.site-settings.edit', [], 'admin.site-settings.edit'],
        ];

        foreach ($routes as [$name, $parameters, $expectedView]) {
            $response = $this->get(route($name, $parameters));

            $response->assertOk();
            $response->assertViewIs($expectedView);
        }
    }

}
