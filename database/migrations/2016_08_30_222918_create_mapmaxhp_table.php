<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapmaxhpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_max_hp', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mapAreaId');
            $table->integer('mapId');
            $table->integer('maxHp');
            $table->integer('exp');
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
        Schema::drop('map_max_hp');
    }
}
