<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyMapRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('map_records', function (Blueprint $table) {
            $table->string('mapAreaId', 10)->change();
            $table->string('mapId', 10)->change();
            $table->string('cellId', 10)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('map_records', function (Blueprint $table) {
            $table->integer('mapAreaId')->change();
            $table->integer('mapId')->change();
            $table->integer('cellId')->change();
        });
    }
}