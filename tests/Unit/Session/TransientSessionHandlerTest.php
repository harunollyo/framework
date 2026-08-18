<?php

namespace Framework\Tests\Unit\Session;

use Framework\Session\Handlers\ArraySessionHandler;
use Framework\Session\Handlers\TransientSessionHandler;
use Framework\Tests\Unit\TestCase;

use function Framework\with_prefix;

class TransientSessionHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrap_application();

        $GLOBALS['framework_test_transients'] = [];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['framework_test_transients'], $GLOBALS['framework_test_ext_object_cache']);

        parent::tearDown();
    }

    protected function id(): string
    {
        return str_repeat('a', 40);
    }

    public function test_a_payload_round_trips(): void
    {
        $handler = new TransientSessionHandler();

        $handler->write($this->id(), ['cart' => ['sku'], 'count' => 2], 7200);

        $this->assertSame(['cart' => ['sku'], 'count' => 2], $handler->read($this->id()));
    }

    public function test_the_lifetime_is_passed_as_the_transient_expiry(): void
    {
        $handler = new TransientSessionHandler();

        $handler->write($this->id(), ['a' => 1], 7200);

        $key = with_prefix('session_' . $this->id());

        $this->assertSame(7200, $GLOBALS['framework_test_transients'][$key]['lifetime']);
    }

    public function test_a_missing_transient_reads_as_an_empty_array(): void
    {
        $this->assertSame([], (new TransientSessionHandler())->read($this->id()));
    }

    public function test_a_corrupted_transient_reads_as_an_empty_array(): void
    {
        $key = with_prefix('session_' . $this->id());
        $GLOBALS['framework_test_transients'][$key] = ['value' => 'not-an-array', 'expires_at' => 0];

        $this->assertSame([], (new TransientSessionHandler())->read($this->id()));
    }

    public function test_destroy_removes_the_payload(): void
    {
        $handler = new TransientSessionHandler();
        $handler->write($this->id(), ['a' => 1], 7200);

        $handler->destroy($this->id());

        $this->assertSame([], $handler->read($this->id()));
    }

    public function test_the_storage_key_is_namespaced_with_the_application_prefix(): void
    {
        $handler = new TransientSessionHandler();
        $handler->write($this->id(), ['a' => 1], 7200);

        $keys = array_keys($GLOBALS['framework_test_transients']);

        $this->assertCount(1, $keys);
        $this->assertStringContainsString('session_' . $this->id(), $keys[0]);
        $this->assertSame(with_prefix('session_' . $this->id()), $keys[0]);
    }

    public function test_the_storage_key_stays_within_the_wordpress_transient_name_limit(): void
    {
        $handler = new TransientSessionHandler();
        $handler->write($this->id(), ['a' => 1], 7200);

        $key = array_keys($GLOBALS['framework_test_transients'])[0];

        // WordPress option names are 191 chars; the "_transient_timeout_" prefix
        // leaves 172 for the transient name itself.
        $this->assertLessThanOrEqual(172, strlen($key));
    }

    public function test_the_external_object_cache_state_is_reported(): void
    {
        $handler = new TransientSessionHandler();

        $this->assertFalse($handler->is_external_object_cache());

        $GLOBALS['framework_test_ext_object_cache'] = true;

        $this->assertTrue($handler->is_external_object_cache());
    }

    public function test_the_array_driver_keeps_values_only_within_one_instance(): void
    {
        $handler = new ArraySessionHandler();
        $handler->write($this->id(), ['cart' => ['sku']], 7200);

        $this->assertSame(['cart' => ['sku']], $handler->read($this->id()));

        // A new instance stands in for a new request: nothing was persisted.
        $this->assertSame([], (new ArraySessionHandler())->read($this->id()));
        $this->assertSame([], $GLOBALS['framework_test_transients']);
    }
}
