<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAcsNode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('acs_nodes');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('acs_nodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('device_id', 30);
            $table->string('queued_command', 100)->nullable();
            $table->boolean('monitor_heartbeat');
            $table->boolean('entry_device')->default(null);
            $table->string('api_key')->nullable();
            $table->dateTime('last_boot')->nullable();
            $table->dateTime('last_heartbeat')->nullable();
            $table->timestamps();
        });
    }
}
