<?php

declare(strict_types=1);

$vendor = dirname(__DIR__) . '/vendor';

$stub_files = [
    __DIR__ . '/wordpress-stubs.php',
    __DIR__ . '/wp-cli-stubs.php',
    $vendor . '/php-stubs/wordpress-stubs/wordpress-stubs.php',
    $vendor . '/php-stubs/wp-cli-stubs/wp-cli-stubs.php',
];

$loaded = [];

foreach ($stub_files as $stub_file) {
    if (!is_readable($stub_file)) {
        continue;
    }

    $real = realpath($stub_file);

    if ($real !== false && isset($loaded[$real])) {
        continue;
    }

    require_once $stub_file;

    if ($real !== false) {
        $loaded[$real] = true;
    }
}

require_once __DIR__ . '/globals.php';
