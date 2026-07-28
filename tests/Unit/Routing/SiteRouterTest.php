<?php

namespace Framework\Tests\Unit\Routing;

use Framework\Contracts\Middleware;
use Framework\Contracts\Request as RequestContract;
use Framework\Http\Request;
use Framework\Route;
use Framework\Routing\SiteRouter;
use Framework\Tests\Unit\TestCase;
use Framework\Wordpress\Constants\HookNames;

class SiteRouterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->reset_route_state();
        Route::set_namespace('framework/v1');
        Route::set_site_namespace('framework');
        $this->bootstrap_application();
    }

    public function test_site_block_marks_routes_as_site_routes(): void
    {
        Route::site(function () {
            Route::get('shop/{id:int}', [SiteRouterTestController::class, 'show'])
                ->name('shop.show');
        });

        $routes = Route::get_site_routes();

        $this->assertCount(1, $routes);
        $this->assertTrue($routes[0]->is_site_route());
        $this->assertSame('shop/{id:int}', $routes[0]->get_endpoint());
        $this->assertSame('shop.show', $routes[0]->get_name());
        $this->assertSame(['id' => 'int'], $routes[0]->get_param_types());
    }

    public function test_fluent_site_marker_and_group_prefix(): void
    {
        Route::group(['prefix' => 'shop'], function () {
            Route::get('products/{id}', [SiteRouterTestController::class, 'show'])
                ->as_site()
                ->where('id', 'int')
                ->name('products.show');
        });

        $route = Route::get_site_routes()[0];

        $this->assertTrue($route->is_site_route());
        $this->assertSame('shop/products/{id}', $route->get_endpoint());
        $this->assertSame(Route::find_named_route('products.show'), $route);
    }

    public function test_register_skips_site_routes(): void
    {
        Route::get('api/ping', [SiteRouterTestController::class, 'show']);
        Route::get('page', [SiteRouterTestController::class, 'show'])->as_site();

        $rest = array_values(array_filter(Route::get_routes(), function (Route $route) {
            return !$route->is_site_route();
        }));
        $site = Route::get_site_routes();

        $this->assertCount(1, $rest);
        $this->assertCount(1, $site);

        $site[0]->register();
        $this->assertTrue($site[0]->is_site_route());
    }

    public function test_dispatch_site_injects_request_and_runs_middleware(): void
    {
        SiteRouterTestMiddleware::$called = false;

        Route::site(function () {
            Route::get('hello', [SiteRouterTestController::class, 'show'])
                ->middleware([SiteRouterTestMiddleware::class]);
        });

        $route = Route::get_site_routes()[0];
        $result = $route->dispatch_site(['id' => 10]);

        $this->assertTrue(SiteRouterTestMiddleware::$called);
        $this->assertInstanceOf(Request::class, $result);
        $this->assertSame(10, $result->route('id'));
    }

    public function test_dispatch_site_supports_closures(): void
    {
        Route::site(function () {
            Route::get('hello', function (Request $request) {
                return 'hi-' . $request->route('name');
            });
        });

        $result = Route::get_site_routes()[0]->dispatch_site(['name' => 'sajeeb']);

        $this->assertSame('hi-sajeeb', $result);
    }

    public function test_dispatch_site_injects_closure_route_params(): void
    {
        Route::site(function () {
            Route::get('hello/{name:alpha}', function (Request $request, string $name) {
                return 'hi-' . $name;
            })->name('hello');
        });

        $result = Route::get_site_routes()[0]->dispatch_site(['name' => 'sajeeb']);

        $this->assertSame('hi-sajeeb', $result);
    }

    public function test_template_hook_aliases_set_hook_name(): void
    {
        Route::site(function () {
            Route::get('dashboard', [SiteRouterTestController::class, 'show'])
                ->template_redirect()
                ->name('dashboard');

            Route::get('legacy', [SiteRouterTestController::class, 'show'])
                ->template_include(20)
                ->name('legacy');
        });

        $routes = Route::get_site_routes();

        $this->assertSame(HookNames::TEMPLATE_REDIRECT, $routes[0]->get_hook_name());
        $this->assertSame(10, $routes[0]->get_hook_priority());
        $this->assertSame(HookNames::TEMPLATE_INCLUDE, $routes[1]->get_hook_name());
        $this->assertSame(20, $routes[1]->get_hook_priority());
    }

    public function test_site_url_builds_named_path(): void
    {
        Route::site(function () {
            Route::get('shop/products/{id}', [SiteRouterTestController::class, 'show'])
                ->name('products.show');
        });

        $router = new SiteRouter('framework');
        $router->boot(Route::get_site_routes());
        Route::set_site_router($router);

        $url = Route::site_url('products.show', ['id' => 42]);

        $this->assertStringContainsString('shop/products/42', $url);
    }
}

class SiteRouterTestController
{
    public function show(RequestContract $request)
    {
        return $request;
    }
}

class SiteRouterTestMiddleware implements Middleware
{
    /**
     * @var bool
     */
    public static $called = false;

    public function handle(RequestContract $request, callable $next)
    {
        static::$called = true;

        return $next($request);
    }
}
