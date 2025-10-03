<?php

namespace App\Providers;

use App\Repositories\Admin\Attribute\AttributeRepository;
use App\Repositories\Admin\Attribute\AttributeRepositoryInterface;
use App\Repositories\Admin\Banner\BannerRepository;
use App\Repositories\Admin\Banner\BannerRepositoryInterface;
use App\Repositories\Admin\Brand\BrandRepository;
use App\Repositories\Admin\Brand\BrandRepositoryInterface;
use App\Repositories\Admin\Menu\MenuRepository;
use App\Models\SiteSetting;
use App\Repositories\Admin\Menu\MenuRepositoryInterface;
use App\Repositories\Admin\MenuItem\MenuItemRepository;
use App\Repositories\Admin\MenuItem\MenuItemRepositoryInterface;
use App\Repositories\Admin\Product\ProductRepository;
use App\Repositories\Admin\Product\ProductRepositoryInterface;
use App\Repositories\Admin\SocialMediaLink\SocialMediaLinkRepository;
use App\Repositories\Admin\SocialMediaLink\SocialMediaLinkRepositoryInterface;
use App\Services\Admin\ImageService;
use App\Services\Admin\MenuService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Admin\Category\CategoryRepositoryInterface::class,
            \App\Repositories\Admin\Category\CategoryRepository::class
        );

        $this->app->singleton('auth.customer', function ($app) {
            return $app['auth']->guard('customer');
        });

        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService;
        });

        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);

        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);

        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(MenuService::class, MenuService::class);

        $this->app->bind(SocialMediaLinkRepositoryInterface::class, SocialMediaLinkRepository::class);

        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);

        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! Schema::hasTable('site_settings')) {
            View::share('siteSettings', null);

            return;
        }

        $siteSettings = Cache::remember('site_settings', now()->addHour(), function () {
            $settings = SiteSetting::query()->first();

            return $settings ?? SiteSetting::make([
                'site_name' => config('app.name'),
            ]);
        });

        if ($siteSettings?->site_name) {
            config(['app.name' => $siteSettings->site_name]);
        }

        View::share('siteSettings', $siteSettings);
    }
}
