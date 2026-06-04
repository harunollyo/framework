<?php

declare(strict_types=1);

$plugin_root = dirname(__DIR__);
$stubs_dir = $plugin_root . '/libraries/themeum/framework/Console/stubs';
$prefix = $argv[1] ?? 'Themeum';

$candidates = [
    '/var/www/html/framework-library/scripts/prefix-scoped-console-stubs.php',
    dirname(__DIR__, 2) . '/scripts/prefix-scoped-console-stubs.php',
];

$prefix_script = null;

foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $prefix_script = $candidate;
        break;
    }
}

if ($prefix_script === null) {
    fwrite(STDERR, "Could not find prefix-scoped-console-stubs.php.\n");
    exit(1);
}

$command = sprintf(
    'php %s %s %s',
    escapeshellarg($prefix_script),
    escapeshellarg($stubs_dir),
    escapeshellarg($prefix)
);

passthru($command, $exit_code);
exit((int) $exit_code);
