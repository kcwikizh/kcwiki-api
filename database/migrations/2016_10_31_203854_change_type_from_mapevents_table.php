<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeFromMapeventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('map_events', function (Blueprint $table) {
            $table->string('eventId',100)->change();
            $table->string('count',100)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('map_events', function (Blueprint $table) {
            $table->integer('eventId')->change();
            $table->integer('count')->change();
        });
    }
}