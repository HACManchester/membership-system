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
        // TODO: Reconcile this with the exact columns in production
        Schema::table('equipment', function (Blueprint $table) {
            $table->boolean('dangerous');
            $table->string('induction_instructions')->nullable();
            $table->string('trainer_instructions')->nullable();
            $table->string('trained_instructions')->nullable();
            $table->string('docs')->nullable();
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
