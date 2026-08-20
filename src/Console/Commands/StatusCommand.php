<?php
/**
 * Reports which registered migrations have been applied and which are still pending.
 * Renders the migrator status as a table including the batch each applied migration belongs to.
 * Provides a read-only view of migration history without altering the database.
 *
 * @package    Framework
 * @subpackage Console\Commands
 * @since      1.0.0
 */
namespace Framework\Console\Commands;

defined('ABSPATH') || exit;

use Framework\Console\CommandBase;

use function Framework\migrator;

class StatusCommand extends CommandBase
{
    /**
     * Run the command
     *
     * @param mixed $args The positional arguments.
     * @param mixed $assoc The associative arguments.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function run($args, $assoc)
    {
        $rows = migrator()->status();

        if (empty($rows)) {
            $this->cli_line('No migrations are registered.');
            return;
        }

        call_user_func(
            '\\WP_CLI\\Utils\\format_items',
            'table',
            $this->format_rows($rows),
            ['Ran', 'Migration', 'Batch']
        );
    }

    /**
     * Format the migrator status rows for display.
     *
     * @param array $rows The migrator status rows.
     *
     * @return array<int, array{Ran: string, Migration: string, Batch: string}>
     *
     * @since 1.0.0
     */
    protected function format_rows(array $rows)
    {
        $formatted = [];

        foreach ($rows as $row) {
            $formatted[] = [
                'Ran'       => $row['ran'] ? 'Yes' : 'No',
                'Migration' => $row['migration'],
                'Batch'     => is_null($row['batch']) ? '' : (string) $row['batch'],
            ];
        }

        return $formatted;
    }

    /**
     * Prepare the command synopsis and metadata
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function prepare()
    {
        $this->summary('Show the status of each registered migration')
            ->description("## EXAMPLES \n\n wp kirki migrate:status");
    }
}
