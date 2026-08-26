<?php

namespace Framework\Tests\Support\Migrations;

use Framework\Contracts\Migration;
use RuntimeException;

class RecordingMigration implements Migration
{
    public static $order = [];

    public $up_calls = 0;

    public $down_calls = 0;

    public $throw_on_up = false;

    public $throw_on_down = false;

    public function up()
    {
        if ($this->throw_on_up) {
            throw new RuntimeException(sprintf('up failed: %s', static::class));
        }

        $this->up_calls++;
        static::$order[] = 'up:' . $this->short_name();
    }

    public function down()
    {
        if ($this->throw_on_down) {
            throw new RuntimeException(sprintf('down failed: %s', static::class));
        }

        $this->down_calls++;
        static::$order[] = 'down:' . $this->short_name();
    }

    protected function short_name(): string
    {
        $parts = explode('\\', static::class);

        return end($parts);
    }

    public static function reset_order(): void
    {
        static::$order = [];
    }
}
