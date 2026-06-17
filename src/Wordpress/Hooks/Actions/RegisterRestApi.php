<?php
/**
 * WordPress rest_api_init action that registers all Route instances with the REST API.
 * Iterates the static route registry and calls register on each.
 * Bridges the Route fluent API to WordPress REST endpoints.
 *
 * @package    Framework
 * @subpackage Wordpress\Hooks\Actions
 * @since      1.0.0
 */
namespace Framework\Wordpress\Hooks\Actions;

defined('ABSPATH') || exit;

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
