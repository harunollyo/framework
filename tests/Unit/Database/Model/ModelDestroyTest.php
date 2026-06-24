<?php

namespace Framework\Tests\Unit\Database\Model;

use Framework\Database\Query\Collection;
use Framework\Tests\Support\Models\StubArticle;
use Framework\Tests\Unit\TestCase;
use function Framework\collection;

class ModelDestroyTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->reset_model_static_state(StubArticle::class);

        parent::tearDown();
    }

    public function test_destroy_returns_zero_for_empty_input(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $this->assertSame(0, StubArticle::destroy([]));
    }

    public function test_destroy_deletes_existing_records_and_returns_count(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_articles', [
            ['id' => 1, 'title' => 'First'],
            ['id' => 2, 'title' => 'Second'],
            ['id' => 3, 'title' => 'Third'],
        ]);

        $deleted = StubArticle::destroy([1, 2, 99]);

        $this->assertSame(2, $deleted);
        $this->assertCount(1, $wpdb->table_data['wp_test_articles']);
        $this->assertSame(3, $wpdb->table_data['wp_test_articles'][0]['id']);
    }

    public function test_destroy_accepts_variadic_ids(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_articles', [
            ['id' => 10, 'title' => 'Ten'],
            ['id' => 11, 'title' => 'Eleven'],
        ]);

        $deleted = StubArticle::destroy(10, 11);

        $this->assertSame(2, $deleted);
        $this->assertCount(0, $wpdb->table_data['wp_test_articles']);
    }

    public function test_destroy_accepts_query_collection_of_models(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_articles', [
            ['id' => 5, 'title' => 'Five'],
            ['id' => 6, 'title' => 'Six'],
        ]);

        $models = new Collection([
            $this->make_existing_model(StubArticle::class, ['id' => 5, 'title' => 'Five']),
            $this->make_existing_model(StubArticle::class, ['id' => 6, 'title' => 'Six']),
        ]);

        $deleted = StubArticle::destroy($models);

        $this->assertSame(2, $deleted);
        $this->assertCount(0, $wpdb->table_data['wp_test_articles']);
    }

    public function test_destroy_uses_single_select_for_multiple_ids(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_articles', [
            ['id' => 1, 'title' => 'First'],
            ['id' => 2, 'title' => 'Second'],
        ]);

        StubArticle::destroy([1, 2]);

        $select_queries = array_values(array_filter($wpdb->queries, function (string $query) {
            return stripos($query, 'SELECT') !== false;
        }));

        $this->assertCount(1, $select_queries);
        $this->assertStringContainsString('in (1, 2)', strtolower($select_queries[0]));
    }

    public function test_destroy_accepts_framework_collection_of_ids(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_articles', [
            ['id' => 7, 'title' => 'Seven'],
        ]);

        $deleted = StubArticle::destroy(collection([7]));

        $this->assertSame(1, $deleted);
        $this->assertCount(0, $wpdb->table_data['wp_test_articles']);
    }
}
