<?php

namespace Framework\Tests\Unit\Http;

use Framework\Http\Cookie;
use Framework\Http\RedirectResponse;
use Framework\Http\Response;
use Framework\Managers\CookieManager;
use Framework\Supports\Facades\Cookie as CookieFacade;
use Framework\Tests\Support\Http\RecordingCookieManager;
use Framework\Tests\Unit\TestCase;

use function Framework\cookie;

class ResponseCookieTest extends TestCase
{
    /**
     * The recording manager bound into the container.
     *
     * @var RecordingCookieManager
     */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $app = $this->bootstrap_application();

        $this->manager = new RecordingCookieManager();
        $app->instance(CookieManager::class, $this->manager);
        $app->instance('cookie', $this->manager);
    }

    public function test_cookie_helper_without_arguments_returns_the_manager(): void
    {
        $this->assertSame($this->manager, cookie());
    }

    public function test_cookie_helper_builds_a_cookie_without_queueing_it(): void
    {
        $cookie = cookie('session', 'abc', 60);

        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertSame('session', $cookie->get_name());
        $this->assertSame('abc', $cookie->get_value());
        $this->assertFalse($this->manager->has_queued('session'));
    }

    public function test_facade_proxies_to_the_manager(): void
    {
        CookieFacade::queue('session', 'abc', 60);

        $this->assertTrue($this->manager->has_queued('session'));
    }

    public function test_with_cookie_queues_a_cookie_instance(): void
    {
        $response = (new Response())->with_cookie(cookie('session', 'abc', 60));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($this->manager->has_queued('session'));
        $this->assertSame('abc', $this->manager->queued('session')->get_value());
    }

    public function test_with_cookie_accepts_factory_arguments(): void
    {
        (new Response())->with_cookie('session', 'abc', 60);

        $this->assertTrue($this->manager->has_queued('session'));
    }

    public function test_with_cookies_accepts_pairs_and_instances(): void
    {
        (new Response())->with_cookies([
            'plain' => 'value',
            cookie('object', 'from-cookie', 10),
        ]);

        $this->assertTrue($this->manager->has_queued('plain'));
        $this->assertTrue($this->manager->has_queued('object'));
        $this->assertSame('value', $this->manager->queued('plain')->get_value());
    }

    public function test_without_cookie_removes_an_attached_cookie(): void
    {
        $response = (new Response())->with_cookie('session', 'abc', 60);
        $response->without_cookie('session');

        $this->assertFalse($this->manager->has_queued('session'));

        $this->manager->flush_queued_cookies();

        $this->assertSame([], $this->manager->sent);
    }

    public function test_json_response_carries_cookies_into_the_queue(): void
    {
        $response = (new Response())->json(['ok' => true]);
        $response->with_cookie('session', 'abc', 60);

        $this->assertTrue($this->manager->has_queued('session'));

        $this->manager->flush_queued_cookies();

        $this->assertSame(['session'], $this->manager->sent_names());
    }

    public function test_redirect_response_flushes_queued_cookies_before_redirecting(): void
    {
        $redirect = new RedirectResponse('https://example.test/next');
        $redirect->with_cookie('session', 'abc', 60);

        $this->assertTrue($this->manager->has_queued('session'));

        // send() exits, so exercise the flush the same way send() does.
        $this->manager->flush_queued_cookies();

        $this->assertSame(['session'], $this->manager->sent_names());
        $this->assertSame([], $this->manager->get_queued_cookies());
    }
}
