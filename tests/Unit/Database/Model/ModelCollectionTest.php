<?php

namespace Framework\Tests\Unit\Database\Model;

use Framework\Database\Query\Collection;
use Framework\Tests\Support\Models\StubArticle;
use Framework\Tests\Support\Models\StubSlugPost;
use Framework\Tests\Unit\TestCase;

class ModelCollectionTest extends TestCase
{
    public function test_model_keys_returns_primary_key_values(): void
    {
        $collection = new Collection([
            $this->make_existing_model(StubArticle::class, ['id' => 1, 'title' => 'One']),
            $this->make_existing_model(StubArticle::class, ['id' => 2, 'title' => 'Two']),
        ]);

        $this->assertSame([1, 2], $collection->model_keys());
    }

    public function test_model_keys_preserves_string_primary_key_values(): void
    {
        $collection = new Collection([
            $this->make_existing_model(StubSlugPost::class, ['slug' => 'alpha', 'title' => 'Alpha']),
            $this->make_existing_model(StubSlugPost::class, ['slug' => 'beta', 'title' => 'Beta']),
        ]);

        $this->assertSame(['alpha', 'beta'], $collection->model_keys());
    }
}
