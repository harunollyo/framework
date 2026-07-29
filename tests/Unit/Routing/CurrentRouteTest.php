<?php

namespace Framework\Tests\Unit\Routing;

use Framework\Routing\CurrentRoute;
use Framework\Tests\Unit\TestCase;

class CurrentRouteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        CurrentRoute::reset();
    }

    public function test_set_and_is(): void
    {
        CurrentRoute::set('products.show', ['id' => 5]);

        $this->assertTrue(CurrentRoute::is('products.show'));
        $this->assertFalse(CurrentRoute::is('products.index'));
    }

    public function test_param_and_params(): void
    {
        CurrentRoute::set('products.show', ['id' => 5, 'slug' => 'widget']);

        $this->assertSame(5, CurrentRoute::param('id'));
        $this->assertSame('widget', CurrentRoute::param('slug'));
        $this->assertSame('fallback', CurrentRoute::param('missing', 'fallback'));
        $this->assertSame(['id' => 5, 'slug' => 'widget'], CurrentRoute::params());
    }

    public function test_params_returns_default_when_empty(): void
    {
        $this->assertSame(['none'], CurrentRoute::params(['none']));
        $this->assertSame([], CurrentRoute::params());
    }

    public function test_reset_clears_context(): void
    {
        CurrentRoute::set('products.show', ['id' => 5]);
        CurrentRoute::reset();

        $this->assertFalse(CurrentRoute::is('products.show'));
        $this->assertNull(CurrentRoute::param('id'));
        $this->assertSame([], CurrentRoute::params());
    }
}
