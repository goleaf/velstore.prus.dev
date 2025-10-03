<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Banner;
use App\Models\BannerTranslation;
use App\Models\Brand;
use App\Models\BrandTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerAddress;
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
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttributeValue;
use App\Models\ProductVariantTranslation;
use App\Models\Refund;
use App\Models\ShippingAddress;
use App\Models\Shop;
use App\Models\SiteSetting;
use App\Models\SocialMediaLink;
use App\Models\SocialMediaLinkTranslation;
use App\Models\StoreSetting;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Mapping between application locales and faker locales.
     */
    protected array $fakerLocales = [
        'en' => 'en_US',
        'es' => 'es_ES',
        'fr' => 'fr_FR',
        'de' => 'de_DE',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $languageDefinitions = [
                'en' => ['name' => 'English', 'translated_text' => 'English', 'active' => true],
                'es' => ['name' => 'Spanish', 'translated_text' => 'Español', 'active' => true],
                'fr' => ['name' => 'French', 'translated_text' => 'Français', 'active' => true],
                'de' => ['name' => 'German', 'translated_text' => 'Deutsch', 'active' => true],
            ];

            foreach ($languageDefinitions as $code => $payload) {
                Language::updateOrCreate(['code' => $code], $payload);
            }

            $languages = array_keys($languageDefinitions);

            $this->seedCurrencies();
            $vendors = Vendor::factory()->count(3)->create();
            $shops = collect();
            foreach ($vendors as $vendor) {
                $shop = Shop::factory()->create([
                    'vendor_id' => $vendor->id,
                    'status' => 'active',
                ]);

                $shops->push($shop);
            }

            SiteSetting::factory()->count(1)->create();
            StoreSetting::factory()->count(10)->create();

            $socialLinks = SocialMediaLink::factory()->count(4)->create();
            foreach ($socialLinks as $link) {
                foreach ($languages as $languageCode) {
                    SocialMediaLinkTranslation::updateOrCreate(
                        [
                            'social_media_link_id' => $link->id,
                            'language_code' => $languageCode,
                        ],
                        [
                            'name' => $this->fakerFor($languageCode)->words(2, true),
                        ]
                    );
                }
            }

            $attributes = Attribute::factory()->count(4)->create();
            foreach ($attributes as $attribute) {
                $values = AttributeValue::factory()->count(4)->create([
                    'attribute_id' => $attribute->id,
                ]);

                foreach ($values as $value) {
                    foreach ($languages as $languageCode) {
                        AttributeValueTranslation::updateOrCreate(
                            [
                                'attribute_value_id' => $value->id,
                                'language_code' => $languageCode,
                            ],
                            [
                                'translated_value' => $this->fakerFor($languageCode)->word(),
                            ]
                        );
                    }
                }
            }

            $brands = Brand::factory()->count(6)->create([
                'status' => 'active',
            ]);
            foreach ($brands as $brand) {
                foreach ($languages as $languageCode) {
                    BrandTranslation::updateOrCreate(
                        [
                            'brand_id' => $brand->id,
                            'locale' => $languageCode,
                        ],
                        [
                            'name' => $this->fakerFor($languageCode)->company(),
                            'description' => $this->fakerFor($languageCode)->paragraph(),
                        ]
                    );
                }
            }

            $parentCategories = Category::factory()->count(4)->create();
            $allCategories = collect($parentCategories->all());

            foreach ($parentCategories as $category) {
                foreach ($languages as $languageCode) {
                    CategoryTranslation::updateOrCreate(
                        [
                            'category_id' => $category->id,
                            'language_code' => $languageCode,
                        ],
                        [
                            'name' => Str::title($this->fakerFor($languageCode)->words(2, true)),
                            'description' => $this->fakerFor($languageCode)->sentence(12),
                            'image_url' => fake()->imageUrl(),
                        ]
                    );
                }

                $children = Category::factory()->count(3)->create([
                    'parent_category_id' => $category->id,
                ]);
                foreach ($children as $child) {
                    foreach ($languages as $languageCode) {
                        CategoryTranslation::updateOrCreate(
                            [
                                'category_id' => $child->id,
                                'language_code' => $languageCode,
                            ],
                            [
                                'name' => Str::title($this->fakerFor($languageCode)->words(3, true)),
                                'description' => $this->fakerFor($languageCode)->sentence(10),
                                'image_url' => fake()->imageUrl(),
                            ]
                        );
                    }
                    $allCategories->push($child);
                }
            }

            $menus = Menu::factory()->count(2)->create(['status' => true]);
            foreach ($menus as $menu) {
                $menuItems = MenuItem::factory()->count(5)->create([
                    'menu_id' => $menu->id,
                ]);

                foreach ($menuItems as $item) {
                    foreach ($languages as $languageCode) {
                        MenuItemTranslation::updateOrCreate(
                            [
                                'menu_item_id' => $item->id,
                                'language_code' => $languageCode,
                            ],
                            [
                                'title' => Str::title($this->fakerFor($languageCode)->words(2, true)),
                            ]
                        );
                    }
                }
            }

            $pages = Page::factory()->count(5)->create();
            foreach ($pages as $page) {
                foreach ($languages as $languageCode) {
                    PageTranslation::updateOrCreate(
                        [
                            'page_id' => $page->id,
                            'language_code' => $languageCode,
                        ],
                        [
                            'title' => Str::title($this->fakerFor($languageCode)->sentence(3)),
                            'content' => $this->fakerFor($languageCode)->paragraphs(3, true),
                        ]
                    );
                }
            }

            $banners = Banner::factory()->count(6)->create();
            foreach ($banners as $banner) {
                foreach ($languages as $languageCode) {
                    BannerTranslation::updateOrCreate(
                        [
                            'banner_id' => $banner->id,
                            'language_code' => $languageCode,
                        ],
                        [
                            'title' => Str::title($this->fakerFor($languageCode)->sentence(4)),
                            'description' => $this->fakerFor($languageCode)->paragraph(),
                            'button_text' => $this->fakerFor($languageCode)->optional()->words(2, true),
                            'button_url' => fake()->optional(0.6)->url(),
                            'image_url' => fake()->imageUrl(1200, 400, true),
                        ]
                    );
                }
            }

            $coupons = Coupon::factory()->count(5)->create();

            $paymentGateways = collect([
                ['name' => 'Stripe', 'code' => 'stripe', 'description' => 'Stripe payment gateway'],
                ['name' => 'PayPal', 'code' => 'paypal', 'description' => 'PayPal payment gateway'],
                ['name' => 'Bank Transfer', 'code' => 'bank-transfer', 'description' => 'Manual bank transfer'],
            ])->map(function (array $gatewayData) {
                return PaymentGateway::updateOrCreate(
                    ['code' => $gatewayData['code']],
                    [
                        'name' => $gatewayData['name'],
                        'description' => $gatewayData['description'],
                        'is_active' => true,
                    ]
                );
            });

            foreach ($paymentGateways as $gateway) {
                PaymentGatewayConfig::factory()->count(3)->create([
                    'gateway_id' => $gateway->id,
                    'environment' => Arr::random(['production', 'sandbox']),
                ]);
            }

            $customers = Customer::factory()->count(10)->create();
            foreach ($customers as $customer) {
                CustomerAddress::factory()->count(2)->create([
                    'customer_id' => $customer->id,
                ]);
            }

            $products = collect();
            foreach (range(1, 20) as $_) {
                $category = $allCategories->random();
                $brand = $brands->random();
                $vendor = $vendors->random();
                $shop = $shops->firstWhere('vendor_id', $vendor->id) ?? $shops->random();

                $products->push(
                    Product::factory()->create([
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'vendor_id' => $vendor->id,
                        'shop_id' => $shop->id,
                        'status' => 1,
                        'product_type' => Arr::random(['simple', 'variable']),
                    ])
                );
            }

            foreach ($products as $product) {
                foreach ($languages as $languageCode) {
                    ProductTranslation::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'language_code' => $languageCode,
                        ],
                        [
                            'name' => Str::title($this->fakerFor($languageCode)->words(3, true)),
                            'description' => $this->fakerFor($languageCode)->paragraphs(3, true),
                            'short_description' => $this->fakerFor($languageCode)->sentence(),
                            'tags' => implode(',', $this->fakerFor($languageCode)->words(4)),
                        ]
                    );
                }

                ProductImage::factory()->count(3)->create([
                    'product_id' => $product->id,
                ]);

                $variantCount = rand(1, 3);
                $variants = ProductVariant::factory()->count($variantCount)->create([
                    'product_id' => $product->id,
                ]);

                foreach ($variants as $variant) {
                    foreach ($languages as $languageCode) {
                        ProductVariantTranslation::updateOrCreate(
                            [
                                'product_variant_id' => $variant->id,
                                'language_code' => $languageCode,
                            ],
                            [
                                'name' => Str::title($this->fakerFor($languageCode)->words(2, true)),
                            ]
                        );
                    }

                    $attributeValueIds = AttributeValue::inRandomOrder()->take(2)->pluck('id');
                    foreach ($attributeValueIds as $attributeValueId) {
                        $productAttributeValue = ProductAttributeValue::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'attribute_value_id' => $attributeValueId,
                            ],
                            []
                        );

                        ProductVariantAttributeValue::updateOrCreate(
                            [
                                'product_variant_id' => $variant->id,
                                'attribute_value_id' => $productAttributeValue->attribute_value_id,
                            ],
                            [
                                'product_id' => $product->id,
                            ]
                        );
                    }
                }

                Wishlist::factory()->count(2)->create([
                    'product_id' => $product->id,
                    'customer_id' => $customers->random()->id,
                ]);

                ProductReview::factory()
                    ->count(3)
                    ->state(function () use ($product, $customers) {
                        return [
                            'product_id' => $product->id,
                            'customer_id' => $customers->random()->id,
                        ];
                    })
                    ->create();
            }

            $orders = collect();
            $adminUser = User::query()->first() ?? User::factory()->create([
                'email' => 'admin@example.com',
            ]);

            foreach (range(1, 15) as $_) {
                $customer = $customers->random();
                $order = Order::factory()->create([
                    'customer_id' => $customer->id,
                    'status' => Arr::random(['pending', 'processing', 'completed', 'canceled']),
                ]);
                $orders->push($order);

                $items = $products->random(rand(1, 3));
                $items = $items instanceof \Illuminate\Support\Collection ? $items : collect([$items]);

                $orderTotal = 0;
                foreach ($items as $item) {
                    $quantity = rand(1, 3);
                    $lineTotal = $item->price * $quantity;
                    $orderTotal += $lineTotal;

                    OrderDetail::factory()->create([
                        'order_id' => $order->id,
                        'product_id' => $item->id,
                        'quantity' => $quantity,
                        'price' => $item->price,
                    ]);
                }

                $order->update(['total_amount' => $orderTotal]);

                $paymentGateway = $paymentGateways->random();
                $paymentStatus = Arr::random(['pending', 'processing', 'completed', 'failed', 'refunded']);
                $payment = Payment::factory()->create([
                    'order_id' => $order->id,
                    'gateway_id' => $paymentGateway->id,
                    'amount' => $orderTotal,
                    'currency' => Currency::inRandomOrder()->value('code') ?? 'USD',
                    'status' => $paymentStatus,
                ]);

                if ($paymentStatus === 'refunded') {
                    Refund::factory()->create([
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => 'completed',
                    ]);
                }

                ShippingAddress::factory()->create([
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone ?? fake()->phoneNumber(),
                    'address' => $customer->address ?? fake()->streetAddress(),
                    'city' => fake()->city(),
                    'postal_code' => fake()->postcode(),
                    'country' => fake()->country(),
                ]);
            }
        });
    }

    protected function seedCurrencies(): void
    {
        $currencies = [
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'exchange_rate' => 1.0],
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 0.92],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£', 'exchange_rate' => 0.80],
            ['name' => 'Japanese Yen', 'code' => 'JPY', 'symbol' => '¥', 'exchange_rate' => 140.5],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }

    protected function fakerFor(string $languageCode)
    {
        $locale = $this->fakerLocales[$languageCode] ?? 'en_US';

        return fake($locale);
    }
}
