<?php

namespace Framework\Wordpress\Hooks\Actions;

use Framework\Wordpress\Constants\HookNames;
use Framework\Wordpress\Constants\HookTypes;
use Framework\Wordpress\BaseHook;
use Framework\Route;

class RegisterRestApi extends BaseHook
{
    public function get_name()
    {
        return HookNames::REST_API_INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $routes = Route::get_routes();

        foreach ($routes as $route) {
            $route->register();
        }
    }
}
