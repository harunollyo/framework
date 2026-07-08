<?php
/**
 * Exists rule class.
 *
 * @package    Framework
 * @subpackage Validation
 * @since      1.0.0
 */
namespace Framework\Validation\Rules;

use Framework\Supports\Arr;
use Framework\Supports\Facades\DB;
use Framework\Validation\ValidationRule;

use function Framework\Polyfill\array_last;

defined('ABSPATH') || exit;

/**
 * Validates that the given value exists in a database table column.
 */
class ExistsRule extends ValidationRule
{
    /**
     * The rule name.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected string $rule = 'exists';

    /**
     * The default messages.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected array $default_messages = [
        'default' => 'The selected {name} does not exist.',
    ];

    /**
     * Validate the rule.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validate(): bool
    {
        if (!$this->record_exists()) {
            return $this->fails($this->default_messages['default']);
        }

        return true;
    }

    /**
     * Check whether a matching record exists in the table.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function record_exists()
    {
        [$table, $column] = $this->table_and_column();

        $results = DB::select(
            "SELECT 1 FROM `{$table}` WHERE `{$column}` = %s LIMIT 1",
            [$this->value]
        );

        return !empty($results);
    }

    /**
     * Resolve the prefixed table name and the column from the rule arguments.
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function table_and_column()
    {
        $arguments = Arr::wrap($this->args);
        $table = DB::get_table_prefix() . $arguments[0];
        $column = $arguments[1] ?? array_last(explode('.', $this->name));

        return [$table, $column];
    }

    /**
     * Get the error messages.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function messages()
    {
        return $this->process_messages($this->messages);
    }
}
