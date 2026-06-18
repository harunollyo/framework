<?php
/**
 * Rule to ensure a a post exist with id and post type.
 *
 * @package    Framework
 * @subpackage Validation\Rules
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

defined('ABSPATH') || exit;

use Framework\Supports\Facades\DB;
use Exception;

class ExistsRule extends BaseRule
{
    /**
     * Check if the value exists in the specified database table and column.
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @since 1.0.0
     */
    public function validate_rule()
    {
        if (stripos($this->rule_value, ',') === false) {
            throw new Exception("Missing parameters for exists rule.");
        }

        [$table_name, $column_name] = explode(",", $this->rule_value, 2);

        $result = DB::table($table_name)->where($column_name, $this->value)->first();

        return !empty($result);
    }

    /**
     * Get the error message if the row does not exist in DB table.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function get_error_message()
    {
        return 'Resource does not exist.';
    }
}
