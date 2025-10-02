<?php

namespace Tests;

use App\Models\Customer;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('auth.providers.customers', config('auth.providers.customers', [
            'driver' => 'eloquent',
            'model' => Customer::class,
        ]));

        config()->set('auth.guards.customer', config('auth.guards.customer', [
            'driver' => 'session',
            'provider' => 'customers',
        ]));
    }
}
