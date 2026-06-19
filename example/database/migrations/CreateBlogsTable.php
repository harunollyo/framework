<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateBlogsTable implements Migration
{
    public function up()
    {
        Schema::create('framework_blogs', function (Structure $table) {
            $table->id();
            $table->big_integer('user_id')->unsigned();
            $table->big_integer('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('framework_categories')->null_on_delete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->long_text('body');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_blogs');
    }
}
