<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDetectedDevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('detected_devices');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('detected_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 50);
            $table->string('mac_address', 25);
            $table->string('display_name', 100)->nullable();
            $table->timestamps();
        });
    }
}
