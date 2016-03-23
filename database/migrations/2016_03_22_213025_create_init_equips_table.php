<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitEquipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('init_equips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sortno');
            $table->integer('slot1')->nullable();
            $table->integer('slot2')->nullable();
            $table->integer('slot3')->nullable();
            $table->integer('slot4')->nullable();
            $table->integer('slot5')->nullable();
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
        Schema::drop('init_equips');
    }
}
