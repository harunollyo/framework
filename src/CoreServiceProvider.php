<?php
/**
 * Registers the framework's essential services during application bootstrap.
 * Binds database, schema, migration, discovery, manager, and scheduler components into the container.
 * Boots listener and policy discovery caching plus scheduler initialization.
 *
 * @package    Framework
 * @since      1.0.0
 */
namespace Framework;

defined('ABSPATH') || exit;

use Framework\Console\CommandManager;
use Framework\Database\Connection\Connection;
use Framework\Database\Connection\DatabaseManager;
use Framework\Database\Migrations\Migrator;
use Framework\Database\Schema\SchemaManager;
use Framework\Discovery\ListenerDiscovery;
use Framework\Discovery\PolicyDiscovery;
use Framework\Managers\EventManager;
use Framework\Managers\LogManager;
use Framework\Managers\PolicyManager;
use Framework\ServiceProvider;
use Framework\Managers\DateManager;
use Framework\Scheduler\Scheduler;
use Framework\Http\Response;
use Framework\Supports\Carbon;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register the hooks to the application.
     *
     * @return void
     */
    public function register()
    {
        $this->register_carbon();
        $this->register_database_services();
        $this->register_discoveries();
        $this->register_managers();
        $this->register_migrations();

        $this->app->singleton(Response::class);

        if (class_exists(\Faker\Factory::class)) {
            $this->app->singleton(\Faker\Factory::class, function () {
                return \Faker\Factory::create();
            });
        }
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make(PolicyDiscovery::class)
            ->discover()
            ->cache();

        $this->app->make(ListenerDiscovery::class)
            ->discover()
            ->cache();

        Scheduler::boot();
    }

    /**
     * Register the framework Carbon subclass for date operations.
     *
     * @return void
     */
    protected function register_carbon()
    {
        if (method_exists(Carbon::class, 'use')) {
            Carbon::use(Carbon::class);
        }
    }

    /**
     * Register the managers.
     *
     * @return void
     */
    protected function register_managers()
    {
        $this->app->singleton(DatabaseManager::class);
        $this->app->singleton(SchemaManager::class);
        $this->app->singleton(LogManager::class);
        $this->app->singleton(EventManager::class);
        $this->app->singleton(PolicyManager::class);
        $this->app->singleton(DateManager::class);
        $this->app->singleton(CommandManager::class);
    }

    /**
     * Register the discoveries.
     *
     * @return void
     */
    protected function register_discoveries()
    {
        $this->app->singleton(ListenerDiscovery::class);
        $this->app->singleton(PolicyDiscovery::class);
    }

    /**
     * Register the database singletons.
     *
     * @return void
     */
    protected function register_database_services()
    {
        $this->app->singleton(Connection::class);
        $this->app->singleton(Migrator::class);
    }

    /**
     * Register the migrations tags.
     *
     * @return void
     */
    protected function register_migrations()
    {
        $migrations_path = $this->app->config_path('migrations.php');

        if (!file_exists($migrations_path)) {
            return;
        }

        $migrations = include $migrations_path;

        if (empty($migrations)) {
            return;
        }

        $this->app->tag($migrations, 'app.migrations');
    }
}
