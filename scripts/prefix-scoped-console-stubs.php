<?php

declare(strict_types=1);

$stubs_dir = $argv[1] ?? null;
$prefix = $argv[2] ?? 'Themeum';

if ($stubs_dir === null || $stubs_dir === '') {
    fwrite(STDERR, "Usage: php prefix-scoped-console-stubs.php <stubs-dir> [prefix]\n");
    exit(1);
}

if (!is_dir($stubs_dir)) {
    exit(0);
}

$prefixed_framework = $prefix . '\\Framework\\';
$pattern = '/(?<!' . preg_quote($prefix, '/') . '\\\\)Framework\\\\/';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($stubs_dir, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'stub') {
        continue;
    }

    $path = $file->getPathname();
    $contents = file_get_contents($path);

    if ($contents === false) {
        continue;
    }

    $updated = preg_replace($pattern, $prefixed_framework, $contents);

    if (!is_string($updated) || $updated === $contents) {
        continue;
    }

    file_put_contents($path, $updated);
}
