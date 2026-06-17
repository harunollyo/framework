<?php
/**
 * Value object describing WP-CLI command arguments, options, and positional parameters.
 * Parses synopsis arrays into normalized CLI signatures for CommandBase implementations.
 * Keeps command registration consistent across the console layer.
 *
 * @package    Framework
 * @subpackage Console
 * @since      1.0.0
 */
namespace Framework\Console;

defined('ABSPATH') || exit;

use Framework\Supports\Flex;

/**
 * @method $this name(string $name)
 * @method $this description(string $description)
 * @method $this optional()
 * @method $this repeating()
 * @method $this options(array $options)
 * @method $this type('positional'|'assoc'|'flag' $type)
 * @method $this default($value)
 */

class Synopsis extends Flex
{
    /**
     * Handle dynamic method calls
     *
     * @param string $method
     * @param array $parameters
     *
     * @return static
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static())->$method(...$parameters);
    }
}
