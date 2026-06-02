<?php

namespace Framework\Filesystem;

use Framework\ServiceProvider;

class FileSystemServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->alias('files', Filesystem::class);
        $this->app->singleton(Filesystem::class);
    }
}
