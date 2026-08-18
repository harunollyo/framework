<?php

namespace Framework\Tests\Unit\Managers;

use Framework\Managers\CookieManager;
use Framework\Managers\SessionManager;
use Framework\Tests\Support\Http\RecordingCookieManager;
use Framework\Tests\Support\Session\RecordingSessionHandler;
use Framework\Tests\Support\Session\TestSessionManager;
use Framework\Tests\Unit\TestCase;

use function Framework\app;

class SessionManagerTest extends TestCase
{
    protected RecordingSessionHandler $handler;

    protected RecordingCookieManager $cookies;

    protected function setUp(): void
    {
        parent::setUp();

        // The manager resolves defaults through config(), which needs a real Application.
        $this->bootstrap_application();

        $this->handler = new RecordingSessionHandler();
        $this->cookies = new RecordingCookieManager();
        $this->cookies->set_default_path_and_domain('/', null, false, 'Lax');

        app()->instance(CookieManager::class, $this->cookies);
    }

    protected function manager(array $request_cookies = []): TestSessionManager
    {
        $manager = new TestSessionManager($this->handler);
        $manager->request_cookies = $request_cookies;

        return $manager;
    }

    protected function valid_id(string $seed = 'a'): string
    {
        return str_pad('', 40, $seed);
    }

    /**
     * Build a manager that behaves as a returning visitor holding the given payload.
     */
    protected function returning_visitor(array $payload, ?string $id = null): TestSessionManager
    {
        $id = $id ?: $this->valid_id('b');
        $this->handler->seed($id, $payload);

        $manager = new TestSessionManager($this->handler);
        $manager->request_cookies = [$manager->get_name() => $id];

        return $manager;
    }

    // ---------------------------------------------------------------- lazy start

    public function test_a_read_only_request_never_starts_a_session(): void
    {
        $manager = $this->manager();

        $this->assertSame('fallback', $manager->get('missing', 'fallback'));
        $this->assertSame([], $manager->all());
        $this->assertFalse($manager->has('anything'));
        $this->assertFalse($manager->is_started());

        $manager->save();

        $this->assertSame([], $this->handler->writes);
        $this->assertSame([], $this->handler->reads);
        $this->assertSame([], $this->cookies->get_queued_cookies());
    }

    public function test_first_write_starts_the_session_and_queues_exactly_one_cookie(): void
    {
        $manager = $this->manager();

        $manager->put('cart', ['sku-1']);

        $this->assertTrue($manager->is_started());

        $manager->save();

        $this->assertCount(1, $this->handler->writes);
        $this->assertCount(1, $this->cookies->get_queued_cookies());
        $this->assertSame($manager->get_name(), $this->cookies->get_queued_cookies()[0]->get_name());
        $this->assertSame($manager->get_id(), $this->cookies->get_queued_cookies()[0]->get_value());
    }

    public function test_a_read_on_an_existing_session_loads_it_without_queueing_a_cookie(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku-1']]);

        $this->assertSame(['sku-1'], $manager->get('cart'));
        $this->assertTrue($manager->is_started());

        $manager->save();

        $this->assertSame([], $this->handler->writes);
        $this->assertSame([], $this->cookies->get_queued_cookies());
    }

    // ------------------------------------------------------------- read and write

    public function test_values_round_trip_including_dot_notation(): void
    {
        $manager = $this->manager();

        $manager->put('user.name', 'Ada');
        $manager->put(['theme' => 'dark', 'locale' => 'bn']);

        $this->assertSame('Ada', $manager->get('user.name'));
        $this->assertSame('dark', $manager->get('theme'));
        $this->assertSame('bn', $manager->get('locale'));
        $this->assertSame('default', $manager->get('user.missing', 'default'));
    }

    public function test_pull_returns_the_value_and_removes_the_key(): void
    {
        $manager = $this->manager();
        $manager->put('token', 'abc');

        $this->assertSame('abc', $manager->pull('token'));
        $this->assertFalse($manager->exists('token'));
    }

    public function test_has_is_false_for_a_null_value_but_exists_is_true(): void
    {
        $manager = $this->manager();
        $manager->put('nullable', null);

        $this->assertFalse($manager->has('nullable'));
        $this->assertTrue($manager->exists('nullable'));
        $this->assertTrue($manager->missing('never-set'));
    }

    public function test_forget_and_flush_remove_values(): void
    {
        $manager = $this->manager();
        $manager->put(['a' => 1, 'b' => 2, 'nested' => ['deep' => 3]]);

        $manager->forget('a');
        $this->assertFalse($manager->exists('a'));
        $this->assertTrue($manager->exists('b'));

        $manager->forget('nested.deep');
        $this->assertFalse($manager->exists('nested.deep'));

        $manager->flush();
        $this->assertFalse($manager->exists('b'));
    }

    public function test_push_increment_and_remember(): void
    {
        $manager = $this->manager();

        $manager->push('items', 'first');
        $manager->push('items', 'second');
        $this->assertSame(['first', 'second'], $manager->get('items'));

        $this->assertSame(1, $manager->increment('views'));
        $this->assertSame(3, $manager->increment('views', 2));
        $this->assertSame(2, $manager->decrement('views'));

        $this->assertSame('computed', $manager->remember('key', function () {
            return 'computed';
        }));
        $this->assertSame('computed', $manager->remember('key', function () {
            return 'recomputed';
        }));
    }

