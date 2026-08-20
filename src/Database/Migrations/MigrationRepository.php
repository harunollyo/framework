<?php
/**
 * Persists applied migration history in WordPress options and reads registered migrations from container tags.
 * Records the batch and timestamp of every applied migration so repeated runs execute only pending work.
 * Bridges the migrator with WordPress option storage.
 *
 * @package    Framework
 * @subpackage Database\Migrations
 * @since      1.0.0
 */
namespace Framework\Database\Migrations;

defined('ABSPATH') || exit;

use Framework\Constants\OptionKeys;
use Framework\Supports\Facades\Option;

use function Framework\app;

class MigrationRepository
{
    /**
     * Get the previously applied migrations keyed by class name.
     *
     * @return array<string, array{batch: int, ran_at: string|null}>
     *
     * @since 1.0.0
     */
    public function get_previous_migrations()
    {
        $migrations = Option::get(OptionKeys::MIGRATIONS, []);

        if (!is_array($migrations)) {
            return [];
        }

        $normalized = [];

        foreach ($migrations as $class_name => $entry) {
            $normalized[$class_name] = $this->normalize_entry($entry);
        }

        return $normalized;
    }

    /**
     * Normalize a stored history entry into the batch and timestamp shape.
     *
     * @param mixed $entry The stored entry.
     *
     * @return array{batch: int, ran_at: string|null}
     *
     * @since 1.0.0
     */
    protected function normalize_entry($entry)
    {
        if (!is_array($entry)) {
            return [
                'batch'  => 1,
                'ran_at' => null,
            ];
        }

        return [
            'batch'  => isset($entry['batch']) ? (int) $entry['batch'] : 1,
            'ran_at' => isset($entry['ran_at']) ? $entry['ran_at'] : null,
        ];
    }

    /**
     * Update the migrations.
     *
     * @param array $migrations The migrations.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function update_migrations(array $migrations)
    {
        Option::set(OptionKeys::MIGRATIONS, $migrations);
    }

    /**
     * Remove the migrations.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function remove_migrations()
    {
        Option::delete(OptionKeys::MIGRATIONS);
    }

    /**
     * Record a single migration as applied within the given batch.
     *
     * @param string $class_name The migration class name.
     * @param int $batch The batch number the migration belongs to.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function record_migration(string $class_name, int $batch)
    {
        $migrations = $this->get_previous_migrations();

        $migrations[$class_name] = [
            'batch'  => $batch,
            'ran_at' => gmdate('Y-m-d H:i:s'),
        ];

        $this->update_migrations($migrations);
    }

    /**
     * Remove a single migration from the applied history.
     *
     * @param string $class_name The migration class name.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function forget_migration(string $class_name)
    {
        $migrations = $this->get_previous_migrations();

        unset($migrations[$class_name]);

        if (empty($migrations)) {
            $this->remove_migrations();
            return;
        }

        $this->update_migrations($migrations);
    }

    /**
     * Get every recorded batch number in descending order.
     *
     * @return array<int, int>
     *
     * @since 1.0.0
     */
    public function get_batch_numbers()
    {
        $batches = [];

        foreach ($this->get_previous_migrations() as $entry) {
            $batches[] = (int) $entry['batch'];
        }

        $batches = array_values(array_unique($batches));

        rsort($batches);

        return $batches;
    }

    /**
     * Get the highest recorded batch number.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function get_last_batch_number()
    {
        $batches = $this->get_batch_numbers();

        return empty($batches) ? 0 : $batches[0];
    }

    /**
     * Get the batch number the next migration run should use.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function get_next_batch_number()
    {
        return $this->get_last_batch_number() + 1;
    }

    /**
     * Get the migration class names recorded within the given batch.
     *
     * @param int $batch The batch number.
     *
     * @return array<int, string>
     *
     * @since 1.0.0
     */
    public function get_migrations_in_batch(int $batch)
    {
        $matched = [];

        foreach ($this->get_previous_migrations() as $class_name => $entry) {
            if ((int) $entry['batch'] === $batch) {
                $matched[] = $class_name;
            }
        }

        return $matched;
    }

    /**
     * Get the registered migrations.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function get_registered_migrations()
    {
        return app()->tagged('app.migrations');
    }

    /**
     * Check if the rollback is enabled.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function is_rollback_enabled()
    {
        return true; // @todo: will be handled later
    }
}
