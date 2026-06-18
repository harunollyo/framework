#!/usr/bin/env php
<?php

$runner = __DIR__ . '/run-phpcs.php';
$max_passes = 10;
$pass = 0;
$exit_code = 0;
$extra_args = array_slice($_SERVER['argv'], 1);
$args = '';

foreach ($extra_args as $arg) {
    $args .= ' ' . escapeshellarg($arg);
}

while ($pass < $max_passes) {
    ++$pass;

    ob_start();
    passthru(
        'php -n -d auto_prepend_file= ' . escapeshellarg($runner) . ' phpcbf' . $args,
        $exit_code
    );
    $output = ob_get_clean();

    echo $output;

    if ($exit_code === 0) {
        break;
    }

    if ($exit_code === 2 && preg_match('/A TOTAL OF 0 ERRORS WERE FIXED/', $output) === 1) {
        break;
    }
}

exit($exit_code === 2 ? 2 : 0);
