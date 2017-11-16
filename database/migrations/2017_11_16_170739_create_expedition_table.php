<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpeditionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expedition', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mapId');
            $table->integer('mapAreaId');
            $table->string('cellId',100);
            $table->string('ships',100);
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
        Schema::drop('expedition');
    }
}