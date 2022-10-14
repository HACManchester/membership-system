<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReconcileEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->boolean('dangerous')->default(0)->nullable();
            $table->longText('induction_instructions')->nullable();
            $table->longText('trainer_instructions')->nullable();
            $table->longText('trained_instructions')->nullable();
            $table->string('docs', 300)->nullable();

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
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn([
                'dangerous',
                'induction_instructions',
                'trainer_instructions',
                'trained_instructions',
                'docs'
            ]);
        });
    }
}
