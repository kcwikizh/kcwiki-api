<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table){
           $table->increments('id');
           $table->string('title', 256);
           $table->string('ship', 1024)->nullable();
           $table->string('equip', 1024)->nullable();
           $table->string('quest', 1024)->nullable();
           $table->text('content')->nullable();
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
        Schema::drop('news');
    }
}
