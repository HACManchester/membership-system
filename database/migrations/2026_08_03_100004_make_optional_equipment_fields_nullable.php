<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * These fields are no longer required on the equipment form (usage cost is
 * display-only; obtained date is hidden; photos are added separately), so the
 * columns that were NOT NULL without a usable default are made nullable.
 */
class MakeOptionalEquipmentFieldsNullable extends Migration
{
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('usage_cost_per', 10)->nullable()->change();
            $table->text('photos')->nullable()->change();
            $table->date('obtained_at')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('usage_cost_per', 10)->nullable(false)->change();
            $table->text('photos')->nullable(false)->change();
            $table->date('obtained_at')->nullable(false)->change();
        });
    }
}
