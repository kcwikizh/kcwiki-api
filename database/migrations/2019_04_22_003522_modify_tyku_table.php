<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTykuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyku', function (Blueprint $table) {
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
        Schema::table('tyku', function (Blueprint $table) {
            $table->string('mapAreaId', 10)->change();
            $table->string('mapId', 10)->change();
            $table->string('cellId', 10)->change();
        });
    }
}
