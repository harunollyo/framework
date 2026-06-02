<?php
/**
 * Framework Dev
 *
 * @package     framework-example
 * Plugin Name: Framework Dev
 * Plugin URI: https://github.com/themeum/framework
 * Description: A minimal sample plugin that exercises themeum/framework inside a real WordPress runtime for library development.
 * Version: 0.1.0-dev
 * Author: Themeum
 * Author URI: https://www.themeum.com
 * Text Domain: framework
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('FRAMEWORK_EXAMPLE_PATH')) {
    define('FRAMEWORK_EXAMPLE_PATH', plugin_dir_path(__FILE__));
}

if (!defined('FRAMEWORK_EXAMPLE_URL')) {
    define('FRAMEWORK_EXAMPLE_URL', plugin_dir_url(__FILE__));
}

if (!defined('FRAMEWORK_EXAMPLE_PREFIX')) {
    define('FRAMEWORK_EXAMPLE_PREFIX', 'framework');
}

require_once __DIR__ . '/vendor/autoload.php';

add_action('init', 'framework_example_boot_application', 0);

function framework_example_boot_application()
{
    require_once FRAMEWORK_EXAMPLE_PATH . 'bootstrap/app.php';
}
