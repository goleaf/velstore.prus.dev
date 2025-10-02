<?php

namespace Tests;

use App\Http\Middleware\AuthenticateCustomer;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('auth.guards.customer', array_merge([
            'driver' => 'session',
            'provider' => 'customers',
        ], Config::get('auth.guards.customer', [])));

        Config::set('auth.providers.customers', array_merge([
            'driver' => 'eloquent',
            'model' => \App\Models\Customer::class,
        ], Config::get('auth.providers.customers', [])));

        $this->app['router']->aliasMiddleware('auth.customer', AuthenticateCustomer::class);
    }
}
