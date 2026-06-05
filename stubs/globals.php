<?php

declare(strict_types=1);

namespace {
    if (!defined('ABSPATH')) {
        define('ABSPATH', '/var/www/html/');
    }

    if (!defined('WP_CLI')) {
        define('WP_CLI', false);
    }

    if (!defined('FS_CHMOD_FILE')) {
        define('FS_CHMOD_FILE', 0644);
    }
}

namespace {
    global $wpdb;

    if (!isset($wpdb)) {
        $wpdb = new wpdb('', '', '', '');
    }
}
