<?php

namespace Framework\Tests\Support\Migrations;

class RecordingOptionStore
{
    public $storage = [];

    public $writes = 0;

    public $deletes = 0;

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->storage) ? $this->storage[$key] : $default;
    }

    public function set($key, $value, $autoload = null)
    {
        $this->storage[$key] = $value;
        $this->writes++;
    }

    public function delete($key)
    {
        unset($this->storage[$key]);
        $this->deletes++;
    }
}
