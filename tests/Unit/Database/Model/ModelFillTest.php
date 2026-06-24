<?php

namespace Framework\Tests\Unit\Database\Model;

use Exception;
use Framework\Exceptions\MassAssignmentException;
use Framework\Tests\Support\Models\NoPrimaryKeyModel;
use Framework\Tests\Support\Models\StrictFillableModel;
use Framework\Tests\Support\Models\StubArticle;
use Framework\Tests\Support\Models\TotallyGuardedModel;
use Framework\Tests\Unit\TestCase;

class ModelFillTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->reset_model_static_state(StubArticle::class);
        $this->reset_model_static_state(StrictFillableModel::class);
        $this->reset_model_static_state(TotallyGuardedModel::class);

        parent::tearDown();
    }

    public function test_fill_assigns_fillable_attributes_when_not_strict(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $article = new StubArticle();
        $article->fill(['title' => 'Allowed title']);

        $this->assertSame('Allowed title', $article->get_attribute('title'));
    }

    public function test_fill_silently_discards_non_fillable_attributes_by_default(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $article = new StubArticle();
        $article->fill([
            'title' => 'Allowed title',
            'is_admin' => true,
        ]);

        $this->assertSame('Allowed title', $article->get_attribute('title'));
        $this->assertNull($article->get_attribute('is_admin'));
    }

    public function test_fill_throws_mass_assignment_exception_when_totally_guarded(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $model = new TotallyGuardedModel();

        $this->expectException(MassAssignmentException::class);
        $this->expectExceptionMessage('Add [title] to fillable array to allow mass assignment');

        $model->fill(['title' => 'Blocked']);
    }

    public function test_fill_throws_mass_assignment_exception_when_strict_mode_enabled(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        StrictFillableModel::prevent_silently_discarding_attributes(true);

        $model = new StrictFillableModel();

        $this->expectException(MassAssignmentException::class);
        $this->expectExceptionMessage('Add [is_admin] to fillable array to allow mass assignment');

        $model->fill([
            'title' => 'Allowed',
            'is_admin' => true,
        ]);
    }

    public function test_fill_invokes_discarded_attribute_callback_when_strict_mode_enabled(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $discarded_keys = [];

        StrictFillableModel::prevent_silently_discarding_attributes(true);
        StrictFillableModel::discarded_attribute_callback(function ($model, array $keys) use (&$discarded_keys) {
            $discarded_keys = $keys;
        });

        $model = new StrictFillableModel();
        $model->fill([
            'title' => 'Allowed',
            'is_admin' => true,
        ]);

        $this->assertSame(['is_admin'], array_values($discarded_keys));
        $this->assertSame('Allowed', $model->get_attribute('title'));
    }

    public function test_should_be_strict_enables_silent_discarding_prevention(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        StrictFillableModel::should_be_strict(true);

        $this->assertTrue(StrictFillableModel::is_attribute_silently_discarding_enabled());
    }
}

class ModelDeleteTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->reset_model_static_state(NoPrimaryKeyModel::class);
        $this->reset_model_static_state(StubArticle::class);

        parent::tearDown();
    }

    public function test_delete_throws_when_primary_key_is_not_defined(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $model = $this->make_existing_model(NoPrimaryKeyModel::class, ['title' => 'No key']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No primary key defined on model.');

        $model->delete();
    }

    public function test_delete_removes_existing_record_and_marks_model_as_not_existing(): void
    {
        $wpdb = $this->bootstrap_model_testing(['prefix' => 'wp_']);
        $wpdb->seed('test_articles', [
            ['id' => 4, 'title' => 'Delete me'],
        ]);

        $article = StubArticle::find(4);

        $this->assertTrue($article->delete());
        $this->assertCount(0, $wpdb->table_data['wp_test_articles']);
    }

    public function test_delete_returns_null_when_model_does_not_exist(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $article = new StubArticle();

        $this->assertNull($article->delete());
    }
}
