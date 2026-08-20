<?php
/**
 * Rolls back the migrations belonging to the most recently applied batches.
 * Undoes each migration in reverse registration order and removes it from the applied history.
 * Accepts a step count so several batches can be unwound in a single invocation.
 *
 * @package    Framework
 * @subpackage Console\Commands
 * @since      1.0.0
 */
namespace Framework\Console\Commands;

defined('ABSPATH') || exit;

use Framework\Console\CommandBase;
use Framework\Console\Synopsis;

use function Framework\migrator;

class RollbackCommand extends CommandBase
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
        $steps = isset($assoc['step']) ? (int) $assoc['step'] : 1;

        if ($steps < 1) {
            $this->cli_error('The --step value must be a positive integer.');
            return;
        }

        $rolled_back = migrator()->rollback($steps);

        if (empty($rolled_back)) {
            $this->cli_line('Nothing to roll back.');
            return;
        }

        foreach ($rolled_back as $class_name) {
            $this->cli_line(sprintf('Rolled back: %s', $class_name));
        }

        $this->cli_success(
            sprintf(
                'Rolled back %d migration(s).',
                count($rolled_back)
            )
        );
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
        $this->summary('Roll back the migrations of the most recent batch')
            ->description("## EXAMPLES \n\n wp kirki migrate:rollback \n\n wp kirki migrate:rollback --step=2")
            ->synopsis(
                Synopsis::type('assoc')
                    ->name('step')
                    ->description('The number of batches to roll back')
                    ->optional()
            );
    }
}
