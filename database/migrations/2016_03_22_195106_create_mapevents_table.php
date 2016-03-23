<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapeventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mapAreaId');
            $table->integer('mapId');
            $table->integer('cellId');
            $table->integer('eventId');
            $table->integer('count');
            $table->boolean('dantan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('map_events');
    }
}
