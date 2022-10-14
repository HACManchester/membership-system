<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReconcileProfileData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profile_data', function(Blueprint $table) {
            $table->string('website', 250)->change();

            // Timestamps still have different defaults but there seems to be no
            // nice way to solve that in Laravel 5.1. It shouldn't cause any
            // meaningful discrepancies between local and live testing.
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profile_data', function(Blueprint $table) {
            $table->string('website', 10)->change();
        });
    }
}
