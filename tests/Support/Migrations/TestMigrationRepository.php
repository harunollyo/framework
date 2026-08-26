<?php

namespace Framework\Tests\Support\Migrations;

use Framework\Database\Migrations\MigrationRepository;

class TestMigrationRepository extends MigrationRepository
{
    protected $registered = [];

    public function set_registered(array $migrations): self
    {
        $this->registered = $migrations;

        return $this;
    }

    public function get_registered_migrations()
    {
        return $this->registered;
    }
}
