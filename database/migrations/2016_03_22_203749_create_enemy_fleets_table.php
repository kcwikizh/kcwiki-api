<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnemyFleetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enemy_fleets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mapAreaId');
            $table->integer('mapId');
            $table->integer('cellId');
            $table->string('fleets', 100);
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
        Schema::drop('enemy_fleets');
    }
}
