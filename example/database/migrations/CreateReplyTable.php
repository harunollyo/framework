<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateReplyTable implements Migration
{
    public function up()
    {
        Schema::create('framework_replies', function (Structure $table) {
            $table->id();
            $table->big_integer('comment_id')->unsigned();
            $table->foreign('comment_id')->references('id')->on('framework_comments')->cascade_on_delete();
            $table->big_integer('user_id')->unsigned();
            $table->text('body');
            $table->timestamps();

            $table->index(['comment_id']);
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_replies');
    }
}
