<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$composer_path = $root . '/composer.json';

/**
 * Print usage and exit.
 *
 * @return void
 *
 * @since 1.0.0
 */
function print_usage()
{
    $usage = <<<'USAGE'
Usage: composer release -- [options]

Options:
  -v, --version=VERSION   Release version (e.g. 1.0.6 or v1.0.6)
  -m, --message=MESSAGE   Commit and annotated tag message
      --dry-run           Preview actions without making changes
      --help              Show this help message

The "--" separator is required so Composer forwards flags to this script.

If -v or -m is omitted, you will be prompted interactively.

Examples:
  composer release -- -v 1.0.6 -m "Fix validation handling"
  composer release -- --dry-run -v 1.0.6 -m "Fix validation handling"
  composer release --

USAGE;

    echo $usage;
}

/**
 * Write a line to stderr.
 *
 * @param string $message Message text.
 *
 * @return void
 *
 * @since 1.0.0
 */
function stderr($message)
{
    fwrite(STDERR, $message . PHP_EOL);
}

/**
 * Abort with an error message.
 *
 * @param string $message Error message.
 *
 * @return void
 *
 * @since 1.0.0
 */
function abort($message)
{
    stderr('Error: ' . $message);
    exit(1);
}

/**
 * Prompt for input on stdin.
 *
 * @param string $question Prompt text.
 *
 * @return string
 *
 * @since 1.0.0
 */
function prompt($question)
{
    echo $question;

    if (function_exists('readline')) {
        $line = readline('');

        if ($line !== false) {
            readline_add_history($line);

            return trim($line);
        }
    }

    $line = fgets(STDIN);

    if ($line === false) {
        return '';
    }

    return trim($line);
}

/**
 * Run a shell command and return its exit code.
 *
 * @param string $command Shell command.
 * @param bool $passthrough When true, stream output to the terminal.
 *
 * @return int
 *
 * @since 1.0.0
 */
function run_command($command, $passthrough = true)
{
    if ($passthrough) {
        passthru($command, $exit_code);

        return (int) $exit_code;
    }

    exec($command . ' 2>&1', $output, $exit_code);

    return (int) $exit_code;
}

/**
 * Run a shell command and capture stdout.
 *
 * @param string $command Shell command.
 *
 * @return string
 *
 * @since 1.0.0
 */
function capture_command($command)
{
    $output = shell_exec($command);

    if ($output === null) {
        return '';
    }

    return trim($output);
}

/**
 * Normalize a version string by stripping a leading "v".
 *
 * @param string $version Raw version input.
 *
 * @return string
 *
 * @since 1.0.0
 */
function normalize_version($version)
{
    $version = trim($version);

    if ($version !== '' && strtolower($version[0]) === 'v') {
        return substr($version, 1);
    }

    return $version;
}

/**
 * Validate semver format x.y.z.
 *
 * @param string $version Normalized version.
 *
 * @return bool
 *
 * @since 1.0.0
 */
function is_valid_semver($version)
{
    return (bool) preg_match('/^\d+\.\d+\.\d+$/', $version);
}

/**
 * Read the version field from composer.json.
 *
 * @param string $composer_path Path to composer.json.
 *
 * @return string
 *
 * @since 1.0.0
 */
function read_composer_version($composer_path)
{
    if (!is_readable($composer_path)) {
        abort('composer.json is not readable.');
    }

    $content = file_get_contents($composer_path);
    $matches = [];

    if ($content === false || !preg_match('/"version"\s*:\s*"([^"]+)"/', $content, $matches)) {
        abort('Could not read version from composer.json.');
    }

    return $matches[1];
}

/**
 * Update the version field in composer.json.
 *
 * @param string $composer_path Path to composer.json.
 * @param string $version New version.
 *
 * @return void
 *
 * @since 1.0.0
 */
function bump_composer_version($composer_path, $version)
{
    $content = file_get_contents($composer_path);

    if ($content === false) {
        abort('Could not read composer.json.');
    }

    $updated = preg_replace(
        '/"version"\s*:\s*"[^"]+"/',
        '"version": "' . $version . '"',
        $content,
        1,
        $count
    );

    if ($count !== 1 || $updated === null) {
        abort('Could not update version in composer.json.');
    }

    if (file_put_contents($composer_path, $updated) === false) {
        abort('Could not write composer.json.');
    }
}

