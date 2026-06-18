<?php
/**
 * Framework Carbon subclass adding isValidDate and to_sql_datetime_string helpers.
 * Uses a standard BASE_FORMAT for database datetime storage.
 * Wraps nesbot/carbon with conventions aligned to the query layer.
 *
 * @package    Framework
 * @subpackage Supports
 * @since      1.0.0
 */
namespace Framework\Supports;

defined('ABSPATH') || exit;

use Carbon\Carbon as BaseCarbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;

class Carbon extends BaseCarbon
{
    const BASE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Check if the value is a valid date.
     *
     * @param mixed $value
     *
     * @return bool
     * @since  1.0.0
     */
    public static function isValidDate($value)
    {
        if ($value instanceof DateTimeInterface) {
            return true;
        }

        try {
            static::parse($value);

            return true;
        } catch (InvalidFormatException $exception) {
            return false;
        }
    }

    /**
     * Convert the Carbon instance to a base format string.
     *
     * @return string
     * @since  1.0.0
     */
    public function toBaseDateString()
    {
        return $this->format(static::BASE_FORMAT);
    }

    /**
     * Convert the Carbon instance to a SQL safe date string.
     *
     * @return string
     * @since  1.0.0
     */
    public function toSqlDatetimeString()
    {
        return $this->toBaseDateString();
    }

    /**
     * Convert the Carbon instance to a SQL safe date string (snake_case alias).
     *
     * @return string
     * @since  1.0.0
     */
    public function to_sql_datetime_string()
    {
        return $this->toSqlDatetimeString();
    }
}
