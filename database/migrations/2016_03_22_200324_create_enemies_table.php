<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnemiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enemies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('enemyId');
            $table->integer('maxHP');
            $table->integer('slot1');
            $table->integer('slot2');
            $table->integer('slot3');
            $table->integer('slot4');
            $table->integer('slot5');
            $table->integer('houg'); // 火力
            $table->integer('raig'); // 雷装
            $table->integer('tyku'); // 对空
            $table->integer('souk'); // 装甲
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
        Schema::drop('enemies');
    }
}
