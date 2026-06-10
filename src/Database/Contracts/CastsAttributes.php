<?php

namespace Framework\Database\Contracts;

use Framework\Database\Query\Model;

/**
 * @template TGet
 * @template TSet
 */
interface CastsAttributes
{
    /**
     * Get the attribute value.
     *
     * @param Model $model The model instance.
     * @param string $key The attribute key.
     * @param TGet|null $value The attribute value.
     * @param array $attributes The attributes array.
     * 
     * @return mixed The attribute value.
     * 
     * @since 1.0.0
     */
    public function get(Model $model, string $key, $value, array $attributes);

    /**
     * Set the attribute value.
     *
     * @param Model $model The model instance.
     * @param string $key The attribute key.
     * @param TSet|null $value The attribute value.
     * @param array<string, mixed> $attributes The attributes array.
     * 
     * @return mixed The attribute value.
     * 
     * @since 1.0.0
     */
    public function set(Model $model, string $key, $value, array $attributes);
}