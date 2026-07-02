<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$stubs_dir = $root . '/stubs';

$links = [
    'wordpress-stubs.php' => $root . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
    'wp-cli-stubs.php' => $root . '/vendor/php-stubs/wp-cli-stubs/wp-cli-stubs.php',
    'phpunit' => $root . '/vendor/phpunit/phpunit/src',
];

if (!is_dir($stubs_dir) && !mkdir($stubs_dir, 0755, true) && !is_dir($stubs_dir)) {
    fwrite(STDERR, "Could not create stubs directory.\n");
    exit(1);
}

foreach ($links as $name => $target) {
    $link = $stubs_dir . '/' . $name;

    if (!is_readable($target)) {
        fwrite(STDERR, "Skip {$name}: run composer install first ({$target} missing).\n");
        continue;
    }

    if (file_exists($link) || is_link($link)) {
        unlink($link);
    }

    if (@symlink($target, $link)) {
        echo "Linked stubs/{$name}\n";
        continue;
    }

    copy($target, $link);
    echo "Copied stubs/{$name} (symlink unavailable)\n";
}
