<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateEventsTable implements Migration
{
    public function up()
    {
        Schema::create('framework_events', function (Structure $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('location')->nullable();
            $table->string('organizer')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_events');
    }
}
