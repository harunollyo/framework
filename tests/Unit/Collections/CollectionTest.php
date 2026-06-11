<?php

namespace Framework\Tests\Unit\Collections;

use InvalidArgumentException;
use Framework\Collections\Collection;
use Framework\Tests\Unit\TestCase;

class CollectionTest extends TestCase
{
    public function test_range_first_and_last_return_expected_items(): void
    {
        $collection = Collection::range(1, 3);

        $this->assertSame([1, 2, 3], $collection->all());
        $this->assertSame(1, $collection->first());
        $this->assertSame(3, $collection->last());
        $this->assertNull((new Collection())->first());
    }

    public function test_contains_supports_values_and_closures(): void
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertTrue($collection->contains(2));
        $this->assertTrue($collection->contains(fn($value) => $value > 2));
        $this->assertFalse($collection->contains(4));
    }

    public function test_map_filter_and_reject_do_not_mutate_original(): void
    {
        $original = new Collection([1, 2, 3, 4]);

        $mapped = $original->map(fn($value) => $value * 2);
        $filtered = $original->filter(fn($value) => $value % 2 === 0);
        $rejected = $original->reject(fn($value) => $value % 2 === 0);

        $this->assertSame([2, 4, 6, 8], $mapped->all());
        $this->assertSame([1 => 2, 3 => 4], $filtered->all());
        $this->assertSame([0 => 1, 2 => 3], $rejected->all());
        $this->assertSame([1, 2, 3, 4], $original->all());
    }

    public function test_each_stops_when_callback_returns_false(): void
    {
        $seen = [];

        (new Collection([1, 2, 3]))->each(function ($value) use (&$seen) {
            $seen[] = $value;

            return $value !== 2;
        });

        $this->assertSame([1, 2], $seen);
    }

    public function test_pluck_extracts_array_keys_and_object_properties(): void
    {
        $collection = new Collection([
            ['name' => 'Jane'],
            (object) ['name' => 'John'],
            ['name' => null],
        ]);

        $result = $collection->pluck('name');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame(['Jane', 'John', null], $result->all());
    }

    public function test_pluck_returns_collection_with_associative_array_when_keyed(): void
    {
        $collection = new Collection([
            ['id' => 1, 'name' => 'Jane'],
            ['id' => 2, 'name' => 'John'],
        ]);

        $result = $collection->pluck('name', 'id');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame([
            1 => 'Jane',
            2 => 'John',
        ], $result->all());
    }

    public function test_pluck_supports_closure_value_and_key(): void
    {
        $collection = new Collection([
            ['id' => 1, 'product' => 'Laptop'],
            ['id' => 2, 'product' => 'Phone'],
        ]);

        $result = $collection->pluck(
            fn($item) => strtoupper($item['product']),
            fn($item) => 'sku-' . $item['id']
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame([
            'sku-1' => 'LAPTOP',
            'sku-2' => 'PHONE',
        ], $result->all());
    }

    public function test_only_requires_at_least_one_key(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Collection(['name' => 'Jane']))->only([]);
    }

    public function test_percentage_returns_null_for_empty_collections(): void
    {
        $this->assertNull((new Collection())->percentage(fn($value) => $value > 0));
    }

    public function test_percentage_calculates_matching_ratio(): void
    {
        $collection = new Collection([1, 2, 3, 4]);

        $this->assertSame(50.0, $collection->percentage(fn($value) => $value % 2 === 0));
    }

    public function test_when_runs_callback_when_condition_is_truthy(): void
    {
        $collection = new Collection([1, 2, 3]);

        $result = $collection->when(true, fn($items) => $items->push(4));

        $this->assertSame([1, 2, 3, 4], $result->all());
    }

    public function test_unless_skips_callback_when_condition_is_truthy(): void
    {
        $collection = new Collection([1, 2, 3]);

        $result = $collection->unless(true, fn($items) => $items->push(5));

        $this->assertSame([1, 2, 3], $result->all());
    }

    public function test_empty_and_not_empty_report_collection_state(): void
    {
        $this->assertTrue((new Collection())->empty());
        $this->assertFalse((new Collection([1]))->empty());
        $this->assertTrue((new Collection([1]))->not_empty());
        $this->assertFalse((new Collection())->not_empty());
    }

    public function test_merge_recursive_combines_nested_values(): void
    {
        $original = new Collection(['name' => 'Widget', 'tags' => ['new']]);

        $merged = $original->merge_recursive(['tags' => ['sale'], 'price' => 10]);

        $this->assertSame([
            'name' => 'Widget',
            'tags' => ['new', 'sale'],
            'price' => 10,
        ], $merged->all());
        $this->assertSame(['name' => 'Widget', 'tags' => ['new']], $original->all());
    }

    public function test_accept_keeps_items_matching_the_predicate(): void
    {
        $collection = new Collection([1, 2, 3, 4]);

        $accepted = $collection->accept(fn($value) => $value % 2 === 0);

        $this->assertInstanceOf(Collection::class, $accepted);
        $this->assertSame([1 => 2, 3 => 4], $accepted->all());
        $this->assertSame([1, 2, 3, 4], $collection->all());
    }

    public function test_map_passes_value_and_key_to_callback(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2]);

        $mapped = $collection->map(fn($value, $key) => $key . $value);

        $this->assertSame(['a' => 'a1', 'b' => 'b2'], $mapped->all());
    }

    /**
     * @dataProvider group_by_provider
     */
    public function test_group_by_supports_all_grouping_strategies(
        array $items,
        $group_by,
        bool $preserve_keys,
        array $expected
    ): void {
        $result = (new Collection($items))->group_by($group_by, $preserve_keys);

        $this->assertInstanceOf(Collection::class, $result);

        foreach ($result->all() as $group) {
            $this->assertInstanceOf(Collection::class, $group);
        }

        $this->assertSame($expected, $this->collection_to_array($result));
    }

    public function group_by_provider(): array
    {
        return [
            'string attribute' => [
                [
                    ['type' => 'fruit', 'name' => 'apple'],
                    ['type' => 'vegetable', 'name' => 'carrot'],
                    ['type' => 'fruit', 'name' => 'banana'],
                ],
                'type',
                false,
                [
                    'fruit' => [
                        ['type' => 'fruit', 'name' => 'apple'],
                        ['type' => 'fruit', 'name' => 'banana'],
                    ],
                    'vegetable' => [
                        ['type' => 'vegetable', 'name' => 'carrot'],
                    ],
                ],
            ],
            'dot notation path' => [
                [
                    ['meta' => ['color' => 'red'], 'id' => 1],
                    ['meta' => ['color' => 'blue'], 'id' => 2],
                    ['meta' => ['color' => 'red'], 'id' => 3],
                ],
                'meta.color',
                false,
                [
                    'red' => [
                        ['meta' => ['color' => 'red'], 'id' => 1],
                        ['meta' => ['color' => 'red'], 'id' => 3],
                    ],
                    'blue' => [
                        ['meta' => ['color' => 'blue'], 'id' => 2],
                    ],
                ],
            ],
            'callback' => [
                [1, 2, 3, 4],
                fn($value) => $value % 2 === 0 ? 'even' : 'odd',
                false,
                [
                    'odd' => [1, 3],
                    'even' => [2, 4],
                ],
            ],
            'callback returning multiple keys' => [
                [
                    ['name' => 'post-1', 'tags' => ['php', 'wp']],
                    ['name' => 'post-2', 'tags' => ['wp']],
                ],
                fn($item) => $item['tags'],
                false,
                [
                    'php' => [
                        ['name' => 'post-1', 'tags' => ['php', 'wp']],
                    ],
                    'wp' => [
                        ['name' => 'post-1', 'tags' => ['php', 'wp']],
                        ['name' => 'post-2', 'tags' => ['wp']],
                    ],
                ],
            ],
            'boolean keys normalized to integers' => [
                [
                    ['active' => true, 'id' => 1],
                    ['active' => false, 'id' => 2],
                    ['active' => true, 'id' => 3],
                ],
                'active',
                false,
                [
                    1 => [
                        ['active' => true, 'id' => 1],
                        ['active' => true, 'id' => 3],
                    ],
                    0 => [
                        ['active' => false, 'id' => 2],
                    ],
                ],
            ],
            'null keys normalized to empty strings' => [
                [
                    ['category' => null, 'id' => 1],
                    ['category' => 'tools', 'id' => 2],
                ],
                'category',
                false,
                [
                    '' => [
                        ['category' => null, 'id' => 1],
                    ],
                    'tools' => [
                        ['category' => 'tools', 'id' => 2],
                    ],
                ],
            ],
            'preserved keys' => [
                [
                    'first' => ['type' => 'a'],
                    'second' => ['type' => 'b'],
                    'third' => ['type' => 'a'],
                ],
                'type',
                true,
                [
                    'a' => [
                        'first' => ['type' => 'a'],
                        'third' => ['type' => 'a'],
                    ],
                    'b' => [
                        'second' => ['type' => 'b'],
                    ],
                ],
            ],
            'reindexed keys' => [
                [
                    'first' => ['type' => 'a'],
                    'second' => ['type' => 'b'],
                    'third' => ['type' => 'a'],
                ],
                'type',
                false,
                [
                    'a' => [
                        ['type' => 'a'],
                        ['type' => 'a'],
                    ],
                    'b' => [
                        ['type' => 'b'],
                    ],
                ],
            ],
            'multi-level grouping' => [
                [
                    ['type' => 'A', 'status' => 'on', 'id' => 1],
                    ['type' => 'A', 'status' => 'off', 'id' => 2],
                    ['type' => 'B', 'status' => 'on', 'id' => 3],
                ],
                ['type', 'status'],
                false,
                [
                    'A' => [
                        'on' => [
                            ['type' => 'A', 'status' => 'on', 'id' => 1],
                        ],
                        'off' => [
                            ['type' => 'A', 'status' => 'off', 'id' => 2],
                        ],
                    ],
                    'B' => [
                        'on' => [
                            ['type' => 'B', 'status' => 'on', 'id' => 3],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider key_by_provider
     */
    public function test_key_by_reindexes_items_by_resolved_key(array $items, $key_by, array $expected): void
    {
        $result = (new Collection($items))->key_by($key_by);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($expected, $result->all());
    }

    public function key_by_provider(): array
    {
        $key_object = new class {
            public function __toString(): string
            {
                return 'object-key';
            }
        };

        return [
            'string attribute' => [
                [
                    ['id' => 'a1', 'name' => 'Jane'],
                    ['id' => 'b2', 'name' => 'John'],
                ],
                'id',
                [
                    'a1' => ['id' => 'a1', 'name' => 'Jane'],
                    'b2' => ['id' => 'b2', 'name' => 'John'],
                ],
            ],
            'dot notation path' => [
                [
                    ['user' => ['id' => 'u1'], 'role' => 'admin'],
                    ['user' => ['id' => 'u2'], 'role' => 'editor'],
                ],
                'user.id',
                [
                    'u1' => ['user' => ['id' => 'u1'], 'role' => 'admin'],
                    'u2' => ['user' => ['id' => 'u2'], 'role' => 'editor'],
                ],
            ],
            'callback' => [
                [
                    ['id' => 1, 'name' => 'Jane'],
                    ['id' => 2, 'name' => 'John'],
                ],
                fn($item) => 'user-' . $item['id'],
                [
                    'user-1' => ['id' => 1, 'name' => 'Jane'],
                    'user-2' => ['id' => 2, 'name' => 'John'],
                ],
            ],
            'duplicate keys keep the last item' => [
                [
                    ['id' => 'dup', 'name' => 'first'],
                    ['id' => 'dup', 'name' => 'second'],
                ],
                'id',
                [
                    'dup' => ['id' => 'dup', 'name' => 'second'],
                ],
            ],
            'object keys cast via __toString' => [
                [
                    ['key' => $key_object, 'value' => 'first'],
                ],
                'key',
                [
                    'object-key' => ['key' => $key_object, 'value' => 'first'],
                ],
            ],
        ];
    }

    /**
     * Recursively convert nested collections into plain arrays.
     *
     * @param mixed $value The value to convert
     *
     * @return mixed The converted value
     * @since 1.0.0
     */
    protected function collection_to_array($value)
    {
        if ($value instanceof Collection) {
            return array_map([$this, 'collection_to_array'], $value->all());
        }

        return $value;
    }
}
