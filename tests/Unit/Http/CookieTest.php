<?php

namespace Framework\Tests\Unit\Http;

use Framework\Http\Cookie;
use Framework\Tests\Unit\TestCase;
use InvalidArgumentException;

class CookieTest extends TestCase
{
    public function test_header_string_contains_the_core_attributes(): void
    {
        $expire = time() + 3600;
        $cookie = new Cookie('session', 'abc123', $expire, '/shop', 'example.test', true, true, false, 'Lax');

        $header = $cookie->to_header_string();

        $this->assertStringContainsString('session=abc123', $header);
        $this->assertStringContainsString('path=/shop', $header);
        $this->assertStringContainsString('domain=example.test', $header);
        $this->assertStringContainsString('; secure', $header);
        $this->assertStringContainsString('; HttpOnly', $header);
        $this->assertStringContainsString('; SameSite=Lax', $header);
        $this->assertStringContainsString('expires=', $header);
        $this->assertStringContainsString('Max-Age=', $header);
    }

    public function test_string_cast_matches_the_header_string(): void
    {
        $cookie = new Cookie('token', 'value');

        $this->assertSame($cookie->to_header_string(), (string) $cookie);
    }

    public function test_same_site_is_normalized_regardless_of_casing(): void
    {
        $lax = new Cookie('a', 'b', 0, '/', null, false, true, false, 'lax');
        $strict = new Cookie('a', 'b', 0, '/', null, false, true, false, 'STRICT');

        $this->assertSame(Cookie::SAME_SITE_LAX, $lax->get_same_site());
        $this->assertSame(Cookie::SAME_SITE_STRICT, $strict->get_same_site());
        $this->assertStringContainsString('SameSite=Strict', $strict->to_header_string());
    }

    public function test_same_site_none_forces_the_secure_flag(): void
    {
        $cookie = new Cookie('a', 'b', 0, '/', null, false, true, false, 'none');

        $this->assertTrue($cookie->is_secure());
        $this->assertStringContainsString('; secure', $cookie->to_header_string());
    }

    public function test_invalid_same_site_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cookie('a', 'b', 0, '/', null, false, true, false, 'sometimes');
    }

    public function test_empty_name_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cookie('');
    }

    public function test_name_with_reserved_characters_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cookie("bad\r\nSet-Cookie: injected");
    }

    public function test_zero_expiry_is_a_session_cookie(): void
    {
        $cookie = new Cookie('a', 'b');

        $this->assertTrue($cookie->is_session());
        $this->assertFalse($cookie->is_expired());
        $this->assertSame(0, $cookie->get_expires_time());
        $this->assertStringNotContainsString('expires=', $cookie->to_header_string());
        $this->assertStringNotContainsString('Max-Age=', $cookie->to_header_string());
    }

    public function test_past_expiry_marks_the_cookie_expired_with_a_zero_max_age(): void
    {
        $cookie = new Cookie('a', '', time() - 3600);

        $this->assertTrue($cookie->is_expired());
        $this->assertFalse($cookie->is_session());
        $this->assertSame(0, $cookie->get_max_age());
        $this->assertStringContainsString('Max-Age=0', $cookie->to_header_string());
    }

    public function test_max_age_reflects_the_remaining_lifetime(): void
    {
        $cookie = new Cookie('a', 'b', time() + 600);

        $this->assertGreaterThan(500, $cookie->get_max_age());
        $this->assertLessThanOrEqual(600, $cookie->get_max_age());
    }

    public function test_value_is_url_encoded_unless_the_cookie_is_raw(): void
    {
        $encoded = new Cookie('a', 'hello world&more');
        $raw = new Cookie('a', 'hello world&more', 0, '/', null, false, true, true);

        $this->assertStringContainsString('a=hello%20world%26more', $encoded->to_header_string());
        $this->assertStringContainsString('a=hello world&more', $raw->to_header_string());
        $this->assertFalse($encoded->is_raw());
        $this->assertTrue($raw->is_raw());
    }

    public function test_empty_path_falls_back_to_root(): void
    {
        $cookie = new Cookie('a', 'b', 0, '');

        $this->assertSame('/', $cookie->get_path());
    }

    public function test_to_options_maps_onto_the_setcookie_options_array(): void
    {
        $expire = time() + 60;
        $cookie = new Cookie('a', 'b', $expire, '/shop', 'example.test', true, false, false, 'Strict');

        $this->assertSame([
            'expires' => $expire,
            'path' => '/shop',
            'domain' => 'example.test',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'Strict',
        ], $cookie->to_options());
    }

    public function test_to_options_omits_same_site_when_it_is_not_set(): void
    {
        $cookie = new Cookie('a', 'b');

        $this->assertArrayNotHasKey('samesite', $cookie->to_options());
        $this->assertSame('', $cookie->to_options()['domain']);
    }

    public function test_http_only_is_omitted_from_the_header_when_disabled(): void
    {
        $cookie = new Cookie('a', 'b', 0, '/', null, false, false);

        $this->assertFalse($cookie->is_http_only());
        $this->assertStringNotContainsString('HttpOnly', $cookie->to_header_string());
    }
}
