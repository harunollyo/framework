<?php

namespace Framework\Tests\Unit\Database\Concerns;

use Framework\Tests\Support\Models\StrictFillableModel;
use Framework\Tests\Support\Models\TotallyGuardedModel;
use Framework\Tests\Unit\TestCase;

class GuardAttributesTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->reset_model_static_state(StrictFillableModel::class);
        $this->reset_model_static_state(TotallyGuardedModel::class);

        parent::tearDown();
    }

    public function test_totally_guarded_returns_true_when_fillable_empty_and_guarded_all(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $model = new TotallyGuardedModel();

        $this->assertTrue($model->totally_guarded());
    }

    public function test_totally_guarded_returns_false_when_fillable_is_defined(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $model = new StrictFillableModel();

        $this->assertFalse($model->totally_guarded());
    }

    public function test_fillable_from_array_filters_to_fillable_keys_when_fillable_defined(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $model = new StrictFillableModel();
        $filtered = $this->invoke_fillable_from_array($model, [
            'title' => 'Allowed',
            'is_admin' => true,
        ]);

        $this->assertSame(['title' => 'Allowed'], $filtered);
    }

    public function test_fillable_from_array_returns_all_attributes_when_fillable_empty(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $model = new TotallyGuardedModel();
        $attributes = [
            'title' => 'Blocked',
            'body' => 'Also blocked',
        ];

        $this->assertSame($attributes, $this->invoke_fillable_from_array($model, $attributes));
    }

    /**
     * Invoke the protected fillable_from_array helper.
     *
     * @param StrictFillableModel|TotallyGuardedModel $model The model instance.
     * @param array $attributes The attributes to filter.
     *
     * @return array
     */
    protected function invoke_fillable_from_array($model, array $attributes): array
    {
        $reflection = new \ReflectionClass($model);
        $method = $reflection->getMethod('fillable_from_array');
        $method->setAccessible(true);

        return $method->invoke($model, $attributes);
    }
}
