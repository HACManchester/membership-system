<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixUsersNullableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->string('gift', 20)->nullable()->change();
            $table->boolean('visited_forum')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->string('gift', 20)->change();
            $table->boolean('visited_forum')->change();
        });
    }
}
