<?php
/**
 * Eloquent model mapping to the WordPress wp_options table.
 * Disables timestamps and casts option_value through unserialize for PHP-serialized storage.
 * Used by OptionManager for query-based option access beyond get_option.
 *
 * @package    Framework
 * @subpackage Wordpress\Models
 * @since      1.0.0
 */
namespace Framework\Wordpress\Models;

defined('ABSPATH') || exit;

use Framework\Database\Query\Model;

class Option extends Model
{
    protected $timestamps = false;

    protected $table = 'options';

    protected $primary_key = 'option_id';

    protected $casts = [
        'option_id' => 'integer',
        'option_name' => 'string',
        'option_value' => 'unserialize',
    ];

    protected $fillable = [
        'option_name',
        'option_value',
        'autoload',
    ];
}