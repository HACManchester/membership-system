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
        Schema::create('large_project', function (Blueprint $table) {
            $table->increments('id');
            $table->string('size', 10);
            $table->integer('user_id');
            $table->boolean('active');
            $table->timestamps();
            $table->date('expires_at')->nullable();

            // Live has 'on update current timestamp' on created_at and updated_at, but that feels like a mistake. I
            // also can't seem to replicate it with Laravel migrations. Shouldn't cause an issue with dev testing
            // however.
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
