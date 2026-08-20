<?php

namespace Framework\Tests\Support\Migrations;

class RecordingSchema
{
    public $calls = [];

    public function disabled_checking_foreign_key_constraints()
    {
        $this->calls[] = 'disable';
    }

    public function enabled_checking_foreign_key_constraints()
    {
        $this->calls[] = 'enable';
    }
}
