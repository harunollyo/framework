<?php

namespace Framework\Tests\Unit\Validation\Rules;

use Framework\Tests\Unit\TestCase;
use Framework\Validation\Validator;

class TypeRulesTest extends TestCase
{
    public function test_numeric_accepts_numbers_and_numeric_strings(): void
    {
        $this->assertTrue(Validator::make(['price' => 10], ['price' => 'numeric'])->passes());
        $this->assertTrue(Validator::make(['price' => 10.5], ['price' => 'numeric'])->passes());
        $this->assertTrue(Validator::make(['price' => '10.5'], ['price' => 'numeric'])->passes());
    }

    public function test_numeric_rejects_non_numeric_values(): void
    {
        $validator = Validator::make(['price' => 'abc'], ['price' => 'numeric']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors());
    }

    public function test_numeric_min_and_max_constraints(): void
    {
        $this->assertTrue(Validator::make(['age' => 20], ['age' => 'numeric|min:18|max:60'])->passes());
        $this->assertTrue(Validator::make(['age' => 15], ['age' => 'numeric|min:18'])->fails());
        $this->assertTrue(Validator::make(['age' => 65], ['age' => 'numeric|max:60'])->fails());
    }

    public function test_numeric_integer_constraint(): void
    {
        $this->assertTrue(Validator::make(['count' => 5], ['count' => 'numeric|integer'])->passes());
        $this->assertTrue(Validator::make(['count' => '5'], ['count' => 'numeric|integer'])->passes());
        $this->assertTrue(Validator::make(['count' => 5.5], ['count' => 'numeric|integer'])->fails());
    }

    public function test_numeric_int_constraint_is_an_alias_of_integer(): void
    {
        $this->assertTrue(Validator::make(['count' => 5], ['count' => 'numeric|int'])->passes());
        $this->assertTrue(Validator::make(['count' => 5.5], ['count' => 'numeric|int'])->fails());
    }

    public function test_array_accepts_arrays_only(): void
    {
        $this->assertTrue(Validator::make(['tags' => ['a', 'b']], ['tags' => 'array'])->passes());
        $this->assertTrue(Validator::make(['tags' => 'a,b'], ['tags' => 'array'])->fails());
    }

    public function test_array_count_constraints(): void
    {
        $this->assertTrue(Validator::make(['tags' => ['a', 'b']], ['tags' => 'array|min:2|max:3'])->passes());
        $this->assertTrue(Validator::make(['tags' => ['a']], ['tags' => 'array|min:2'])->fails());
        $this->assertTrue(Validator::make(['tags' => ['a', 'b']], ['tags' => 'array|size:3'])->fails());
        $this->assertTrue(Validator::make(['tags' => ['a', 'b', 'c']], ['tags' => 'array|exactly:3'])->passes());
    }

    public function test_array_contains_constraint(): void
    {
        $this->assertTrue(Validator::make(['colors' => ['red', 'blue']], ['colors' => 'array|contains:red'])->passes());
        $this->assertTrue(Validator::make(['colors' => ['green']], ['colors' => 'array|contains:red'])->fails());
    }

    public function test_boolean_accepts_common_boolean_representations(): void
    {
        foreach ([true, false, 0, 1, '0', '1', 'true', 'false'] as $value) {
            $validator = Validator::make(['flag' => $value], ['flag' => 'boolean']);

            $this->assertTrue($validator->passes(), 'Failed for value: ' . var_export($value, true));
        }
    }

    public function test_boolean_rejects_other_values(): void
    {
        $this->assertTrue(Validator::make(['flag' => 'yes'], ['flag' => 'boolean'])->fails());
        $this->assertTrue(Validator::make(['flag' => 2], ['flag' => 'boolean'])->fails());
    }

    public function test_strict_boolean_accepts_real_booleans_only(): void
    {
        $this->assertTrue(Validator::make(['flag' => true], ['flag' => 'boolean:strict'])->passes());
        $this->assertTrue(Validator::make(['flag' => false], ['flag' => 'boolean:strict'])->passes());
        $this->assertTrue(Validator::make(['flag' => '1'], ['flag' => 'boolean:strict'])->fails());
        $this->assertTrue(Validator::make(['flag' => 1], ['flag' => 'boolean:strict'])->fails());
    }

    public function test_object_accepts_objects_and_associative_arrays(): void
    {
        $this->assertTrue(Validator::make(['meta' => new \stdClass()], ['meta' => 'object'])->passes());
        $this->assertTrue(Validator::make(['meta' => ['key' => 'value']], ['meta' => 'object'])->passes());
    }

    public function test_object_rejects_lists_and_scalars(): void
    {
        $this->assertTrue(Validator::make(['meta' => ['a', 'b']], ['meta' => 'object'])->fails());
        $this->assertTrue(Validator::make(['meta' => 'string'], ['meta' => 'object'])->fails());
    }
}