    public function test_only_reads_a_subset(): void
    {
        $manager = $this->manager();
        $manager->put(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a' => 1, 'c' => 3], $manager->only(['a', 'c']));
    }

    // ------------------------------------------------------------------- flash

    public function test_flashed_values_live_for_exactly_one_following_request(): void
    {
        $id = $this->valid_id('c');

        // Request A flashes a value.
        $a = $this->manager();
        $a->put('cart', ['sku']);
        $a->flash('status', 'saved');
        $a->save();
        $this->handler->seed($a->get_id(), $this->handler->last_written_payload());

        // Request B reads it back.
        $b = $this->manager([$a->get_name() => $a->get_id()]);
        $this->assertSame('saved', $b->get('status'));
        $b->save();
        $this->handler->seed($a->get_id(), $this->handler->last_written_payload());

        // Request C no longer sees it.
        $c = $this->manager([$a->get_name() => $a->get_id()]);
        $this->assertNull($c->get('status'));
        $this->assertSame(['sku'], $c->get('cart'));
    }

    public function test_now_does_not_survive_a_save(): void
    {
        $manager = $this->manager();
        $manager->put('cart', ['sku']);
        $manager->now('toast', 'hello');

        $this->assertSame('hello', $manager->get('toast'));

        $manager->save();

        $this->assertArrayNotHasKey('toast', $this->handler->last_written_payload());
    }

    public function test_reflash_extends_every_flashed_value(): void
    {
        $a = $this->manager();
        $a->put('cart', ['sku']);
        $a->flash('status', 'saved');
        $a->flash('other', 'kept');
        $a->save();
        $this->handler->seed($a->get_id(), $this->handler->last_written_payload());

        $b = $this->manager([$a->get_name() => $a->get_id()]);
        $b->reflash();
        $b->save();
        $this->handler->seed($a->get_id(), $this->handler->last_written_payload());

        $c = $this->manager([$a->get_name() => $a->get_id()]);
        $this->assertSame('saved', $c->get('status'));
        $this->assertSame('kept', $c->get('other'));
    }

    public function test_keep_extends_only_the_named_values(): void
    {
        $a = $this->manager();
        $a->put('cart', ['sku']);
        $a->flash('status', 'saved');
        $a->flash('other', 'dropped');
        $a->save();
        $this->handler->seed($a->get_id(), $this->handler->last_written_payload());

        $b = $this->manager([$a->get_name() => $a->get_id()]);
        $b->keep('status');
        $b->save();
        $this->handler->seed($a->get_id(), $this->handler->last_written_payload());

        $c = $this->manager([$a->get_name() => $a->get_id()]);
        $this->assertSame('saved', $c->get('status'));
        $this->assertNull($c->get('other'));
    }

    public function test_flashed_old_input_is_readable_on_the_next_request(): void
    {
        $a = $this->manager();
        $a->put('cart', ['sku']);
        $a->flash_input(['email' => 'ada@example.test']);
        $a->save();
        $this->handler->seed($a->get_id(), $this->handler->last_written_payload());

        $b = $this->manager([$a->get_name() => $a->get_id()]);

        $this->assertSame('ada@example.test', $b->get_old_input('email'));
        $this->assertSame('fallback', $b->get_old_input('missing', 'fallback'));
        $this->assertTrue($b->has_old_input());
    }

    // ---------------------------------------------------------------- identity

    public function test_regenerate_changes_the_id_and_keeps_the_data(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku']]);

        $before = $manager->get_id();
        $token_before = $manager->token();

        $manager->regenerate();

        $this->assertNotSame($before, $manager->get_id());
        $this->assertNotSame($token_before, $manager->token());
        $this->assertSame(['sku'], $manager->get('cart'));
        $this->assertSame([], $this->handler->destroys);
    }

    public function test_regenerate_with_destroy_removes_the_previous_payload(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku']]);
        $before = $manager->get_id();

        $manager->regenerate(true);

        $this->assertSame([$before], $this->handler->destroys);
        $this->assertSame(['sku'], $manager->get('cart'));
    }

    public function test_invalidate_changes_the_id_and_drops_the_data(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku']]);
        $before = $manager->get_id();
        $token_before = $manager->token();

        $manager->invalidate();

        $this->assertNotSame($before, $manager->get_id());
        $this->assertNotSame($token_before, $manager->token());
        $this->assertNull($manager->get('cart'));
        $this->assertSame([$before], $this->handler->destroys);
    }

    public function test_migrate_without_destroy_leaves_the_previous_payload(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku']]);
        $before = $manager->get_id();

        $manager->migrate(false);

        $this->assertNotSame($before, $manager->get_id());
        $this->assertSame([], $this->handler->destroys);
    }

    // ------------------------------------------------------- identifier handling

    public function test_a_malformed_identifier_is_discarded_without_a_storage_read(): void
    {
        $manager = $this->manager();
        $manager->request_cookies = [$manager->get_name() => '../../etc/passwd'];

        $this->assertSame('default', $manager->get('anything', 'default'));
        $this->assertFalse($manager->is_started());
        $this->assertSame([], $this->handler->reads);

        $manager->put('a', 1);

        $this->assertNotSame('../../etc/passwd', $manager->get_id());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $manager->get_id());
    }

