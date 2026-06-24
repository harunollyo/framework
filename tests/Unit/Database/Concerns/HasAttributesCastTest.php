<?php

namespace Framework\Tests\Unit\Database\Concerns;

use Framework\Tests\Support\Models\CastMetadataIntegerModel;
use Framework\Tests\Support\Models\CastMetadataStringModel;
use Framework\Tests\Support\Models\UnserializeCastModel;
use Framework\Tests\Unit\TestCase;

class HasAttributesCastTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->reset_cast_type_cache(CastMetadataIntegerModel::class);
        $this->reset_model_static_state(CastMetadataIntegerModel::class);
        $this->reset_model_static_state(CastMetadataStringModel::class);
        $this->reset_model_static_state(UnserializeCastModel::class);

        parent::tearDown();
    }

    public function test_cast_type_cache_does_not_collide_across_models_sharing_attribute_names(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $integer_model = new CastMetadataIntegerModel();
        $integer_model->set_raw_attributes(['metadata' => '42'], true);

        $string_model = new CastMetadataStringModel();
        $string_model->set_raw_attributes(['metadata' => '42'], true);

        $this->assertSame(42, $integer_model->get_attribute('metadata'));
        $this->assertSame('42', $string_model->get_attribute('metadata'));
    }

    public function test_unserialize_cast_decodes_serialized_array_payload(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $payload = serialize(['key' => 'value']);
        $model = new UnserializeCastModel();
        $model->set_raw_attributes(['payload' => $payload], true);

        $this->assertSame(['key' => 'value'], $model->get_attribute('payload'));
    }

    public function test_unserialize_cast_returns_raw_value_when_not_serialized(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $model = new UnserializeCastModel();
        $model->set_raw_attributes(['payload' => 'plain-text'], true);

        $this->assertSame('plain-text', $model->get_attribute('payload'));
    }

    public function test_unserialize_cast_does_not_restore_objects_when_allowed_classes_disabled(): void
    {
        $this->bootstrap_model_testing(['prefix' => 'wp_']);

        $payload = serialize(new \stdClass());
        $model = new UnserializeCastModel();
        $model->set_raw_attributes(['payload' => $payload], true);

        $value = $model->get_attribute('payload');

        $this->assertNotInstanceOf(\stdClass::class, $value);
    }
}
