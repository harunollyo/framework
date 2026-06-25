<?php

namespace Framework\Tests\Unit\Database\Query\Relations;

use Framework\Database\Query\Relations\HasOne;
use Framework\Database\Query\Relations\Relation;
use Framework\Tests\Support\Models\StubArticle;
use Framework\Tests\Support\Models\StubComment;
use Framework\Tests\Unit\TestCase;

class RelationHashTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->reset_relation_join_count();
        $this->reset_model_static_state(StubArticle::class);
        $this->reset_model_static_state(StubComment::class);

        parent::tearDown();
    }

    public function test_get_relation_count_hash_defaults_to_incrementing_join_count(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $relation = $this->make_has_one_relation();

        $first_hash = $relation->get_relation_count_hash();
        $second_hash = $relation->get_relation_count_hash();

        $this->assertSame('framework_reserved_0', $first_hash);
        $this->assertSame('framework_reserved_1', $second_hash);
    }

    public function test_get_relation_count_hash_can_read_without_incrementing(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $this->reset_relation_join_count();

        $relation = $this->make_has_one_relation();

        $this->assertSame('framework_reserved_0', $relation->get_relation_count_hash(false));
        $this->assertSame('framework_reserved_0', $relation->get_relation_count_hash(false));
    }

    public function test_self_relation_existence_query_uses_framework_reserved_alias(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $this->reset_relation_join_count();

        $parent = new StubArticle();
        $related = new StubComment();
        $relation = new HasOne($related, $parent, 'article_id', 'id');

        $query = $relation->get_relation_existence_query_for_self_relation(
            $related->new_query(),
            $parent->new_query()
        );

        $sql = $query->to_sql();

        $this->assertStringContainsString('framework_reserved_0', $sql);
    }

    /**
     * Build a HasOne relation for hash testing.
     *
     * @return HasOne
     */
    protected function make_has_one_relation(): HasOne
    {
        $parent = new StubArticle();
        $related = new StubComment();

        return new HasOne($related, $parent, 'article_id', 'id');
    }
}
