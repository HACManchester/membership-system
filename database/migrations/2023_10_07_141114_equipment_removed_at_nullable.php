<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EquipmentRemovedAtNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('equipment', function(Blueprint $table) {
            $table->date('removed_at')->nullable()->change();
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
            // Laravel 10's change() kept the column's nullability, so this rollback never
            // reverted it; Laravel 11 keeps only what's stated, so it's stated here
            $table->date('removed_at')->nullable()->change();
        });
    }
}
