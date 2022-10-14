<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats', function (Blueprint $table) {
            // This table is utf8mb4, whereas the rest is utf8. utf8mb4 is preferred nowadays for emoji support, so we
            // might want to consider swapping later.
            $table->charset = "utf8mb4";
            $table->collation = "utf8mb4_unicode_ci";

            // Columns
            $table->increments('id');
            $table->date('date');
            $table->string('category', 100);
            $table->string('label', 100);
            $table->string('value', 500);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('stats');
    }
}
