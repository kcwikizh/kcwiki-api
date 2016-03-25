<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paths', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mapAreaId');
            $table->integer('mapId');
            $table->string('path', 120);
            $table->string('decks', 120);
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
        Schema::drop('paths');
    }
}
