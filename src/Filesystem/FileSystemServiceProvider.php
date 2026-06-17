<?php
/**
 * Registers the Filesystem class as a singleton and binds the files container alias.
 * Minimal provider that wires file operations into the service container.
 * Loaded during application bootstrap for File facade access.
 *
 * @package    Framework
 * @subpackage Filesystem
 * @since      1.0.0
 */
namespace Framework\Filesystem;

defined('ABSPATH') || exit;

use Framework\ServiceProvider;

class FileSystemServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->alias('files', Filesystem::class);
        $this->app->singleton(Filesystem::class);
    }
}
