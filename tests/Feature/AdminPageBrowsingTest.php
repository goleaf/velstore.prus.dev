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

        $admin = \App\Models\User::factory()->create();
        $this->actingAs($admin);

        $this->seedAdminReferenceData();
    }

    public function test_admin_page_routes_are_accessible(): void
    {
        $routes = [
            ['admin.dashboard'],
            ['admin.categories.index'],
            ['admin.categories.create'],
            ['admin.categories.edit', ['category' => $this->category->id]],
            ['admin.products.index'],
            ['admin.products.create'],
            ['admin.products.edit', ['product' => $this->product->id]],
            ['admin.brands.index'],
            ['admin.brands.create'],
            ['admin.brands.edit', ['brand' => $this->brand->id]],
            ['admin.profile.show'],
            ['admin.menus.index'],
            ['admin.menus.create'],
            ['admin.menus.edit', ['menu' => $this->menu->id]],
            ['admin.menus.items.index', ['menu' => $this->menu->id]],
            ['admin.menus.items.create', ['menu' => $this->menu->id]],
            ['admin.menus.items.edit', ['item' => $this->menuItem->id]],
            ['admin.menus.item.index'],
            ['admin.banners.index'],
            ['admin.banners.create'],
            ['admin.banners.edit', ['banner' => $this->banner->id]],
            ['admin.social-media-links.index'],
            ['admin.social-media-links.create'],
            ['admin.social-media-links.edit', ['social_media_link' => $this->socialLink->id]],
            ['admin.orders.index'],
            ['admin.orders.show', ['order' => $this->order->id]],
            ['admin.coupons.index'],
            ['admin.coupons.create'],
            ['admin.coupons.edit', ['coupon' => $this->coupon->id]],
            ['admin.product_variants.index'],
            ['admin.product_variants.create'],
            ['admin.product_variants.edit', ['product_variant' => $this->productVariant->id]],
            ['admin.customers.index'],
            ['admin.customers.create'],
            ['admin.customers.edit', ['customer' => $this->customer->id]],
            ['admin.customers.show', ['customer' => $this->customer->id]],
            ['admin.reviews.index'],
            ['admin.reviews.show', ['review' => $this->review->id]],
            ['admin.reviews.edit', ['review' => $this->review->id]],
            ['admin.attributes.index'],
            ['admin.attributes.create'],
            ['admin.attributes.edit', ['attribute' => $this->attribute->id]],
            ['admin.vendors.index'],
            ['admin.vendors.create'],
            ['admin.pages.index'],
            ['admin.pages.create'],
            ['admin.pages.edit', ['page' => $this->page->id]],
            ['admin.payments.index'],
            ['admin.payments.show', ['payment' => $this->payment->id]],
            ['admin.refunds.index'],
            ['admin.refunds.show', ['refund' => $this->refund->id]],
            ['admin.payment-gateways.index'],
            ['admin.payment-gateways.edit', ['payment_gateway' => $this->paymentGateway->id]],
            ['admin.site-settings.index'],
            ['admin.site-settings.edit'],
        ];

        foreach ($routes as $route) {
            [$name, $parameters] = $route + [1 => []];

            $response = $this->get(route($name, $parameters));
            $response->assertOk();
        }
    }

}
