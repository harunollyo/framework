<?php

namespace Framework\Tests\Unit\Managers;


use Framework\Http\Cookie;
use Framework\Tests\Support\Http\RecordingCookieManager;
use Framework\Tests\Unit\TestCase;

class CookieManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The manager resolves defaults through config(), which needs a real Application.
        $this->bootstrap_application();

        $GLOBALS['framework_test_is_ssl'] = false;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['framework_test_is_ssl']);

        parent::tearDown();
    }

    protected function manager(): RecordingCookieManager
    {
        $manager = new RecordingCookieManager();
        $manager->set_default_path_and_domain('/', null, false, Cookie::SAME_SITE_LAX);

        return $manager;
    }

    public function test_make_converts_minutes_into_an_expiry_timestamp(): void
    {
        $cookie = $this->manager()->make('session', 'abc', 60);

        $this->assertSame('session', $cookie->get_name());
        $this->assertSame('abc', $cookie->get_value());
        $this->assertEqualsWithDelta(time() + 3600, $cookie->get_expires_time(), 5);
    }

    public function test_make_without_minutes_produces_a_session_cookie(): void
    {
        $cookie = $this->manager()->make('session', 'abc');

        $this->assertTrue($cookie->is_session());
        $this->assertSame(0, $cookie->get_expires_time());
    }

    public function test_forever_lives_years_into_the_future(): void
    {
        $cookie = $this->manager()->forever('remember', 'token');

        $this->assertGreaterThan(time() + (60 * 60 * 24 * 365 * 4), $cookie->get_expires_time());
    }

    public function test_forget_produces_an_expired_empty_cookie(): void
    {
        $cookie = $this->manager()->forget('session', '/shop', 'example.test');

        $this->assertSame('', $cookie->get_value());
        $this->assertTrue($cookie->is_expired());
        $this->assertSame('/shop', $cookie->get_path());
        $this->assertSame('example.test', $cookie->get_domain());
    }

    public function test_queue_accepts_a_cookie_instance(): void
    {
        $manager = $this->manager();
        $manager->queue($manager->make('session', 'abc', 10));

        $this->assertTrue($manager->has_queued('session'));
        $this->assertInstanceOf(Cookie::class, $manager->queued('session'));
        $this->assertSame('abc', $manager->queued('session')->get_value());
    }

    public function test_queue_accepts_factory_arguments(): void
    {
        $manager = $this->manager();
        $manager->queue('session', 'abc', 10);

        $this->assertTrue($manager->has_queued('session'));
        $this->assertSame('abc', $manager->queued('session')->get_value());
    }

    public function test_queueing_does_not_emit(): void
    {
        $manager = $this->manager();
        $manager->queue('session', 'abc', 10);

        $this->assertSame([], $manager->sent);
    }

    public function test_unqueue_removes_a_cookie_before_it_is_emitted(): void
    {
        $manager = $this->manager();
        $manager->queue('session', 'abc', 10);
        $manager->unqueue('session');

        $this->assertFalse($manager->has_queued('session'));

        $manager->flush_queued_cookies();

        $this->assertSame([], $manager->sent);
    }

    public function test_queued_returns_the_default_when_absent(): void
    {
        $manager = $this->manager();

        $this->assertNull($manager->queued('missing'));
        $this->assertSame('fallback', $manager->queued('missing', 'fallback'));
        $this->assertFalse($manager->has_queued('missing'));
    }

    public function test_same_name_on_different_paths_are_both_kept(): void
    {
        $manager = $this->manager();
        $manager->queue('flag', 'shop', 10, '/shop');
        $manager->queue('flag', 'blog', 10, '/blog');

        $this->assertCount(2, $manager->get_queued_cookies());
        $this->assertSame('shop', $manager->queued('flag', null, '/shop')->get_value());
        $this->assertSame('blog', $manager->queued('flag', null, '/blog')->get_value());

        $manager->flush_queued_cookies();

        $this->assertCount(2, $manager->sent);
    }

    public function test_unqueue_with_a_path_removes_only_that_path(): void
    {
        $manager = $this->manager();
        $manager->queue('flag', 'shop', 10, '/shop');
        $manager->queue('flag', 'blog', 10, '/blog');
        $manager->unqueue('flag', '/shop');

        $this->assertFalse($manager->has_queued('flag', '/shop'));
        $this->assertTrue($manager->has_queued('flag', '/blog'));
        $this->assertCount(1, $manager->get_queued_cookies());
    }

    public function test_flush_emits_then_clears_the_queue(): void
    {
        $manager = $this->manager();
        $manager->queue('a', '1', 10);
        $manager->queue('b', '2', 10);

        $manager->flush_queued_cookies();

        $this->assertSame(['a', 'b'], $manager->sent_names());
        $this->assertSame([], $manager->get_queued_cookies());

        $manager->flush_queued_cookies();

        $this->assertCount(2, $manager->sent);
    }

    public function test_expire_queues_a_deletion_cookie(): void
    {
        $manager = $this->manager();
        $manager->expire('session');

        $this->assertTrue($manager->has_queued('session'));
        $this->assertTrue($manager->queued('session')->is_expired());
    }

    public function test_headers_already_sent_skips_the_write_and_logs_a_warning(): void
    {
        $manager = $this->manager();
        $manager->headers_sent = true;
        $manager->queue('session', 'abc', 10);

        $manager->flush_queued_cookies();

        $this->assertSame([], $manager->sent);
        $this->assertCount(1, $manager->warnings);
        $this->assertStringContainsString('session', $manager->warnings[0]);
    }

    public function test_defaults_are_applied_to_new_cookies(): void
    {
        $manager = $this->manager();
        $manager->set_default_path_and_domain('/shop', 'example.test', true, Cookie::SAME_SITE_STRICT);

        $cookie = $manager->make('a', 'b', 10);

        $this->assertSame('/shop', $cookie->get_path());
        $this->assertSame('example.test', $cookie->get_domain());
        $this->assertTrue($cookie->is_secure());
        $this->assertSame(Cookie::SAME_SITE_STRICT, $cookie->get_same_site());
    }

    public function test_per_cookie_arguments_override_the_defaults(): void
    {
        $manager = $this->manager();
        $manager->set_default_path_and_domain('/shop', 'example.test', true, Cookie::SAME_SITE_STRICT);

        $cookie = $manager->make('a', 'b', 10, '/blog', 'other.test', false, true, false, Cookie::SAME_SITE_LAX);

        $this->assertSame('/blog', $cookie->get_path());
        $this->assertSame('other.test', $cookie->get_domain());
        $this->assertFalse($cookie->is_secure());
        $this->assertSame(Cookie::SAME_SITE_LAX, $cookie->get_same_site());
    }

    public function test_defaults_fall_back_to_the_wordpress_environment(): void
    {
        $manager = new RecordingCookieManager();

        $defaults = $manager->get_defaults();

        $this->assertSame('/wp-subdir/', $defaults['path']);
        $this->assertSame('example.test', $defaults['domain']);
        $this->assertFalse($defaults['secure']);
        $this->assertSame(Cookie::SAME_SITE_LAX, $defaults['same_site']);
    }

    public function test_secure_default_follows_the_tls_state(): void
    {
        $GLOBALS['framework_test_is_ssl'] = true;

        $manager = new RecordingCookieManager();

        $this->assertTrue($manager->get_defaults()['secure']);
        $this->assertTrue($manager->make('a', 'b', 10)->is_secure());
    }

    public function test_raw_cookies_are_recorded_with_their_raw_flag(): void
    {
        $manager = $this->manager();
        $manager->queue($manager->make('a', 'hello world', 10, null, null, null, true, true));

        $manager->flush_queued_cookies();

        $this->assertTrue($manager->sent[0]->is_raw());
    }
}
