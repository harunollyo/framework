<?php
/**
 * Creates a timestamped migration file in the database migrations folder.
 * Uses a stub template and converts the supplied name to snake_case for the filename.
 * Integrates with the migrator by placing files where MigrationRepository discovers them.
 *
 * @package    Framework
 * @subpackage Console\Commands
 * @since      1.0.0
 */
namespace Framework\Console\Commands;

defined('ABSPATH') || exit;

use Framework\Console\CommandBase;
use Framework\Console\Synopsis;
use Framework\Supports\Facades\File;
use Framework\Supports\Str;

use function Framework\app;
use function Framework\database_path;

class MakeMigrationCommand extends CommandBase
{
    /**
     * Command's positional arguments
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $args;

    /**
     * Command's associative arguments
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $assoc;

    /**
     * Migration base path
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected $output_dir;

    /**
     * Initialize the command
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->output_dir = database_path('migrations');
    }

    /**
     * Prepare the command's synopsis and other metadata
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function prepare()
    {
        $this->summary('Create a new migration file')
            ->description("## EXAMPLES \n\n wp kirki make:migration create_users_table")
            ->synopsis(
                Synopsis::type('positional')
                    ->name('name')
                    ->description('The migration name. The name should starts with `create_` and ends with `table`')
            )
            ->synopsis(
                Synopsis::type('assoc')
                    ->name('prefix')
                    ->description('Table prefix (if any)')
                    ->optional()
            )
            ->synopsis(
                Synopsis::type('assoc')
                    ->name('table')
                    ->description('The existing table to alter. Generates an alter migration')
                    ->optional()
            )
            ->synopsis(
                Synopsis::type('assoc')
                    ->name('create')
                    ->description('The table to create. Generates a create migration')
                    ->optional()
            );
    }

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
        $this->args = $args;
        $this->assoc = $assoc;

        $this->create();
    }

    /**
     * Check if the command passed the validation
     *
     * @param mixed $args The positional arguments.
     * @param mixed $assoc The associative arguments.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function passed($args, $assoc)
    {
        return !empty($args[0]);
    }

    /**
     * Get data for migration file
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function data()
    {
        return [
            'stub' => $this->get_stub(),
            'table' => $this->table($this->args[0]),
            'migration_class' => $this->migration_class($this->args[0]),
            'output_file' => sprintf(
                '%s/%s.php',
                $this->output_dir,
                $this->migration_class($this->args[0])
            ),
        ];
    }

    /**
     * Get migration stub content
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function get_stub()
    {
        $stub_name = $this->is_alter_migration() ? 'migration-table.stub' : 'migration.stub';
        $stub_path = $this->stub_path() . '/' . $stub_name;

        if (File::missing($stub_path)) {
            \WP_CLI::error('Migration stub not found: ' . $stub_path);
        }

        return File::get($stub_path);
    }

    /**
     * Determine whether the migration should alter an existing table
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function is_alter_migration()
    {
        return !empty($this->assoc['table']);
    }

    /**
     * Create migration file
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function create()
    {
        $data = $this->data();

        $migration_class = $data['migration_class'];
        $output_file = $data['output_file'];

        if (File::exists($output_file)) {
            \WP_CLI::error('Migration file already exists.');
        }

        $content = $this->populate_stub($data);

        File::put($output_file, $content);

        $this->register_migration($migration_class);

        \WP_CLI::success(sprintf('Migration  [%s] created.', $migration_class));
    }

    /**
     * Append the generated migration to the migrations config file
     *
     * @param string $migration_class The generated migration class name.
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function register_migration(string $migration_class)
    {
        $config_path = app()->config_path('migrations.php');

        if (File::missing($config_path)) {
            $this->cli_warning(
                sprintf(
                    'Migrations config [%s] not found. Register [%s] manually.',
                    $config_path,
                    $migration_class
                )
            );
            return;
        }

        $contents = File::get($config_path);
        $position = strrpos($contents, '];');

        if ($position === false) {
            $this->cli_warning(
                sprintf(
                    'Could not parse migrations config [%s]. Register [%s] manually.',
                    $config_path,
                    $migration_class
                )
            );
            return;
        }

        $qualified_name = sprintf('%s\\%s', app()->get_migrations_namespace(), $migration_class);

        if (strpos($contents, sprintf('\\%s::class', $qualified_name)) !== false) {
            return;
        }

        File::put(
            $config_path,
            sprintf(
                '%s    \\%s::class,%s%s',
                substr($contents, 0, $position),
                $qualified_name,
                PHP_EOL,
                substr($contents, $position)
            )
        );
    }

    /**
     * Get table name from filename
     *
     * @param mixed $filename The filename.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function table($filename)
    {
        $table = $this->base_table_name($filename);

        $prefix = $this->assoc['prefix'] ?? app()->prefix();

        if ($prefix) {
            return sprintf('%s_%s', Str::snake(Str::trim($prefix, '_')), $table);
        }

        return $table;
    }

    /**
     * Get the unprefixed table name the migration targets
     *
     * @param mixed $filename The filename.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function base_table_name($filename)
    {
        if (!empty($this->assoc['table'])) {
            return $this->assoc['table'];
        }

        if (!empty($this->assoc['create'])) {
            return $this->assoc['create'];
        }

        return Str::replace(
            ['create_', '_table'],
            '',
            $filename
        );
    }

    /**
     * Get migration class name from filename
     *
     * @param mixed $filename The filename.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function migration_class($filename)
    {
        return Str::pascal($filename);
    }

    /**
     * Populate stub content
     *
     * @param mixed $data The data payload.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function populate_stub($data)
    {
        return Str::replace(
            ['{{class_name}}', '{{table}}', '{{migrations_namespace}}'],
            [$data['migration_class'], $data['table'], app()->get_migrations_namespace()],
            $data['stub']
        );
    }
}
