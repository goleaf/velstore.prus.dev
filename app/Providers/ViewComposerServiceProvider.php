<?php

namespace App\Providers;

use App\View\Composers\AdminLanguageComposer;
use App\View\Composers\StoreMenuComposer;
use App\View\Composers\StorePageComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('themes.*', StoreMenuComposer::class);
        View::composer('themes.*', StorePageComposer::class);
        View::composer('admin.*', AdminLanguageComposer::class);
    }
}
