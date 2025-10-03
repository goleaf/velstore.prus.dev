<?php

namespace Tests\Feature;

use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesAdminTestData;
use Tests\TestCase;

class AdminPageViewTest extends TestCase
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

    /**
     * @dataProvider adminPageViewProvider
     */
    public function test_admin_pages_render_expected_views(string $route, array $parameters, string $view): void
    {
        $resolvedParameters = $this->resolveRouteParameters($parameters);
        $response = $this->get(route($route, $resolvedParameters));

        $response
            ->assertOk()
            ->assertViewIs($view);
    }

    /**
     * @return array<string, array{string, array, string}>
     */
    public static function adminPageViewProvider(): array
    {
        return [
            'dashboard' => ['admin.dashboard', [], 'admin.home'],
            'categories index' => ['admin.categories.index', [], 'admin.categories.index'],
            'categories create' => ['admin.categories.create', [], 'admin.categories.create'],
            'categories edit' => ['admin.categories.edit', ['category' => fn (self $test) => $test->category->id], 'admin.categories.edit'],
            'products index' => ['admin.products.index', [], 'admin.products.index'],
            'products create' => ['admin.products.create', [], 'admin.products.create'],
            'products edit' => ['admin.products.edit', ['product' => fn (self $test) => $test->product->id], 'admin.products.edit'],
            'brands index' => ['admin.brands.index', [], 'admin.brands.index'],
            'brands create' => ['admin.brands.create', [], 'admin.brands.create'],
            'brands edit' => ['admin.brands.edit', ['id' => fn (self $test) => $test->brand->id], 'admin.brands.edit'],
            'profile show' => ['admin.profile.show', [], 'admin.profile.show'],
            'menus index' => ['admin.menus.index', [], 'admin.menus.index'],
            'menus create' => ['admin.menus.create', [], 'admin.menus.create'],
            'menus edit' => ['admin.menus.edit', ['menu' => fn (self $test) => $test->menu->id], 'admin.menus.edit'],
            'menu items index' => ['admin.menus.items.index', ['menu' => fn (self $test) => $test->menu->id], 'admin.menu_items.index'],
            'menu items create' => ['admin.menus.items.create', ['menu' => fn (self $test) => $test->menu->id], 'admin.menu_items.create'],
            'menu items list' => ['admin.menus.item.index', [], 'admin.menu_items.index'],
            'banners index' => ['admin.banners.index', [], 'admin.banners.index'],
            'banners create' => ['admin.banners.create', [], 'admin.banners.create'],
            'banners edit' => ['admin.banners.edit', ['banner' => fn (self $test) => $test->banner->id], 'admin.banners.edit'],
            'social media links index' => ['admin.social-media-links.index', [], 'admin.social-media-links.index'],
            'social media links create' => ['admin.social-media-links.create', [], 'admin.social-media-links.create'],
            'social media links edit' => ['admin.social-media-links.edit', ['social_media_link' => fn (self $test) => $test->socialLink->id], 'admin.social-media-links.edit'],
            'orders index' => ['admin.orders.index', [], 'admin.orders.index'],
            'orders create' => ['admin.orders.create', [], 'admin.orders.create'],
            'orders show' => ['admin.orders.show', ['order' => fn (self $test) => $test->order->id], 'admin.orders.show'],
            'coupons index' => ['admin.coupons.index', [], 'admin.coupons.index'],
            'coupons create' => ['admin.coupons.create', [], 'admin.coupons.create'],
            'coupons edit' => ['admin.coupons.edit', ['coupon' => fn (self $test) => $test->coupon->id], 'admin.coupons.edit'],
            'product variants index' => ['admin.product_variants.index', [], 'admin.product_variants.index'],
            'product variants create' => ['admin.product_variants.create', [], 'admin.product_variants.create'],
            'product variants edit' => ['admin.product_variants.edit', ['product_variant' => fn (self $test) => $test->productVariant->id], 'admin.product_variants.edit'],
            'customers index' => ['admin.customers.index', [], 'admin.customers.index'],
            'customers create' => ['admin.customers.create', [], 'admin.customers.create'],
            'customers edit' => ['admin.customers.edit', ['customer' => fn (self $test) => $test->customer->id], 'admin.customers.edit'],
            'customers show' => ['admin.customers.show', ['customer' => fn (self $test) => $test->customer->id], 'admin.customers.show'],
            'reviews index' => ['admin.reviews.index', [], 'admin.reviews.index'],
            'reviews show' => ['admin.reviews.show', ['review' => fn (self $test) => $test->review->id], 'admin.reviews.show'],
            'reviews edit' => ['admin.reviews.edit', ['review' => fn (self $test) => $test->review->id], 'admin.reviews.edit'],
            'attributes index' => ['admin.attributes.index', [], 'admin.attributes.index'],
            'attributes create' => ['admin.attributes.create', [], 'admin.attributes.create'],
            'attributes edit' => ['admin.attributes.edit', ['attribute' => fn (self $test) => $test->attribute->id], 'admin.attributes.edit'],
            'vendors index' => ['admin.vendors.index', [], 'admin.vendors.index'],
            'vendors create' => ['admin.vendors.create', [], 'admin.vendors.create'],
            'pages index' => ['admin.pages.index', [], 'admin.pages.index'],
            'pages create' => ['admin.pages.create', [], 'admin.pages.create'],
            'pages edit' => ['admin.pages.edit', ['page' => fn (self $test) => $test->page->id], 'admin.pages.edit'],
            'payments index' => ['admin.payments.index', [], 'admin.payments.index'],
            'payments show' => ['admin.payments.show', ['payment' => fn (self $test) => $test->payment->id], 'admin.payments.show'],
            'refunds index' => ['admin.refunds.index', [], 'admin.refunds.index'],
            'refunds show' => ['admin.refunds.show', ['refund' => fn (self $test) => $test->refund->id], 'admin.refunds.show'],
            'payment gateways index' => ['admin.payment-gateways.index', [], 'admin.payment_gateways.index'],
            'payment gateways edit' => ['admin.payment-gateways.edit', ['paymentGateway' => fn (self $test) => $test->paymentGateway->id], 'admin.payment_gateways.edit'],
            'site settings index' => ['admin.site-settings.index', [], 'admin.site-settings.index'],
            'site settings edit' => ['admin.site-settings.edit', [], 'admin.site-settings.edit'],
        ];
    }

    /**
     * Laravel's data providers cannot resolve closures automatically, so we
     * resolve any lazy parameter callbacks before the test runs.
     *
     * @param array $parameters
     * @return array
     */
    protected function resolveRouteParameters(array $parameters): array
    {
        foreach ($parameters as $key => $value) {
            if ($value instanceof Closure) {
                $parameters[$key] = $value($this);
            }
        }

        return $parameters;
    }
}
