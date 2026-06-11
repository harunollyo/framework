<?php

namespace Framework\Tests\Unit\Polyfill;

use ArrayIterator;
use Framework\Tests\Unit\TestCase;

use function Framework\Polyfill\array_first;
use function Framework\Polyfill\array_key_first;
use function Framework\Polyfill\array_key_last;
use function Framework\Polyfill\array_last;
use function Framework\Polyfill\is_iterable;
use function Framework\Polyfill\str_contains;
use function Framework\Polyfill\str_ends_with;
use function Framework\Polyfill\str_starts_with;

class PolyfillTest extends TestCase
{
    public function test_array_first_returns_first_value_or_null(): void
    {
        $this->assertSame(1, array_first([1, 2, 3]));
        $this->assertSame('a', array_first(['first' => 'a', 'second' => 'b']));
        $this->assertNull(array_first([]));
    }

    public function test_array_last_returns_last_value_or_null(): void
    {
        $this->assertSame(3, array_last([1, 2, 3]));
        $this->assertSame('b', array_last(['first' => 'a', 'second' => 'b']));
        $this->assertNull(array_last([]));
    }

    public function test_array_last_does_not_mutate_internal_pointer(): void
    {
        $array = [1, 2, 3];

        array_last($array);

        $this->assertSame(1, current($array));
    }

    public function test_is_iterable_detects_arrays_and_traversables(): void
    {
        $this->assertTrue(is_iterable([1, 2, 3]));
        $this->assertTrue(is_iterable(new ArrayIterator([1, 2, 3])));
        $this->assertFalse(is_iterable('string'));
        $this->assertFalse(is_iterable(42));
        $this->assertFalse(is_iterable(null));
        $this->assertFalse(is_iterable(new \stdClass()));
    }

    public function test_array_key_first_returns_first_key_or_null(): void
    {
        $this->assertSame(0, array_key_first([1, 2, 3]));
        $this->assertSame('first', array_key_first(['first' => 'a', 'second' => 'b']));
        $this->assertNull(array_key_first([]));
    }

    public function test_array_key_last_returns_last_key_or_null(): void
    {
        $this->assertSame(2, array_key_last([1, 2, 3]));
        $this->assertSame('second', array_key_last(['first' => 'a', 'second' => 'b']));
        $this->assertNull(array_key_last([]));
    }

    public function test_str_contains_matches_substrings(): void
    {
        $this->assertTrue(str_contains('framework', 'work'));
        $this->assertTrue(str_contains('framework', 'frame'));
        $this->assertTrue(str_contains('framework', ''));
        $this->assertFalse(str_contains('framework', 'laravel'));
        $this->assertFalse(str_contains('', 'needle'));
    }

    public function test_str_starts_with_matches_prefixes(): void
    {
        $this->assertTrue(str_starts_with('framework', 'frame'));
        $this->assertTrue(str_starts_with('framework', ''));
        $this->assertFalse(str_starts_with('framework', 'work'));
        $this->assertFalse(str_starts_with('frame', 'framework'));
    }

    public function test_str_ends_with_matches_suffixes(): void
    {
        $this->assertTrue(str_ends_with('framework', 'work'));
        $this->assertTrue(str_ends_with('framework', ''));
        $this->assertTrue(str_ends_with('framework', 'framework'));
        $this->assertFalse(str_ends_with('framework', 'frame'));
        $this->assertFalse(str_ends_with('work', 'framework'));
    }
}
