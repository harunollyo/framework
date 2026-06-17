<?php
/**
 * Proxy that invokes methods on a target object and always returns the target for chaining.
 * Powers the tap helper pattern where side-effect calls should not break fluent pipelines.
 * Delegates __call to the wrapped instance.
 *
 * @package    Framework
 * @subpackage Supports
 * @since      1.0.0
 */
namespace Framework\Supports;

defined('ABSPATH') || exit;

class HigherOrderTapProxy
{
    /**
     * The target instance.
     *
     * @var object
     */
    protected $target;

    /**
     * Create a new proxy instance.
     *
     * @param  object  $target
     * @return void
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Dynamically handle calls to the object.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->target->{$method}(...$parameters);

        return $this->target;
    }
}
