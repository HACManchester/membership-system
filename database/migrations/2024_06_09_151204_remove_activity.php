<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('access_log');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('access_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('key_fob_id');
            $table->string('response', 5);   //Status code
            $table->string('service', 50);
            $table->boolean('delayed');
            $table->timestamps();
        });
    }
}
