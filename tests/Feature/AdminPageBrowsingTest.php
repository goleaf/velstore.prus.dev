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
            ['name' => 'admin.dashboard'],
            ['name' => 'admin.categories.index'],
            ['name' => 'admin.categories.create'],
            ['name' => 'admin.categories.edit', 'parameters' => ['category' => $this->category->id]],
            ['name' => 'admin.products.index'],
            ['name' => 'admin.products.create'],
            ['name' => 'admin.products.edit', 'parameters' => ['product' => $this->product->id]],
            ['name' => 'admin.brands.index'],
            ['name' => 'admin.brands.getData', 'expectsJson' => true, 'headers' => ['X-Requested-With' => 'XMLHttpRequest']],
            ['name' => 'admin.brands.create'],
            ['name' => 'admin.brands.edit', 'parameters' => ['brand' => $this->brand->id]],
            ['name' => 'admin.profile.show'],
            ['name' => 'admin.menus.index'],
            ['name' => 'admin.menus.create'],
            ['name' => 'admin.menus.edit', 'parameters' => ['menu' => $this->menu->id]],
            ['name' => 'admin.menus.items.index', 'parameters' => ['menu' => $this->menu->id]],
            ['name' => 'admin.menus.items.create', 'parameters' => ['menu' => $this->menu->id]],
            ['name' => 'admin.menus.items.edit', 'parameters' => ['item' => $this->menuItem->id]],
            ['name' => 'admin.menus.item.index'],
            ['name' => 'admin.banners.index'],
            ['name' => 'admin.banners.create'],
            ['name' => 'admin.banners.edit', 'parameters' => ['banner' => $this->banner->id]],
            ['name' => 'admin.social-media-links.index'],
            ['name' => 'admin.social-media-links.create'],
            ['name' => 'admin.social-media-links.edit', 'parameters' => ['social_media_link' => $this->socialLink->id]],
            ['name' => 'admin.orders.index'],
            ['name' => 'admin.orders.show', 'parameters' => ['order' => $this->order->id]],
            ['name' => 'admin.coupons.index'],
            ['name' => 'admin.coupons.create'],
            ['name' => 'admin.coupons.edit', 'parameters' => ['coupon' => $this->coupon->id]],
            ['name' => 'admin.product_variants.index'],
            ['name' => 'admin.product_variants.create'],
            ['name' => 'admin.product_variants.edit', 'parameters' => ['product_variant' => $this->productVariant->id]],
            ['name' => 'admin.customers.index'],
            ['name' => 'admin.customers.data', 'expectsJson' => true, 'headers' => ['X-Requested-With' => 'XMLHttpRequest']],
            ['name' => 'admin.customers.create'],
            ['name' => 'admin.customers.edit', 'parameters' => ['customer' => $this->customer->id]],
            ['name' => 'admin.customers.show', 'parameters' => ['customer' => $this->customer->id]],
            ['name' => 'admin.reviews.index'],
            ['name' => 'admin.reviews.data', 'expectsJson' => true, 'headers' => ['X-Requested-With' => 'XMLHttpRequest']],
            ['name' => 'admin.reviews.show', 'parameters' => ['review' => $this->review->id]],
            ['name' => 'admin.reviews.edit', 'parameters' => ['review' => $this->review->id]],
            ['name' => 'admin.attributes.index'],
            ['name' => 'admin.attributes.create'],
            ['name' => 'admin.attributes.edit', 'parameters' => ['attribute' => $this->attribute->id]],
            ['name' => 'admin.vendors.index'],
            ['name' => 'admin.vendors.data', 'expectsJson' => true, 'headers' => ['X-Requested-With' => 'XMLHttpRequest']],
            ['name' => 'admin.vendors.create'],
            ['name' => 'admin.pages.index'],
            ['name' => 'admin.pages.create'],
            ['name' => 'admin.pages.edit', 'parameters' => ['page' => $this->page->id]],
            ['name' => 'admin.payments.index'],
            ['name' => 'admin.payments.getData', 'expectsJson' => true, 'headers' => ['X-Requested-With' => 'XMLHttpRequest']],
            ['name' => 'admin.payments.show', 'parameters' => ['payment' => $this->payment->id]],
            ['name' => 'admin.refunds.index'],
            ['name' => 'admin.refunds.getData', 'expectsJson' => true, 'headers' => ['X-Requested-With' => 'XMLHttpRequest']],
            ['name' => 'admin.refunds.show', 'parameters' => ['refund' => $this->refund->id]],
            ['name' => 'admin.payment-gateways.index'],
            ['name' => 'admin.payment-gateways.getData', 'expectsJson' => true, 'headers' => ['X-Requested-With' => 'XMLHttpRequest']],
            ['name' => 'admin.payment-gateways.edit', 'parameters' => ['payment_gateway' => $this->paymentGateway->id]],
            ['name' => 'admin.site-settings.index'],
            ['name' => 'admin.site-settings.edit'],
        ];

        foreach ($routes as $route) {
            $name = $route['name'];
            $parameters = $route['parameters'] ?? [];
            $headers = $route['headers'] ?? [];
            $expectsJson = $route['expectsJson'] ?? false;

            $url = route($name, $parameters);
            $response = $expectsJson
                ? $this->getJson($url, $headers)
                : $this->get($url, $headers);

            $response->assertOk();
        }
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
