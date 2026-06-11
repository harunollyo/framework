<?php

namespace Framework\Tests\Unit\Supports;

use ArrayAccess;
use InvalidArgumentException;
use Framework\Collections\Collection;
use Framework\Contracts\Support\Arrayable;
use Framework\Supports\Arr;
use Framework\Tests\Unit\TestCase;

class ArrTest extends TestCase
{
    public function test_from_returns_arrays_and_arrayable_values(): void
    {
        $arrayable = new class (['name' => 'Widget']) implements Arrayable {
            private array $items;

            public function __construct(array $items)
            {
                $this->items = $items;
            }

            public function to_array(): array
            {
                return $this->items;
            }
        };

        $this->assertSame(['name' => 'Widget'], Arr::from(['name' => 'Widget']));
        $this->assertSame(['name' => 'Widget'], Arr::from($arrayable));
        $this->assertSame(['a', 'b'], Arr::from(new Collection(['a', 'b'])));
    }

    public function test_from_throws_for_scalar_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Arr::from('scalar');
    }

    public function test_wrap_handles_null_scalar_and_array_values(): void
    {
        $this->assertSame([], Arr::wrap(null));
        $this->assertSame(['value'], Arr::wrap('value'));
        $this->assertSame(['a', 'b'], Arr::wrap(['a', 'b']));
    }

    public function test_flatten_flattens_nested_arrays_with_depth(): void
    {
        $nested = [
            ['a', ['b', 'c']],
            'd',
        ];

        $this->assertSame(['a', 'b', 'c', 'd'], Arr::flatten($nested));
        $this->assertSame(['a', ['b', 'c'], 'd'], Arr::flatten($nested, 1));
    }

    public function test_is_associative_detects_keyed_arrays(): void
    {
        $this->assertFalse(Arr::is_associative(['a', 'b']));
        $this->assertTrue(Arr::is_associative(['first' => 'a', 'second' => 'b']));
    }

    public function test_pluck_extracts_values_from_arrays_and_objects(): void
    {
        $rows = [
            ['name' => 'Jane'],
            (object) ['name' => 'John'],
            ['name' => null],
        ];

        $this->assertSame(['Jane', 'John', null], Arr::pluck($rows, 'name'));
    }

    public function test_pluck_builds_associative_array_when_key_is_provided(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Jane'],
            ['id' => 2, 'name' => 'John'],
        ];

        $this->assertSame([
            1 => 'Jane',
            2 => 'John',
        ], Arr::pluck($rows, 'name', 'id'));
    }

    public function test_pluck_supports_closure_value_and_key(): void
    {
        $rows = [
            ['id' => 1, 'product' => 'Laptop'],
            ['id' => 2, 'product' => 'Phone'],
        ];

        $this->assertSame([
            'sku-1' => 'LAPTOP',
            'sku-2' => 'PHONE',
        ], Arr::pluck(
            $rows,
            fn($item) => strtoupper($item['product']),
            fn($item) => 'sku-' . $item['id']
        ));
    }

    public function test_pluck_resolves_dot_notation_paths(): void
    {
        $rows = [
            ['user' => ['name' => 'Jane']],
            (object) ['user' => (object) ['name' => 'John']],
        ];

        $this->assertSame(['Jane', 'John'], Arr::pluck($rows, 'user.name'));
    }

    public function test_pluck_returns_null_for_missing_nested_keys(): void
    {
        $rows = [
            ['user' => ['name' => 'Jane']],
            ['user' => []],
        ];

        $this->assertSame(['Jane', null], Arr::pluck($rows, 'user.name'));
    }

    public function test_pluck_casts_object_keys_with_to_string(): void
    {
        $key_object = new class {
            public function __toString(): string
            {
                return 'object-key';
            }
        };

        $rows = [
            ['key' => $key_object, 'value' => 'first'],
            ['key' => $key_object, 'value' => 'second'],
        ];

        $this->assertSame([
            'object-key' => 'second',
        ], Arr::pluck($rows, 'value', 'key'));
    }

    public function test_exists_supports_arrays_and_array_access(): void
    {
        $arrayAccess = new class implements ArrayAccess {
            private array $items = ['key' => 'value'];

            public function offsetExists($offset): bool
            {
                return array_key_exists($offset, $this->items);
            }

            #[\ReturnTypeWillChange]
            public function offsetGet($offset)
            {
                return $this->items[$offset];
            }

            public function offsetSet($offset, $value): void
            {
                $this->items[$offset] = $value;
            }

            public function offsetUnset($offset): void
            {
                unset($this->items[$offset]);
            }
        };

        $this->assertTrue(Arr::exists(['name' => 'Jane'], 'name'));
        $this->assertTrue(Arr::exists($arrayAccess, 'key'));
        $this->assertFalse(Arr::exists(['name' => 'Jane'], 'missing'));
    }

    public function test_json_encode_uses_wp_json_encode(): void
    {
        $this->assertSame(
            '{"name":"Widget"}',
            Arr::json_encode(['name' => 'Widget'])
        );
    }

    public function test_map_preserves_keys_and_passes_value_and_key(): void
    {
        $result = Arr::map(['a' => 1, 'b' => 2], fn($value, $key) => $key . $value);

        $this->assertSame(['a' => 'a1', 'b' => 'b2'], $result);
    }

    public function test_map_falls_back_for_value_only_internal_callbacks(): void
    {
        $this->assertSame(
            ['a' => 'X', 'b' => 'Y'],
            Arr::map(['a' => 'x', 'b' => 'y'], 'strtoupper')
        );
    }

    public function test_reject_removes_items_where_callback_returns_true(): void
    {
        $result = Arr::reject([1, 2, 3, 4], fn($value) => $value % 2 === 0);

        $this->assertSame([0 => 1, 2 => 3], $result);
    }

    public function test_accept_keeps_items_where_callback_returns_true(): void
    {
        $result = Arr::accept([1, 2, 3, 4], fn($value) => $value % 2 === 0);

        $this->assertSame([1 => 2, 3 => 4], $result);
    }

    public function test_reject_and_accept_partition_items_for_same_predicate(): void
    {
        $items = ['a' => 1, 'b' => 2, 'c' => 3];
        $predicate = fn($value) => $value > 1;

        $this->assertSame(['b' => 2, 'c' => 3], Arr::accept($items, $predicate));
        $this->assertSame(['a' => 1], Arr::reject($items, $predicate));
    }
}
