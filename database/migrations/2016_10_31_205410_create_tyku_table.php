<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTykuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyku', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mapAreaId');
            $table->integer('mapId');
            $table->integer('cellId');
            $table->integer('tyku');
            $table->char('rank', 1);
            $table->integer('seiku');
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
        Schema::drop('tyku');
    }
}
