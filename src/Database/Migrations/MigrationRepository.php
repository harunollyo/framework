<?php

namespace Framework\Database\Migrations;

use Kirki\App\Constants\OptionKeys;
use Framework\Supports\Facades\Option;

use function Framework\app;

class MigrationRepository
{
    /**
     * Get the previous migrations.
     *
     * @return array
     */
    public function get_previous_migrations()
    {
        return Option::get(OptionKeys::MIGRATIONS, []);
    }

    /**
     * Update the migrations.
     *
     * @param array $migrations
     * @return void
     */
    public function update_migrations(array $migrations)
    {
        Option::set(OptionKeys::MIGRATIONS, $migrations);
    }

    /**
     * Remove the migrations.
     *
     * @return void
     */
    public function remove_migrations()
    {
        Option::delete(OptionKeys::MIGRATIONS);
    }

    /**
     * Get the registered migrations.
     *
     * @return array
     */
    public function get_registered_migrations()
    {
        return app()->tagged('app.migrations');
    }

    /**
     * Check if the rollback is enabled.
     *
     * @return bool
     */
    public function is_rollback_enabled()
    {
        return true; // @todo: will be handled later
    }
}
