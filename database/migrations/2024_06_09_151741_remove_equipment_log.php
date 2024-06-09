<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveEquipmentLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('equipment_log');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::create('equipment_log', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->integer('key_fob_id');
            $table->string('device', 50);
            $table->boolean('active');    //in progress?
            $table->dateTime('started')->nullable();
            $table->dateTime('last_update')->nullable();
            $table->dateTime('finished')->nullable();
			$table->boolean('removed');
			$table->boolean('billed');
			$table->boolean('processed');
			$table->string('reason', 20);
            $table->string('notes');
			$table->timestamps();
		});
    }
}
