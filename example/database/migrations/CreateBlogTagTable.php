<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateBlogTagTable implements Migration
{
    public function up()
    {
        Schema::create('framework_blog_tag', function (Structure $table) {
            $table->id();
            $table->big_integer('blog_id')->unsigned();
            $table->foreign('blog_id')->references('id')->on('framework_blogs')->cascade_on_delete();
            $table->big_integer('tag_id')->unsigned();
            $table->foreign('tag_id')->references('id')->on('framework_tags')->cascade_on_delete();
            $table->timestamps();

            $table->unique(['blog_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_blog_tag');
    }
}
