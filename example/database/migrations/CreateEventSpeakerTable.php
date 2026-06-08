<?php

namespace Example\Database\Migrations;

use Framework\Contracts\Migration;
use Framework\Database\Schema\Structure;
use Framework\Supports\Facades\Schema;

class CreateEventSpeakerTable implements Migration
{
    public function up()
    {
        Schema::create('framework_event_speaker', function (Structure $table) {
            $table->id();
            $table->big_integer('event_id')->unsigned();
            $table->big_integer('speaker_id')->unsigned();
            $table->foreign('event_id')->references('id')->on('framework_events')->cascade_on_delete();
            $table->foreign('speaker_id')->references('id')->on('framework_speakers')->cascade_on_delete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop_if_exists('framework_event_speaker');
    }
}
