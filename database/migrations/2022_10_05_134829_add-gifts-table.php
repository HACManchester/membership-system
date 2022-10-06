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
        // TODO: Reconcile this with the exact columns in production
        Schema::create('gifts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->integer('months')->nullable(); // how long the code lasts
            $table->string('gifter_name')->nullable(); // set upon redemption
            $table->string('gitee_name')->nullable(); // set upon redemption
            $table->float('credit')->nullable(); // would be safer to store as pence, to avoid precision errors
            $table->date('expires')->nullable(); // unused?
            $table->timestamps();
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
