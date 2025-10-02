<?php

namespace Tests;

use App\Http\Middleware\AuthenticateCustomer;
use App\Models\Customer;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $app->make('router')->aliasMiddleware('auth.customer', AuthenticateCustomer::class);

        config()->set('auth.guards.customer', [
            'driver' => 'session',
            'provider' => 'customers',
        ]);

        config()->set('auth.providers.customers', [
            'driver' => 'eloquent',
            'model' => Customer::class,
        ]);

        return $app;
    }
}
