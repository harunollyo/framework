<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateTagsTable implements Migration
{
    public function up()
    {
        Schema::create('framework_tags', function (Structure $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_tags');
    }
}
