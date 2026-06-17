<?php
/**
 * Exception thrown when validation fails.
 *
 * @package    Framework
 * @subpackage Exceptions
 * @since      1.0.0
 */
namespace Framework\Exceptions;

defined('ABSPATH') || exit;

use Framework\Http\Response;
use Exception;

class ValidationException extends Exception
{
    /** @var array<string> */
    protected $errors;

    /**
     * @return static
     */
    public static function with_errors(array $errors, string $message = 'Validation failed!')
    {
        $code = Response::UNPROCESSABLE_ENTITY;
        $instance = new static($message, $code);
        $instance->errors = $errors;
        return $instance;
    }

    public function get_errors()
    {
        return $this->errors;
    }
}
