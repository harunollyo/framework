<?php

namespace Framework\Tests\Unit\Http;

use Framework\Http\Request;
use Framework\Tests\Unit\TestCase;
use WP_REST_Request;

class RequestCookieTest extends TestCase
{
    protected function tearDown(): void
    {
        $_COOKIE = [];

        parent::tearDown();
    }

    protected function make_request(array $cookies = []): Request
    {
        return (new Request())->make_from_http(
            ['q' => 'search'],
            ['name' => 'John'],
            [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/shop'],
            [],
            $cookies
        );
    }

    public function test_cookie_is_read_by_name(): void
    {
        $request = $this->make_request(['session' => 'abc123']);

        $this->assertSame('abc123', $request->cookie('session'));
        $this->assertTrue($request->has_cookie('session'));
    }

    public function test_missing_cookie_returns_the_default(): void
    {
        $request = $this->make_request(['session' => 'abc123']);

        $this->assertNull($request->cookie('missing'));
        $this->assertSame('fallback', $request->cookie('missing', 'fallback'));
        $this->assertFalse($request->has_cookie('missing'));
    }

    public function test_all_cookies_are_returned(): void
    {
        $request = $this->make_request(['a' => '1', 'b' => '2']);

        $this->assertSame(['a' => '1', 'b' => '2'], $request->cookies());
        $this->assertSame(['a' => '1', 'b' => '2'], $request->cookie());
    }

    public function test_cookie_values_are_unslashed(): void
    {
        $request = $this->make_request(['quoted' => 'He said \\"hello\\"']);

        $this->assertSame('He said "hello"', $request->cookie('quoted'));
    }

    public function test_cookies_are_absent_from_request_input(): void
    {
        $request = $this->make_request(['name' => 'Attacker', 'admin' => '1']);

        $this->assertSame('John', $request->get('name'));
        $this->assertSame('John', $request->all()['name']);
        $this->assertNull($request->get('admin'));
        $this->assertArrayNotHasKey('admin', $request->all());
    }

    public function test_cookies_are_absent_from_magic_property_access(): void
    {
        $request = $this->make_request(['admin' => '1']);

        $this->assertNull($request->admin);
        $this->assertFalse(isset($request->admin));
    }

    public function test_cookie_does_not_override_a_route_param_or_query(): void
    {
        $request = $this->make_request(['q' => 'injected']);

        $this->assertSame('search', $request->get('q'));
        $this->assertSame('injected', $request->cookie('q'));
    }

    public function test_capture_reads_the_cookie_superglobal(): void
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_COOKIE = ['session' => 'from-global'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/hello';

        $request = Request::capture();

        $this->assertSame('from-global', $request->cookie('session'));
        $this->assertArrayNotHasKey('session', $request->all());

        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }

    public function test_rest_request_reads_the_cookie_superglobal(): void
    {
        $_COOKIE = ['session' => 'rest-cookie'];

        $request = Request::from_wp_rest_request(
            new WP_REST_Request('GET', '/shop/products', ['id' => 5])
        );

        $this->assertSame('rest-cookie', $request->cookie('session'));
        $this->assertTrue($request->has_cookie('session'));
        $this->assertArrayNotHasKey('session', $request->all());
    }

    public function test_request_without_cookies_has_an_empty_bag(): void
    {
        $request = $this->make_request();

        $this->assertSame([], $request->cookies());
        $this->assertFalse($request->has_cookie('anything'));
    }
}
