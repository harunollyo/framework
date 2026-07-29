<?php

namespace Framework\Tests\Unit\Http;

use Framework\Http\Request;
use Framework\Tests\Unit\TestCase;

class RequestHttpTest extends TestCase
{
    public function test_make_from_http_merges_query_body_and_route_params(): void
    {
        $request = (new Request())->make_from_http(
            ['q' => 'search'],
            ['name' => 'John'],
            [],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/shop/products/5'],
            ['id' => 5]
        );

        $this->assertSame('POST', $request->get_method());
        $this->assertSame('search', $request->get('q'));
        $this->assertSame('John', $request->get('name'));
        $this->assertSame(5, $request->route('id'));
        $this->assertSame(['id' => 5], $request->route_params());
    }

    public function test_set_route_params_merges_into_attributes(): void
    {
        $request = (new Request())->make_from_http([], [], [], ['REQUEST_METHOD' => 'GET']);
        $request->set_route_params(['slug' => 'hello']);

        $this->assertSame('hello', $request->get('slug'));
        $this->assertSame('hello', $request->route('slug'));
    }

    public function test_capture_reads_superglobals(): void
    {
        $_GET = ['page' => '1'];
        $_POST = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/hello';

        $request = Request::capture();

        $this->assertSame('GET', $request->get_method());
        $this->assertSame('1', $request->get('page'));

        $_GET = [];
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }
}
