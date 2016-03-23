<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipAttrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ship_attrs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sortno');
            $table->integer('taisen');
            $table->integer('kaihi');
            $table->integer('sakuteki');
            $table->integer('luck');
            $table->integer('level');
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
        Schema::drop('ship_attrs');
    }
}