/**
 * Print dry-run action line.
 *
 * @param string $message Action description.
 *
 * @return void
 *
 * @since 1.0.0
 */
function dry_run_line($message)
{
    echo '[dry-run] ' . $message . PHP_EOL;
}

$options = getopt('v:m:h', ['version:', 'message:', 'dry-run', 'help']);

if (isset($options['h']) || isset($options['help'])) {
    print_usage();
    exit(0);
}

$dry_run = isset($options['dry-run']);
$version_input = $options['v'] ?? $options['version'] ?? null;
$message = $options['m'] ?? $options['message'] ?? null;

if ($version_input === null || $version_input === '') {
    $version_input = prompt('Version (e.g. 1.0.6): ');
}

if ($message === null || $message === '') {
    $message = prompt('Release message: ');
}

$version = normalize_version((string) $version_input);
$tag_name = 'v' . $version;

if ($version === '') {
    abort('Version is required.');
}

if ($message === '') {
    abort('Release message is required.');
}

if (!is_valid_semver($version)) {
    abort('Version must be semver format x.y.z (got "' . $version . '").');
}

$current_version = read_composer_version($composer_path);

if (version_compare($version, $current_version, '<=')) {
    abort(
        'Version must be greater than current composer.json version '
        . $current_version . ' (got "' . $version . '").'
    );
}

$branch = capture_command('git rev-parse --abbrev-ref HEAD');

if ($branch !== 'main') {
    abort('Release must be run from the main branch (current: "' . $branch . '").');
}

$dirty = capture_command('git status --porcelain');

if ($dirty !== '') {
    abort('Working tree is not clean. Commit or stash changes before releasing.');
}

if (run_command('git rev-parse ' . escapeshellarg($tag_name) . ' >/dev/null 2>&1', false) === 0) {
    abort('Tag "' . $tag_name . '" already exists locally.');
}

$remote_tag = capture_command(
    'git ls-remote --tags origin ' . escapeshellarg('refs/tags/' . $tag_name)
);

if ($remote_tag !== '') {
    abort('Tag "' . $tag_name . '" already exists on origin.');
}

if ($dry_run) {
    dry_run_line('Would bump composer.json: ' . $current_version . ' → ' . $version);
    dry_run_line('Would run: composer test:unit');
    dry_run_line('Would commit: "' . $message . '"');
    dry_run_line('Would tag: ' . $tag_name . ' (annotated)');
    dry_run_line('Would push: origin ' . $branch . ', origin ' . $tag_name);
    exit(0);
}

echo 'Running unit tests...' . PHP_EOL;

if (run_command('composer test:unit') !== 0) {
    abort('Unit tests failed. Fix failures before releasing.');
}

echo 'Bumping composer.json to ' . $version . '...' . PHP_EOL;
bump_composer_version($composer_path, $version);

echo 'Committing version bump...' . PHP_EOL;

if (run_command('git add composer.json') !== 0) {
    abort('git add failed.');
}

$commit_command = 'git commit -m ' . escapeshellarg($message);

if (run_command($commit_command) !== 0) {
    abort('git commit failed.');
}

echo 'Creating annotated tag ' . $tag_name . '...' . PHP_EOL;

$tag_command = 'git tag -a ' . escapeshellarg($tag_name) . ' -m ' . escapeshellarg($message);

if (run_command($tag_command) !== 0) {
    stderr('Recovery: git reset HEAD~1');
    abort('git tag failed.');
}

echo 'Pushing commit to origin...' . PHP_EOL;

if (run_command('git push origin HEAD') !== 0) {
    stderr('Recovery: git tag -d ' . $tag_name);
    stderr('Recovery: git reset HEAD~1');
    abort('git push failed.');
}

echo 'Pushing tag to origin...' . PHP_EOL;

if (run_command('git push origin ' . escapeshellarg($tag_name)) !== 0) {
    stderr('Recovery: git tag -d ' . $tag_name);
    stderr('Recovery: git reset HEAD~1');
    abort('git push tag failed.');
}

echo 'Released ' . $tag_name . ' successfully.' . PHP_EOL;
