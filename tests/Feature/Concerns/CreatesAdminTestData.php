<?php

namespace Tests\Feature\Concerns;

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
use App\Models\Shop;
use App\Models\SiteSetting;
use App\Models\SocialMediaLink;
use App\Models\SocialMediaLinkTranslation;
use App\Models\Vendor;
use Carbon\Carbon;

trait CreatesAdminTestData
{
    protected Category $category;
    protected Product $product;
    protected Brand $brand;
    protected Menu $menu;
    protected MenuItem $menuItem;
    protected Banner $banner;
    protected SocialMediaLink $socialLink;
    protected Order $order;
    protected Coupon $coupon;
    protected ProductVariant $productVariant;
    protected Customer $customer;
    protected ProductReview $review;
    protected Attribute $attribute;
    protected Page $page;
    protected Payment $payment;
    protected Refund $refund;
    protected PaymentGateway $paymentGateway;

    protected function seedAdminReferenceData(): void
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

        $shop = Shop::create([
            'vendor_id' => $vendor->id,
            'name' => 'Test Shop',
            'slug' => 'test-shop',
            'logo' => 'N/A',
            'description' => 'Test shop description',
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
            'shop_id' => $shop->id,
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
            'status' => 1,
            'type' => 'promotion',
        ]);

        BannerTranslation::create([
            'banner_id' => $this->banner->id,
            'language_code' => 'en',
            'title' => 'Homepage Banner',
            'description' => 'Banner description',
            'image_url' => 'banners/homepage-banner.jpg',
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
            'shop_id' => $shop->id,
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
