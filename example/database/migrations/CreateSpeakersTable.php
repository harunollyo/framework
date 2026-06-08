<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateSpeakersTable implements Migration
{
    public function up()
    {
        Schema::create('framework_speakers', function (Structure $table) {
            $table->id();
            $table->string('name');
            $table->text('designation')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_speakers');
    }
}
