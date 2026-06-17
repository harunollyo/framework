<?php
/**
 * Runtime exception thrown when a query expecting a single row returns more than one.
 * Carries the matched record count for error reporting.
 * Used by firstOrFail-style retrieval methods on the query builder.
 *
 * @package    Framework
 * @subpackage Exceptions
 * @since      1.0.0
 */
namespace Framework\Exceptions;

defined('ABSPATH') || exit;

use RuntimeException;

class MultipleRecordsFoundException extends RuntimeException
{
    protected $count;

    public function __construct($count, $code = 0, $previous = null)
    {
        $this->count = $count;

        parent::__construct(sprintf('%d records were found', $count, $code, $previous));
    }

    public function get_count()
    {
        return $this->count;
    }
}
