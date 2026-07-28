<?php

namespace Framework\Tests\Unit\Routing;

use Framework\Routing\RouteParser;
use Framework\Tests\Unit\TestCase;

class RouteParserTest extends TestCase
{
    public function test_parse_segments_supports_literals_and_params(): void
    {
        $parser = new RouteParser();
        $segments = $parser->parse_segments('shop/products/{id:int}/edit');

        $this->assertCount(4, $segments);
        $this->assertSame('literal', $segments[0]['type']);
        $this->assertSame('shop', $segments[0]['value']);
        $this->assertSame('param', $segments[2]['type']);
        $this->assertSame('id', $segments[2]['name']);
        $this->assertSame('int', $segments[2]['inline_type']);
    }

    public function test_extract_param_types_from_inline_syntax(): void
    {
        $parser = new RouteParser();
        $segments = $parser->parse_segments('products/{id:int}/{slug:slug}');
        $types = $parser->extract_param_types($segments);

        $this->assertSame(['id' => 'int', 'slug' => 'slug'], $types);
    }

    public function test_format_rest_endpoint_uses_inline_types_and_where_overrides(): void
    {
        $parser = new RouteParser();

        $formatted = $parser->format_rest_endpoint('products/{id:int}', []);
        $this->assertSame('products/(?P<id>\d+)', $formatted);

        $overridden = $parser->format_rest_endpoint('products/{id}', ['id' => '[0-9]+']);
        $this->assertSame('products/(?P<id>[0-9]+)', $overridden);
    }

    public function test_build_site_pattern_returns_regex_and_param_names(): void
    {
        $parser = new RouteParser();
        $segments = $parser->parse_segments('shop/{id:int}');
        $types = $parser->extract_param_types($segments);

        [$pattern, $names] = $parser->build_site_pattern($segments, $types);

        $this->assertSame('^shop/(\d+)/?$', $pattern);
        $this->assertSame(['id'], $names);
    }

    public function test_resolve_regex_falls_back_to_raw_pattern(): void
    {
        $parser = new RouteParser();

        $this->assertSame('\d+', $parser->resolve_regex('int'));
        $this->assertSame('[A-Z]{3}', $parser->resolve_regex('[A-Z]{3}'));
    }
}
