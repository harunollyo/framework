<?php

namespace Framework\Tests\Unit\Http;

use Framework\Http\Client\Request as ClientRequest;
use Framework\Http\Cookie;
use Framework\Tests\Unit\TestCase;
use WP_Http_Cookie;

class ClientCookieTest extends TestCase
{
    protected function cookies(ClientRequest $request): array
    {
        $reflection = new \ReflectionClass(ClientRequest::class);
        $property = $reflection->getProperty('options');
        $property->setAccessible(true);

        return $property->getValue($request)['cookies'] ?? [];
    }

    public function test_with_cookie_adds_a_name_and_value_pair(): void
    {
        $request = (new ClientRequest())->with_cookie('session', 'abc123');

        $cookies = $this->cookies($request);

        $this->assertArrayHasKey('session', $cookies);
        $this->assertInstanceOf(WP_Http_Cookie::class, $cookies['session']);
        $this->assertSame('session', $cookies['session']->name);
        $this->assertSame('abc123', $cookies['session']->value);
    }

    public function test_with_cookie_accepts_a_domain(): void
    {
        $request = (new ClientRequest())->with_cookie('session', 'abc', 'api.example.test');

        $this->assertSame('api.example.test', $this->cookies($request)['session']->domain);
    }

    public function test_with_cookies_accepts_name_and_value_pairs(): void
    {
        $request = (new ClientRequest())->with_cookies(['a' => '1', 'b' => '2']);

        $cookies = $this->cookies($request);

        $this->assertCount(2, $cookies);
        $this->assertSame('1', $cookies['a']->value);
        $this->assertSame('2', $cookies['b']->value);
    }

    public function test_with_cookies_accepts_framework_cookie_objects(): void
    {
        $expire = time() + 600;
        $cookie = new Cookie('session', 'abc', $expire, '/shop', 'example.test');

        $request = (new ClientRequest())->with_cookies([$cookie]);

        $sent = $this->cookies($request)['session'];

        $this->assertInstanceOf(WP_Http_Cookie::class, $sent);
        $this->assertSame('session', $sent->name);
        $this->assertSame('abc', $sent->value);
        $this->assertSame($expire, $sent->expires);
        $this->assertSame('/shop', $sent->path);
        $this->assertSame('example.test', $sent->domain);
    }

    public function test_with_cookies_accepts_wp_http_cookie_instances_untouched(): void
    {
        $wp_cookie = new WP_Http_Cookie(['name' => 'raw', 'value' => 'kept']);

        $request = (new ClientRequest())->with_cookies([$wp_cookie]);

        $this->assertSame($wp_cookie, $this->cookies($request)['raw']);
    }

    public function test_with_cookies_accepts_mixed_shapes_together(): void
    {
        $request = (new ClientRequest())->with_cookies([
            'plain' => 'value',
            new Cookie('object', 'from-cookie'),
            new WP_Http_Cookie(['name' => 'wp', 'value' => 'from-wp']),
        ], 'example.test');

        $cookies = $this->cookies($request);

        $this->assertCount(3, $cookies);
        $this->assertSame('value', $cookies['plain']->value);
        $this->assertSame('from-cookie', $cookies['object']->value);
        $this->assertSame('from-wp', $cookies['wp']->value);
    }

    public function test_framework_cookie_falls_back_to_the_given_domain(): void
    {
        $request = (new ClientRequest())->with_cookies(
            [new Cookie('session', 'abc')],
            'fallback.test'
        );

        $this->assertSame('fallback.test', $this->cookies($request)['session']->domain);
    }

    public function test_session_cookie_is_sent_without_an_expiry(): void
    {
        $request = (new ClientRequest())->with_cookies([new Cookie('session', 'abc')]);

        $this->assertNull($this->cookies($request)['session']->expires);
    }

    public function test_repeating_a_name_replaces_the_previous_cookie(): void
    {
        $request = (new ClientRequest())
            ->with_cookie('session', 'first')
            ->with_cookie('session', 'second');

        $cookies = $this->cookies($request);

        $this->assertCount(1, $cookies);
        $this->assertSame('second', $cookies['session']->value);
    }

    public function test_requests_start_with_no_cookies(): void
    {
        $this->assertSame([], $this->cookies(new ClientRequest()));
    }
}
