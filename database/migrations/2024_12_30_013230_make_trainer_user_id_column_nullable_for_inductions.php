<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeTrainerUserIdColumnNullableForInductions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inductions', function (Blueprint $table) {
            $table->integer('trainer_user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inductions', function (Blueprint $table) {
            // Laravel 10's change() kept the column's nullability, so this rollback never
            // reverted it; Laravel 11 keeps only what's stated, so it's stated here
            $table->integer('trainer_user_id')->nullable()->change();
        });
    }
}
