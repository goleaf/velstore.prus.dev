<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\BannerTranslation;
use App\Models\Brand;
use App\Models\BrandTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Language;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemTranslation;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\ProductVariantTranslation;
use App\Models\Refund;
use App\Models\ShippingAddress;
use App\Models\SiteSetting;
use App\Models\SocialMediaLink;
use App\Models\SocialMediaLinkTranslation;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageBrowsingTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private Product $product;
    private Brand $brand;
    private Menu $menu;
    private MenuItem $menuItem;
    private Banner $banner;
    private SocialMediaLink $socialLink;
    private Order $order;
    private Coupon $coupon;
    private ProductVariant $productVariant;
    private Customer $customer;
    private ProductReview $review;
    private Attribute $attribute;
    private Page $page;
    private Payment $payment;
    private Refund $refund;
    private PaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');

        $admin = User::factory()->create();
        $this->actingAs($admin);

        $this->seedReferenceData();
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

    /**
     * @dataProvider adminPageViewProvider
     */
    public function test_admin_pages_render_expected_views(string $routeName, array $parameterBindings, string $expectedView): void
    {
        $parameters = [];

        foreach ($parameterBindings as $parameter => $binding) {
            if (! property_exists($this, $binding)) {
                $parameters[$parameter] = $binding;

                continue;
            }

            $value = $this->{$binding};

            if ($value instanceof Model) {
                $parameters[$parameter] = $value->getRouteKey();

                continue;
            }

            $parameters[$parameter] = $value;
        }

        $response = $this->get(route($routeName, $parameters));

        $response
            ->assertOk()
            ->assertViewIs($expectedView);
    }

    public static function adminPageViewProvider(): array
    {
        return [
            'dashboard' => ['admin.dashboard', [], 'admin.home'],
            'categories index' => ['admin.categories.index', [], 'admin.categories.index'],
            'categories create' => ['admin.categories.create', [], 'admin.categories.create'],
            'categories edit' => ['admin.categories.edit', ['category' => 'category'], 'admin.categories.edit'],
            'products index' => ['admin.products.index', [], 'admin.products.index'],
            'products create' => ['admin.products.create', [], 'admin.products.create'],
            'products edit' => ['admin.products.edit', ['product' => 'product'], 'admin.products.edit'],
            'brands index' => ['admin.brands.index', [], 'admin.brands.index'],
            'brands create' => ['admin.brands.create', [], 'admin.brands.create'],
            'brands edit' => ['admin.brands.edit', ['brand' => 'brand'], 'admin.brands.edit'],
            'profile show' => ['admin.profile.show', [], 'admin.profile.show'],
            'menus index' => ['admin.menus.index', [], 'admin.menus.index'],
            'menus create' => ['admin.menus.create', [], 'admin.menus.create'],
            'menus edit' => ['admin.menus.edit', ['menu' => 'menu'], 'admin.menus.edit'],
            'menu items index (scoped)' => ['admin.menus.items.index', ['menu' => 'menu'], 'admin.menu_items.index'],
            'menu items global index' => ['admin.menus.item.index', [], 'admin.menu_items.index'],
            'menu items create' => ['admin.menus.items.create', ['menu' => 'menu'], 'admin.menu_items.create'],
            'menu items edit' => ['admin.menus.items.edit', ['item' => 'menuItem'], 'admin.menu_items.edit'],
            'banners index' => ['admin.banners.index', [], 'admin.banners.index'],
            'banners create' => ['admin.banners.create', [], 'admin.banners.create'],
            'banners edit' => ['admin.banners.edit', ['banner' => 'banner'], 'admin.banners.edit'],
            'social links index' => ['admin.social-media-links.index', [], 'admin.social-media-links.index'],
            'social links create' => ['admin.social-media-links.create', [], 'admin.social-media-links.create'],
            'social links edit' => ['admin.social-media-links.edit', ['social_media_link' => 'socialLink'], 'admin.social-media-links.edit'],
            'orders index' => ['admin.orders.index', [], 'admin.orders.index'],
            'orders show' => ['admin.orders.show', ['order' => 'order'], 'admin.orders.show'],
            'coupons index' => ['admin.coupons.index', [], 'admin.coupons.index'],
            'coupons create' => ['admin.coupons.create', [], 'admin.coupons.create'],
            'coupons edit' => ['admin.coupons.edit', ['coupon' => 'coupon'], 'admin.coupons.edit'],
            'product variants index' => ['admin.product_variants.index', [], 'admin.product_variants.index'],
            'product variants create' => ['admin.product_variants.create', [], 'admin.product_variants.create'],
            'product variants edit' => ['admin.product_variants.edit', ['product_variant' => 'productVariant'], 'admin.product_variants.edit'],
            'customers index' => ['admin.customers.index', [], 'admin.customers.index'],
            'customers create' => ['admin.customers.create', [], 'admin.customers.create'],
            'customers edit' => ['admin.customers.edit', ['customer' => 'customer'], 'admin.customers.edit'],
            'customers show' => ['admin.customers.show', ['customer' => 'customer'], 'admin.customers.show'],
            'reviews index' => ['admin.reviews.index', [], 'admin.reviews.index'],
            'reviews show' => ['admin.reviews.show', ['review' => 'review'], 'admin.reviews.show'],
            'reviews edit' => ['admin.reviews.edit', ['review' => 'review'], 'admin.reviews.edit'],
            'attributes index' => ['admin.attributes.index', [], 'admin.attributes.index'],
            'attributes create' => ['admin.attributes.create', [], 'admin.attributes.create'],
            'attributes edit' => ['admin.attributes.edit', ['attribute' => 'attribute'], 'admin.attributes.edit'],
            'vendors index' => ['admin.vendors.index', [], 'admin.vendors.index'],
            'vendors create' => ['admin.vendors.create', [], 'admin.vendors.create'],
            'pages index' => ['admin.pages.index', [], 'admin.pages.index'],
            'pages create' => ['admin.pages.create', [], 'admin.pages.create'],
            'pages edit' => ['admin.pages.edit', ['page' => 'page'], 'admin.pages.edit'],
            'payments index' => ['admin.payments.index', [], 'admin.payments.index'],
            'payments show' => ['admin.payments.show', ['payment' => 'payment'], 'admin.payments.show'],
            'refunds index' => ['admin.refunds.index', [], 'admin.refunds.index'],
            'refunds show' => ['admin.refunds.show', ['refund' => 'refund'], 'admin.refunds.show'],
            'payment gateways index' => ['admin.payment-gateways.index', [], 'admin.payment_gateways.index'],
            'payment gateways edit' => ['admin.payment-gateways.edit', ['payment_gateway' => 'paymentGateway'], 'admin.payment_gateways.edit'],
            'site settings index' => ['admin.site-settings.index', [], 'admin.site-settings.index'],
            'site settings edit' => ['admin.site-settings.edit', [], 'admin.site-settings.edit'],
        ];
    }

    private function seedReferenceData(): void
    {
        Language::create([
            'code' => 'en',
            'name' => 'English',
            'translated_text' => 'English',
            'active' => true,
        ]);

        $this->category = Category::create([
            'slug' => 'test-category',
            'status' => true,
        ]);

        CategoryTranslation::create([
            'category_id' => $this->category->id,
            'language_code' => 'en',
            'name' => 'Test Category',
            'description' => 'Category description',
            'image_url' => 'categories/test-category.jpg',
        ]);

        $this->brand = Brand::create([
            'slug' => 'test-brand',
            'status' => true,
        ]);

        BrandTranslation::create([
            'brand_id' => $this->brand->id,
            'locale' => 'en',
            'name' => 'Test Brand',
            'description' => 'Brand description',
        ]);

        $vendor = Vendor::create([
            'name' => 'Vendor',
            'email' => 'vendor@example.com',
            'password' => 'password',
            'status' => 'active',
        ]);

        $size = Attribute::create(['name' => 'Size']);
        AttributeValue::create([
            'attribute_id' => $size->id,
            'value' => 'Large',
        ]);

        $color = Attribute::create(['name' => 'Color']);
        AttributeValue::create([
            'attribute_id' => $color->id,
            'value' => 'Red',
        ]);

        $this->attribute = Attribute::create(['name' => 'Material']);
        AttributeValue::create([
            'attribute_id' => $this->attribute->id,
            'value' => 'Cotton',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $vendor->id,
            'shop_id' => 1,
            'price' => 99.99,
            'stock' => 10,
            'status' => true,
            'slug' => 'test-product',
            'product_type' => 'simple',
            'SKU' => 'SKU-TEST',
            'currency' => 'USD',
        ]);

        ProductTranslation::create([
            'product_id' => $this->product->id,
            'language_code' => 'en',
            'locale' => 'en',
            'name' => 'Test Product',
            'description' => 'Product description',
        ]);

        $this->productVariant = ProductVariant::create([
            'product_id' => $this->product->id,
            'variant_slug' => 'test-product-variant',
            'price' => 99.99,
            'discount_price' => 79.99,
            'stock' => 5,
            'SKU' => 'VARIANT-1',
            'barcode' => '1234567890123',
            'is_primary' => true,
            'weight' => 1,
            'dimensions' => '10x10x5',
        ]);

        ProductVariantTranslation::create([
            'product_variant_id' => $this->productVariant->id,
            'language_code' => 'en',
            'name' => 'Variant',
        ]);

        $this->menu = Menu::create([
            'title' => 'Main Menu',
            'status' => true,
            'date' => now(),
        ]);

        $this->menuItem = MenuItem::create([
            'menu_id' => $this->menu->id,
            'slug' => 'menu-item',
            'order_number' => 1,
        ]);

        MenuItemTranslation::create([
            'menu_item_id' => $this->menuItem->id,
            'language_code' => 'en',
            'title' => 'Menu Item',
        ]);

        $this->banner = Banner::create([
            'title' => 'Homepage Banner',
            'status' => true,
            'type' => 'hero',
        ]);

        BannerTranslation::create([
            'banner_id' => $this->banner->id,
            'language_code' => 'en',
            'title' => 'Homepage Banner',
            'description' => 'Banner description',
            'image_url' => 'banners/homepage-banner.jpg',
            'type' => 'hero',
        ]);

        $this->socialLink = SocialMediaLink::create([
            'type' => 'facebook',
            'platform' => 'Facebook',
            'link' => 'https://facebook.com/example',
        ]);

        SocialMediaLinkTranslation::create([
            'social_media_link_id' => $this->socialLink->id,
            'language_code' => 'en',
            'name' => 'Facebook',
        ]);

        $this->coupon = Coupon::create([
            'code' => 'WELCOME',
            'discount' => 10,
            'type' => 'percentage',
            'expires_at' => Carbon::now()->addDay(),
        ]);

        $this->customer = Customer::factory()->create();

        $this->order = Order::create([
            'customer_id' => $this->customer->id,
            'total_amount' => 150,
            'status' => 'completed',
        ]);

        OrderDetail::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 50,
        ]);

        ShippingAddress::create([
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
            'address' => '123 Street',
            'city' => 'City',
            'postal_code' => '12345',
            'country' => 'Country',
        ]);

        $this->paymentGateway = PaymentGateway::create([
            'name' => 'Stripe',
            'code' => 'stripe',
            'description' => 'Stripe gateway',
            'is_active' => true,
        ]);

        PaymentGatewayConfig::create([
            'gateway_id' => $this->paymentGateway->id,
            'key_name' => 'api_key',
            'key_value' => 'secret',
            'is_encrypted' => false,
            'environment' => 'test',
        ]);

        $this->payment = Payment::create([
            'order_id' => $this->order->id,
            'gateway_id' => $this->paymentGateway->id,
            'amount' => 150,
            'currency' => 'USD',
            'status' => 'completed',
            'transaction_id' => 'txn_1',
        ]);

        $this->refund = Refund::create([
            'payment_id' => $this->payment->id,
            'amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
            'refund_id' => 'refund_1',
        ]);

        $this->review = ProductReview::create([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'rating' => 5,
            'review' => 'Great product',
            'is_approved' => true,
        ]);

        $this->page = Page::create([
            'slug' => 'about-us',
            'status' => true,
        ]);

        PageTranslation::create([
            'page_id' => $this->page->id,
            'language_code' => 'en',
            'title' => 'About us',
            'content' => 'About page content',
            'image_url' => 'pages/about-us.jpg',
        ]);

        SiteSetting::create([
            'site_name' => 'Velstore',
            'tagline' => 'Tagline',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta description',
            'meta_keywords' => 'shop,store',
            'contact_email' => 'info@example.com',
            'contact_phone' => '+123456789',
            'address' => '123 Market Street',
            'footer_text' => 'Copyright',
        ]);
    }
}
