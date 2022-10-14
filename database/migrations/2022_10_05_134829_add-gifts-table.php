<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gifts', function (Blueprint $table) {
            // This table is utf8mb4, whereas the rest is utf8. utf8mb4 is preferred nowadays for emoji support, so we
            // might want to consider swapping later.
            $table->charset = "utf8mb4";
            $table->collation = "utf8mb4_unicode_ci";

            // Columns
            $table->increments('id');
            $table->string('code', 20);
            $table->string('gifter_name', 50)->nullable(); // set upon redemption
            $table->string('giftee_name', 50)->nullable(); // set upon redemption
            $table->integer('months')->default(0); // how long the code lasts
            $table->float('credit')->default(0); // would be safer to store as pence, to avoid precision errors
            $table->date('expires'); // unused?

            // No timestamps. Maybe add in a later migration?
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('gifts');
    }
}
