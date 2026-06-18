<?php
/**
 * Exception thrown when an invalid cast type is used.
 *
 * @package    Framework
 * @subpackage Exceptions
 * @since      1.0.0
 */
namespace Framework\Exceptions;

defined('ABSPATH') || exit;

use Exception;
use RuntimeException;

class InvalidCastException extends RuntimeException
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $model;
    
    /**
     * The attribute key.
     *
     * @var string
     */
    protected $key;
    

    /**
     * The cast type.
     *
     * @var string
     */
    protected $cast_type;

    /**
     * Create a new InvalidCastException instance.
     *
     * @param object $model The model instance.
     * @param string $key The attribute key.
     * @param string $cast_type The cast type.
     */
    public function __construct($model, $key, $cast_type)
    {
        $class_name = get_class($model);

        parent::__construct(sprintf('Invalid cast type for attribute %s on model %s: %s', $key, $class_name, $cast_type));

        $this->model = $class_name;
        $this->key = $key;
        $this->cast_type = $cast_type;
    }
}
