<?php

namespace Framework\Tests\Unit\Database\Model;

use Framework\Database\Query\Collection;
use Framework\Tests\Support\Models\StubArticle;
use Framework\Tests\Support\Models\StubSlugPost;
use Framework\Tests\Unit\TestCase;

class ModelFindTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->reset_model_static_state(StubArticle::class);
        $this->reset_model_static_state(StubSlugPost::class);

        parent::tearDown();
    }

    public function test_find_returns_hydrated_model_for_integer_primary_key(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_articles', [
            ['id' => 1, 'title' => 'First article'],
            ['id' => 2, 'title' => 'Second article'],
        ]);

        $article = StubArticle::find(1);

        $this->assertInstanceOf(StubArticle::class, $article);
        $this->assertSame(1, $article->get_attribute('id'));
        $this->assertSame('First article', $article->get_attribute('title'));
    }

    public function test_find_returns_null_when_record_does_not_exist(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_articles', [
            ['id' => 1, 'title' => 'First article'],
        ]);

        $this->assertNull(StubArticle::find(99));
    }

    public function test_find_accepts_string_primary_key_values(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_slug_posts', [
            ['slug' => 'hello-world', 'title' => 'Hello'],
            ['slug' => 'second-post', 'title' => 'Second'],
        ]);

        $post = StubSlugPost::find('hello-world');

        $this->assertInstanceOf(StubSlugPost::class, $post);
        $this->assertSame('hello-world', $post->get_attribute('slug'));
        $this->assertSame('Hello', $post->get_attribute('title'));
    }
}
