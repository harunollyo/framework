<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class AlterBlogsTableAndOthers implements Migration
{
    public function up()
    {
        Schema::table('framework_blogs', function (Structure $table) {
            $table->unsigned_big_integer('created_by')->nullable();
            $table->unsigned_big_integer('updated_by')->nullable();
        });
    }

    public function down()
    {
        Schema::table('framework_blogs', function (Structure $table) {
            $table->drop_column(['created_by', 'updated_by']);
        });
    }
}
