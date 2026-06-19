<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateBlogSeoTable implements Migration
{
    public function up()
    {
        Schema::create('framework_blog_seo', function (Structure $table) {
            $table->id();
            $table->big_integer('blog_id')->unsigned();
            $table->foreign('blog_id')->references('id')->on('framework_blogs')->cascade_on_delete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();

            $table->unique(['blog_id']);
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_blog_seo');
    }
}
