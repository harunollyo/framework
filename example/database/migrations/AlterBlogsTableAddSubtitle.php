<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class AlterBlogsTableAddSubtitle implements Migration
{
    public function up()
    {
        Schema::table('framework_blogs', function (Structure $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->unsigned_integer('view_count')->default(0);
            $table->string('featured_image', 512)->nullable()->change();
            $table->rename_column('excerpt', 'summary');
            $table->index(['view_count']);
        });
    }

    public function down()
    {
        Schema::table('framework_blogs', function (Structure $table) {
            $table->drop_index(['view_count']);
            $table->rename_column('summary', 'excerpt');
            $table->string('featured_image')->nullable()->change();
            $table->drop_column(['subtitle', 'view_count']);
        });
    }
}
