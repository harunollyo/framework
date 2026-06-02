<?php

namespace Framework\Console\Commands;

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
