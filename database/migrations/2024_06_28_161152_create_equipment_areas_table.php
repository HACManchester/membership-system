<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipmentAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_areas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();

            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description');
        });

        Schema::create('equipment_area_user', function(Blueprint $table) {
            $table->timestamps();

            $table->unsignedBigInteger('equipment_area_id');
            $table->unsignedInteger('user_id');

            $table->foreign('equipment_area_id')->references('id')->on('equipment_areas');
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
        Schema::dropIfExists('equipment_area_users');
        Schema::dropIfExists('equipment_areas');
    }
}