    /**
     * @dataProvider malformed_identifiers
     */
    public function test_identifier_validation_rejects_anything_but_forty_hex_characters(string $candidate): void
    {
        $this->assertFalse($this->manager()->is_valid_id($candidate));
    }

    public static function malformed_identifiers(): array
    {
        return [
            'too short' => [str_repeat('a', 39)],
            'too long' => [str_repeat('a', 41)],
            'uppercase' => [str_repeat('A', 40)],
            'non hex' => [str_repeat('z', 40)],
            'sql metacharacters' => ["' OR 1=1 --" . str_repeat('a', 29)],
            'empty' => [''],
        ];
    }

    public function test_generated_identifiers_are_forty_hex_characters_and_unique(): void
    {
        $manager = $this->manager();

        $first = $manager->generate_id();
        $second = $manager->generate_id();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $first);
        $this->assertNotSame($first, $second);
    }

    // -------------------------------------------------------------- late writes

    public function test_a_late_write_persists_when_the_identifier_came_from_the_request(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku']]);
        $manager->headers_sent = true;

        $manager->put('cart', ['sku', 'sku-2']);
        $manager->save();

        $this->assertCount(1, $this->handler->writes);
        $this->assertSame([], $manager->warnings);
    }

    public function test_a_late_first_write_is_skipped_and_warned_about(): void
    {
        $manager = $this->manager();
        $manager->headers_sent = true;

        $manager->put('cart', ['sku']);
        $manager->save();

        $this->assertSame([], $this->handler->writes);
        $this->assertCount(1, $manager->warnings);
        $this->assertStringContainsString('headers were already sent', $manager->warnings[0]);
    }

    // -------------------------------------------------------------- save policy

    public function test_save_writes_nothing_when_the_session_never_started(): void
    {
        $this->manager()->save();

        $this->assertSame([], $this->handler->writes);
    }

    public function test_save_writes_nothing_when_the_session_is_unmodified(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku']]);
        $manager->get('cart');

        $manager->save();

        $this->assertSame([], $this->handler->writes);
    }

    public function test_save_passes_the_configured_lifetime(): void
    {
        $manager = $this->manager();
        $manager->put('cart', ['sku']);
        $manager->save();

        $this->assertSame($manager->get_lifetime_seconds(), $this->handler->writes[0]['lifetime']);
    }

    // ----------------------------------------------------------------- pruning

    public function test_a_session_holding_only_bookkeeping_is_pruned(): void
    {
        $id = $this->valid_id('d');
        $manager = $this->returning_visitor(
            ['_token' => str_repeat('e', 40), '_flash' => ['old' => ['status'], 'new' => []], 'status' => 'seen'],
            $id
        );

        // Reading the flashed value then saving ages it out, leaving only bookkeeping.
        $this->assertSame('seen', $manager->get('status'));

        $manager->save();

        $this->assertSame([], $this->handler->writes);
        $this->assertSame([$id], $this->handler->destroys);
        $queued = array_map(function ($cookie) {
            return $cookie->get_name();
        }, $this->cookies->get_queued_cookies());

        $this->assertContains($manager->get_name(), $queued);
        $this->assertTrue($this->cookies->queued($manager->get_name())->is_expired());
    }

    public function test_a_session_holding_application_data_is_persisted(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku']]);
        $manager->put('cart', ['sku', 'sku-2']);

        $manager->save();

        $this->assertCount(1, $this->handler->writes);
        $this->assertSame([], $this->handler->destroys);
    }

    public function test_the_parity_token_alone_does_not_sustain_a_session(): void
    {
        $manager = $this->manager();

        $manager->token();
        $manager->regenerate_token();
        $manager->save();

        $this->assertSame([], $this->handler->writes);
    }

    // ------------------------------------------------------------------ misc

    public function test_has_session_reports_a_cookie_holder_that_has_not_started(): void
    {
        $manager = $this->returning_visitor(['cart' => ['sku']]);

        $this->assertFalse($manager->is_started());
        $this->assertTrue($manager->has_session());

        $this->assertFalse($this->manager()->has_session());
    }

    public function test_previous_url_round_trips(): void
    {
        $manager = $this->manager();

        $this->assertNull($manager->previous_url());

        $manager->set_previous_url('https://example.test/checkout');

        $this->assertSame('https://example.test/checkout', $manager->previous_url());
    }

    public function test_the_queued_cookie_carries_the_expected_attributes(): void
    {
        $manager = $this->manager();
        $manager->put('cart', ['sku']);
        $manager->save();

        $cookie = $this->cookies->get_queued_cookies()[0];

        $this->assertTrue($cookie->is_http_only());
        $this->assertSame('Lax', $cookie->get_same_site());
        $this->assertFalse($cookie->is_secure());
    }
}
