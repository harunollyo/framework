<?php

declare(strict_types=1);

namespace {
    if (!defined('ABSPATH')) {
        define('ABSPATH', '/var/www/html/');
    }

    if (!defined('WP_CLI')) {
        define('WP_CLI', false);
    }
}

namespace {
    global $wpdb;

    if (!isset($wpdb)) {
        $wpdb = new wpdb('', '', '', '');
    }
}
