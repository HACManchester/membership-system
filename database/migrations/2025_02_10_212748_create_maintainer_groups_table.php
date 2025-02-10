<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintainerGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintainer_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();

            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description');

            $table->unsignedBigInteger('equipment_area_id')->nullable();
            $table->foreign('equipment_area_id')->references('id')->on('equipment_areas');
        });

        Schema::create('maintainer_group_user', function(Blueprint $table) {
            $table->timestamps();

            $table->unsignedBigInteger('maintainer_group_id');
            $table->unsignedInteger('user_id');

            $table->foreign('maintainer_group_id')->references('id')->on('maintainer_groups');
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
        Schema::dropIfExists('maintainer_groups');
        Schema::dropIfExists('maintainer_group_users');
    }
}
