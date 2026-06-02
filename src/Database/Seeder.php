<?php

namespace Framework\Database;

use Framework\Supports\Arr;
use Framework\Supports\Facades\DB;
use Framework\Supports\Facades\Log;
use Throwable;

use function Framework\app;

class Seeder
{
    /**
     * Store the called seeder classes
     * 
     * @var string[]
     */
    protected static $called = [];

    /**
     * Track the already resolved seeders so that it doesn't run again
     *
     * @var string[]
     */
    protected static $resolved = [];

    /**
     * Call the seeders
     *
     * @param string|string[] $class
     * @return Seeder
     */
    public function call($class)
    {
        $classes = Arr::wrap($class);

        foreach ($classes as $class) {
            if (!in_array($class, static::$called, true) && empty(static::$resolved[$class])) {
                static::$called[] = $class;
            }
        }

        return $this;
    }

    /**
     * Resolve the seeder
     *
     * @param string $class
     * @return Seeder
     */
    protected function resolve($class)
    {
        return app()->make($class);
    }

    /**
     * Run the seeder
     *
     * @return void
     */
    public function run()
    {
        //
    }

    /**
     * Run the seeders
     *
     * @return void
     */
    public function __invoke()
    {
        try {
            while (!empty(static::$called)) {
                $seeder = array_shift(static::$called);
                $this->resolve($seeder)->run();

                static::$resolved[$seeder] = true;
            }
        } catch (Throwable $exception) {
            throw $exception;
        }
    }
}
