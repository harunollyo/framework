<?php
/**
 * Runs all registered migrations that have not yet been applied.
 * Delegates to the Migrator service and reports success via WP-CLI.
 * The primary entry point for applying schema changes in a WordPress plugin context.
 *
 * @package    Framework
 * @subpackage Console\Commands
 * @since      1.0.0
 */
namespace Framework\Console\Commands;

defined('ABSPATH') || exit;

use Framework\Console\CommandBase;

use function Framework\migrator;

class MigrateCommand extends CommandBase
{
    /**
     * Run the command
     *
     * @param array $args
     * @param array $assoc
     *
     * @return void
     */
    public function run($args, $assoc)
    {
        migrator()->run();
        \WP_CLI::success('Migrations run successfully.');
    }
}
