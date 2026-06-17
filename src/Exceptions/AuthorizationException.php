<?php
/**
 * Exception thrown when a authorization fails.
 *
 * @package    Framework
 * @subpackage Exceptions
 * @since      1.0.0
 */
namespace Framework\Exceptions;

defined('ABSPATH') || exit;

use Framework\Http\Response;
use RuntimeException;

class AuthorizationException extends RuntimeException
{
    public function __construct($message = '', $code = 0, $previous = null)
    {
        if ($message === '') {
            $message = 'You have to be logged in';
        }

        if ($code === 0) {
            $code = Response::UNAUTHORIZED;
        }

        parent::__construct($message, $code, $previous);
    }
}
