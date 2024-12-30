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
            $table->integer('trainer_user_id')->change();
        });
    }
}
