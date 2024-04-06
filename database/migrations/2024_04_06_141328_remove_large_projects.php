<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveLargeProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('large_project');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('large_project', function (Blueprint $table) {
            $table->increments('id');
            $table->string('size', 10);
            $table->integer('user_id');
            $table->boolean('active');
            $table->timestamps();
            $table->date('expires_at')->nullable();
        });
    }
}
