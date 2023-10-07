<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ToggleEquipmentInductions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function(Blueprint $table) {
            $table->boolean('accepting_inductions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment', function(Blueprint $table) {
            $table->dropColumn('accepting_inductions');
        });
    }
}
