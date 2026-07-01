<?php

namespace Example\App\Providers;

use Framework\ServiceProvider;

use function Framework\config;

class TestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $config = config('app.name');
    }
}