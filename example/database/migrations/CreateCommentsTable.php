<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateCommentsTable implements Migration
{
    public function up()
    {
        Schema::create('framework_comments', function (Structure $table) {
            $table->id();
            $table->big_integer('blog_id')->unsigned();
            $table->foreign('blog_id')->references('id')->on('framework_blogs')->cascade_on_delete();
            $table->big_integer('user_id')->unsigned();
            $table->big_integer('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('framework_comments')->cascade_on_delete();
            $table->text('body');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['blog_id', 'is_approved']);
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_comments');
    }
}
