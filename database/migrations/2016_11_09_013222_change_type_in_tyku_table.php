<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeInTykuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyku', function (Blueprint $table) {
            $table->integer('minTyku');
            $table->renameColumn('tyku', 'maxTyku');
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
            $table->dropColumn('minTyku');
            $table->renameColumn('maxTyku', 'tyku');
        });
    }
}