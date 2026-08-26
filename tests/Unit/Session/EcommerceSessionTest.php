<?php

namespace Framework\Tests\Unit\Session;

use Framework\Contracts\SessionHandler;
use Framework\Exceptions\ValidationException;
use Framework\Http\Cookie;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Managers\CookieManager;
use Framework\Managers\SessionManager;
use Framework\Session\Handlers\ArraySessionHandler;
use Framework\Supports\ErrorBag;
use Framework\Tests\Support\Http\RecordingCookieManager;
use Framework\Tests\Support\Session\RecordingSessionHandler;
use Framework\Tests\Support\Session\TestSessionManager;
use Framework\Tests\Unit\TestCase;

use function Framework\app;

/**
 * The session cases a shop actually exercises: cart, coupons, checkout steps,
 * flash notices, form redisplay, and the login and logout transitions.
 *
 * Each test maps to a numbered row in docs/sessions-ecommerce.md.
 */
class EcommerceSessionTest extends TestCase
{
    protected RecordingSessionHandler $handler;

    protected RecordingCookieManager $cookies;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrap_application();

        $this->handler = new RecordingSessionHandler();
        $this->cookies = new RecordingCookieManager();
        $this->cookies->set_default_path_and_domain('/', null, false, 'Lax');

        app()->instance(CookieManager::class, $this->cookies);
        app()->instance(SessionHandler::class, $this->handler);
    }

    /**
     * Build the session for one request and bind it the way the container would.
     */
    protected function shop(array $request_cookies = []): TestSessionManager
    {
        $manager = new TestSessionManager($this->handler);
        $manager->request_cookies = $request_cookies;

        app()->instance(SessionManager::class, $manager);
        app()->instance('session', $manager);

        return $manager;
    }

    /**
     * Build the next request from the same visitor, carrying their cookie back.
     */
    protected function next_request(TestSessionManager $previous): TestSessionManager
    {
        return $this->shop([$previous->get_name() => $previous->get_id()]);
    }

    /**
     * Build a visitor who already holds a session with the given payload.
     */
    protected function returning_shopper(array $payload): TestSessionManager
    {
        $id = str_pad('', 40, 'b');
        $this->handler->seed($id, $payload);

        $manager = $this->shop([]);
        $manager->request_cookies = [$manager->get_name() => $id];

        return $manager;
    }

    /**
     * Bind an HTTP request so the flash-data trait can read its input.
     */
    protected function request_with(array $attributes, array $files = []): Request
    {
        $request = new Request();
        $request->make_from_http([], $attributes, $files, [], [], []);

        app()->instance(Request::class, $request);
        app()->instance('request', $request);

        return $request;
    }

    /**
     * Get the queued cookie carrying the session identifier, if any.
     */
    protected function queued_session_cookie(TestSessionManager $manager): ?Cookie
    {
        foreach ($this->cookies->get_queued_cookies() as $cookie) {
            if ($cookie->get_name() === $manager->get_name()) {
                return $cookie;
            }
        }

        return null;
    }

    // ----------------------------------------------------------------- 1-8 cart

    public function test_browsing_without_adding_anything_creates_nothing(): void
    {
        $shop = $this->shop();

        $this->assertSame([], $shop->get('cart.items', []));
        $this->assertFalse($shop->is_started());
        $this->assertFalse($shop->save());

        $this->assertSame([], $this->handler->writes);
        $this->assertSame([], $this->handler->reads);
        $this->assertSame([], $this->cookies->get_queued_cookies());
    }

    public function test_adding_a_product_writes_one_payload_and_one_cookie(): void
    {
        $shop = $this->shop();

        $shop->put('cart.items.sku-10', ['product_id' => 10, 'quantity' => 1]);

        $this->assertTrue($shop->save());
        $this->assertCount(1, $this->handler->writes);

        $payload = $this->handler->last_written_payload();
        $this->assertSame(1, $payload['cart']['items']['sku-10']['quantity']);

        $cookie = $this->queued_session_cookie($shop);
        $this->assertNotNull($cookie);
        $this->assertSame($shop->get_id(), $cookie->get_value());
    }

    public function test_the_cart_reads_back_on_the_next_request(): void
    {
        $first = $this->shop();
        $first->put('cart.items.sku-10', ['product_id' => 10, 'quantity' => 2]);
        $first->save();

        $second = $this->next_request($first);

        $this->assertSame(2, $second->get('cart.items.sku-10.quantity'));
        $this->assertSame($first->get_id(), $second->get_id());
    }

    public function test_adding_the_same_product_twice_accumulates_quantity(): void
    {
        $shop = $this->shop();

        $shop->put('cart.items.sku-10', ['product_id' => 10, 'quantity' => 1]);

        $current = $shop->get('cart.items.sku-10.quantity', 0);
        $shop->put('cart.items.sku-10.quantity', $current + 2);

        $this->assertSame(3, $shop->get('cart.items.sku-10.quantity'));
        $this->assertCount(1, $shop->get('cart.items'));
    }

    public function test_a_second_product_coexists_with_the_first(): void
    {
        $shop = $this->shop();

        $shop->put('cart.items.sku-10', ['quantity' => 1]);
        $shop->put('cart.items.sku-20', ['quantity' => 4]);

        $this->assertSame(
            ['sku-10', 'sku-20'],
            array_keys($shop->get('cart.items'))
        );
    }

    public function test_removing_one_product_leaves_the_other(): void
    {
        $shop = $this->returning_shopper([
            'cart' => ['items' => [
                'sku-10' => ['quantity' => 1],
                'sku-20' => ['quantity' => 4],
            ]],
        ]);

        $shop->forget('cart.items.sku-10');

        $this->assertSame(['sku-20'], array_keys($shop->get('cart.items')));
        $this->assertTrue($shop->save());
    }

    public function test_clearing_the_cart_prunes_the_session(): void
    {
        $shop = $this->returning_shopper([
            'cart' => ['items' => ['sku-10' => ['quantity' => 1]]],
        ]);

        $id = $shop->get_id();
        $shop->forget('cart');

        $this->assertFalse($shop->save());
        $this->assertSame([], $this->handler->writes);
        $this->assertContains($id, $this->handler->destroys);

        $cookie = $this->queued_session_cookie($shop);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->is_expired());
    }

    public function test_reading_an_unset_cart_key_returns_the_default_without_starting(): void
    {
        $shop = $this->shop();

        $this->assertSame('USD', $shop->get('checkout.currency', 'USD'));
        $this->assertTrue($shop->missing('checkout.currency'));
        $this->assertFalse($shop->is_started());
        $this->assertSame([], $this->handler->reads);
    }

    // --------------------------------------------------- 9-15 values and counters

    public function test_push_appends_to_recently_viewed(): void
    {
        $shop = $this->shop();

        $shop->push('recently_viewed', 'blue-shirt');
        $shop->push('recently_viewed', 'red-hat');

        $this->assertSame(['blue-shirt', 'red-hat'], $shop->get('recently_viewed'));
    }

    public function test_increment_and_decrement_apply_arithmetic(): void
    {
        $shop = $this->shop();

        $this->assertSame(2, $shop->increment('cart.count', 2));
        $this->assertSame(3, $shop->increment('cart.count'));
        $this->assertSame(1, $shop->decrement('cart.count', 2));

        $shop->save();

        $this->assertSame(1, $this->handler->last_written_payload()['cart']['count']);
    }

    public function test_remember_runs_the_callback_once(): void
    {
        $shop = $this->shop();
        $calls = 0;

        $resolver = function () use (&$calls) {
            $calls++;

            return 'zone-eu';
        };

        $this->assertSame('zone-eu', $shop->remember('shipping.zone', $resolver));
        $this->assertSame('zone-eu', $shop->remember('shipping.zone', $resolver));
        $this->assertSame(1, $calls);
    }

    public function test_pull_consumes_a_coupon(): void
    {
        $shop = $this->shop();
        $shop->put('checkout.coupon', 'SUMMER10');

        $this->assertSame('SUMMER10', $shop->pull('checkout.coupon'));
        $this->assertFalse($shop->exists('checkout.coupon'));
        $this->assertSame('none', $shop->pull('checkout.coupon', 'none'));
    }

    public function test_has_and_exists_differ_on_a_null_value(): void
    {
        $shop = $this->shop();
        $shop->put('checkout.gift_note', null);

        $this->assertFalse($shop->has('checkout.gift_note'));
        $this->assertTrue($shop->exists('checkout.gift_note'));
        $this->assertTrue($shop->missing('checkout.never_set'));
    }

    public function test_only_returns_the_requested_keys(): void
    {
        $shop = $this->shop();
        $shop->put(['cart' => ['items' => []], 'currency' => 'USD', 'locale' => 'bn']);

        $this->assertSame(
            ['cart' => ['items' => []], 'currency' => 'USD'],
            $shop->only(['cart', 'currency'])
        );
    }

    public function test_flush_clears_data_but_keeps_the_identifier(): void
    {
        $shop = $this->returning_shopper(['cart' => ['items' => ['sku-10' => []]]]);
        $id = $shop->get_id();

        $shop->flush();

        $this->assertNull($shop->get('cart'));
        $this->assertSame($id, $shop->get_id());
    }

    // ------------------------------------------------------------- 16-20 flash

    public function test_a_flashed_notice_is_readable_on_the_next_request(): void
    {
        $first = $this->shop();
        $first->flash('notice', 'Added to your cart.');
        $first->save();

        $second = $this->next_request($first);

        $this->assertSame('Added to your cart.', $second->get('notice'));
    }

    public function test_a_flashed_notice_is_gone_one_request_later(): void
    {
        $first = $this->shop();
        $first->flash('notice', 'Added to your cart.');
        $first->save();

        $second = $this->next_request($first);
        $second->get('notice');
        $second->save();

        $third = $this->next_request($second);

        $this->assertNull($third->get('notice'));
        $this->assertContains($second->get_id(), $this->handler->destroys);
    }

    public function test_now_is_readable_this_request_and_not_stored(): void
    {
        $shop = $this->shop();
        $shop->put('cart.items.sku-10', ['quantity' => 1]);
        $shop->now('error', 'That size just sold out.');

        $this->assertSame('That size just sold out.', $shop->get('error'));

        $shop->save();

        $payload = $this->handler->last_written_payload();
        $this->assertArrayNotHasKey('error', $payload);
        $this->assertArrayHasKey('cart', $payload);
    }

    public function test_reflash_carries_a_notice_through_a_detour(): void
    {
        $first = $this->shop();
        $first->put('cart.items.sku-10', ['quantity' => 1]);
        $first->flash('notice', 'Please sign in to check out.');
        $first->save();

        $detour = $this->next_request($first);
        $detour->reflash();
        $detour->save();

        $third = $this->next_request($detour);

        $this->assertSame('Please sign in to check out.', $third->get('notice'));
    }

    public function test_keep_preserves_only_the_named_value(): void
    {
        $first = $this->shop();
        $first->put('cart.items.sku-10', ['quantity' => 1]);
        $first->flash('notice', 'Kept.');
        $first->flash('warning', 'Dropped.');
        $first->save();

        $detour = $this->next_request($first);
        $detour->keep('notice');
        $detour->save();

        $third = $this->next_request($detour);

        $this->assertSame('Kept.', $third->get('notice'));
        $this->assertNull($third->get('warning'));
    }

    // ------------------------------------------------------------- 21-25 forms

    public function test_with_input_makes_checkout_fields_readable_as_old_input(): void
    {
        $shop = $this->shop();

        $this->request_with([
            'email' => 'ada@example.test',
            'postcode' => 'E1 6AN',
        ]);

        (new RedirectResponse('/checkout'))->with_input();
        $shop->save();

        $next = $this->next_request($shop);

        $this->assertSame('ada@example.test', $next->get_old_input('email'));
        $this->assertSame('E1 6AN', $next->get_old_input('postcode'));
        $this->assertTrue($next->has_old_input());
    }

    public function test_a_configured_dont_flash_field_never_reaches_storage(): void
    {
        $shop = new class ($this->handler) extends TestSessionManager {
            protected function resolve_defaults()
            {
                $defaults = parent::resolve_defaults();
                $defaults['dont_flash'][] = 'card_number';

                return $defaults;
            }
        };

        app()->instance(SessionManager::class, $shop);

        $this->request_with([
            'email' => 'ada@example.test',
            'card_number' => '4242424242424242',
        ]);

        (new RedirectResponse('/checkout'))->with_input();
        $shop->save();

        $payload = $this->handler->last_written_payload();

        $this->assertSame('ada@example.test', $payload['_old_input']['email']);
        $this->assertArrayNotHasKey('card_number', $payload['_old_input']);
        $this->assertStringNotContainsString('4242424242424242', serialize($payload));
    }

    public function test_an_explicit_input_array_is_still_filtered(): void
    {
        $shop = $this->shop();

        $this->request_with([]);

        (new RedirectResponse('/checkout'))->with_input([
            'email' => 'ada@example.test',
            'password' => 'hunter2',
        ]);

        $shop->save();

        $payload = $this->handler->last_written_payload();

        $this->assertSame('ada@example.test', $payload['_old_input']['email']);
        $this->assertArrayNotHasKey('password', $payload['_old_input']);
        $this->assertStringNotContainsString('hunter2', serialize($payload));
    }

    public function test_with_errors_stores_plain_arrays_readable_through_an_error_bag(): void
    {
        $shop = $this->shop();

        $exception = ValidationException::with_errors([
            'email' => ['The email must be a valid address.'],
            'postcode' => ['The postcode is required.'],
        ], 'Validation failed');

        (new RedirectResponse('/checkout'))->with_errors($exception->get_errors());
        $shop->save();

        $payload = $this->handler->last_written_payload();
        $this->assertIsArray($payload['errors']);
        $this->assertStringNotContainsString('Object', serialize($payload['errors']));

        $next = $this->next_request($shop);
        $bag = new ErrorBag($next->get('errors', []));

        $this->assertTrue($bag->any());
        $this->assertTrue($bag->has('email'));
        $this->assertFalse($bag->has('name'));
        $this->assertSame('The email must be a valid address.', $bag->first('email'));
        $this->assertSame(['email', 'postcode'], $bag->keys());
        $this->assertCount(2, $bag);
    }

    public function test_flash_only_and_flash_except_store_exact_subsets(): void
    {
        $shop = $this->shop();

        $request = $this->request_with([
            'email' => 'ada@example.test',
            'postcode' => 'E1 6AN',
            'newsletter' => '1',
        ]);

        $request->flash_only(['email']);
        $shop->save();

        $this->assertSame(
            ['email' => 'ada@example.test'],
            $this->handler->last_written_payload()['_old_input']
        );

        $second = $this->next_request($shop);
        $request->flash_except(['newsletter']);
        $second->save();

        $this->assertSame(
            ['email' => 'ada@example.test', 'postcode' => 'E1 6AN'],
            $this->handler->last_written_payload()['_old_input']
        );
    }

    public function test_a_flash_survives_the_double_save_on_the_redirect_path(): void
    {
        $shop = $this->shop();
        $shop->put('cart.items.sku-10', ['quantity' => 1]);
        $shop->flash('notice', 'Added to your cart.');

        // SiteRouter::send_response() flushes cookies, which saves.
        $shop->save();
        // RedirectResponse::send() then saves again before redirecting.
        $shop->save();

        $payload = $this->handler->last_written_payload();

        $this->assertSame('Added to your cart.', $payload['notice']);
        $this->assertSame(['notice'], $payload['_flash']['old']);
    }

    public function test_a_flash_stays_readable_after_the_save_that_precedes_rendering(): void
    {
        $first = $this->shop();
        $first->put('cart.items.sku-10', ['quantity' => 1]);
        $first->flash('notice', 'Added to your cart.');
        $first->save();

        $second = $this->next_request($first);

        // The controller reads the cart, which starts the session.
        $this->assertSame(1, $second->get('cart.items.sku-10.quantity'));

        // The router then saves and flushes before the view is rendered.
        $second->save();

        // The template renders after that and must still see the notice.
        $this->assertSame('Added to your cart.', $second->get('notice'));
    }

    // ---------------------------------------------------------- 26-30 identity

    public function test_login_migration_keeps_the_cart_under_a_new_id(): void
    {
        $shop = $this->returning_shopper(['cart' => ['items' => ['sku-10' => ['quantity' => 1]]]]);
        $guest_id = $shop->get_id();

        $shop->migrate(false);

        $this->assertNotSame($guest_id, $shop->get_id());
        $this->assertSame(1, $shop->get('cart.items.sku-10.quantity'));
        $this->assertSame([], $this->handler->destroys);
        $this->assertTrue($shop->save());
    }

    public function test_logout_invalidation_destroys_the_cart(): void
    {
        $shop = $this->returning_shopper(['cart' => ['items' => ['sku-10' => ['quantity' => 1]]]]);
        $customer_id = $shop->get_id();

        $shop->invalidate();

        $this->assertNotSame($customer_id, $shop->get_id());
        $this->assertNull($shop->get('cart'));
        $this->assertContains($customer_id, $this->handler->destroys);

        $this->assertFalse($shop->save());
        $this->assertSame([], $this->handler->writes);
    }

    public function test_regenerate_with_destroy_keeps_data_and_removes_the_old_payload(): void
    {
        $shop = $this->returning_shopper(['cart' => ['items' => ['sku-10' => ['quantity' => 1]]]]);
        $old_id = $shop->get_id();

        $shop->regenerate(true);

        $this->assertNotSame($old_id, $shop->get_id());
        $this->assertSame(1, $shop->get('cart.items.sku-10.quantity'));
        $this->assertSame([$old_id], $this->handler->destroys);
    }

    public function test_a_tampered_identifier_cookie_is_rejected_without_a_storage_read(): void
    {
        $probe = $this->shop();
        $name = $probe->get_name();

        foreach (["'; DROP TABLE wp_options; --", 'zzzz', str_pad('', 41, 'a'), ''] as $tampered) {
            $this->handler->reads = [];

            $shop = $this->shop([$name => $tampered]);

            $this->assertNull($shop->get('cart'));
            $this->assertFalse($shop->is_started());
            $this->assertFalse($shop->has_session());
            $this->assertSame([], $this->handler->reads);
        }
    }

    public function test_has_session_reflects_the_incoming_cookie(): void
    {
        $shop = $this->shop();
        $this->assertFalse($shop->has_session());

        $shop->put('cart.items.sku-10', ['quantity' => 1]);
        $this->assertTrue($shop->has_session());

        $returning = $this->returning_shopper(['cart' => []]);
        $this->assertTrue($returning->has_session());
        $this->assertFalse($returning->is_started());
    }

    // ------------------------------------------------------- 31-35 persistence

    public function test_saving_twice_without_changes_writes_once(): void
    {
        $shop = $this->shop();
        $shop->put('cart.items.sku-10', ['quantity' => 1]);

        $this->assertTrue($shop->save());
        $this->assertFalse($shop->save());
        $this->assertCount(1, $this->handler->writes);
    }

    public function test_a_new_session_after_headers_are_sent_is_skipped_with_a_warning(): void
    {
        $shop = $this->shop();
        $shop->headers_sent = true;

        $shop->put('cart.items.sku-10', ['quantity' => 1]);

        $this->assertFalse($shop->save());
        $this->assertSame([], $this->handler->writes);
        $this->assertCount(1, $shop->warnings);
        $this->assertStringContainsString('headers were already sent', $shop->warnings[0]);
    }

    public function test_an_existing_session_still_saves_after_headers_are_sent(): void
    {
        $shop = $this->returning_shopper(['cart' => ['items' => []]]);
        $shop->headers_sent = true;

        $shop->put('cart.items.sku-10', ['quantity' => 1]);

        $this->assertTrue($shop->save());
        $this->assertCount(1, $this->handler->writes);
        $this->assertSame([], $shop->warnings);
    }

    public function test_the_payload_lifetime_comes_from_configuration(): void
    {
        $shop = $this->shop();
        $shop->put('cart.items.sku-10', ['quantity' => 1]);
        $shop->save();

        $expected = (int) $shop->get_defaults()['lifetime'] * 60;

        $this->assertSame($expected, $shop->get_lifetime_seconds());
        $this->assertSame($expected, $this->handler->writes[0]['lifetime']);
    }

    public function test_the_array_driver_keeps_nothing_between_requests(): void
    {
        $first_handler = new ArraySessionHandler();

        $first = new TestSessionManager($first_handler);
        $first->put('cart.items.sku-10', ['quantity' => 1]);

        $this->assertTrue($first->save());
        $this->assertSame(1, $first->get('cart.items.sku-10.quantity'));

        // A new request rebuilds the container, so the driver starts empty.
        $second = new TestSessionManager(new ArraySessionHandler());
        $second->request_cookies = [$second->get_name() => $first->get_id()];

        $this->assertNull($second->get('cart'));
    }
}
