<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLargeProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // TODO: Reconcile this with the exact columns in production
        Schema::create('large_project', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('size');
            $table->boolean('active');
            $table->unsignedInteger('user_id');        
            $table->timestamps();    

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('large_project');
    }
}
