<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessLockdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_lockdowns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('started_by');
            $table->string('reason')->nullable();

            // Role names whose members keep their door access while this lockdown runs.
            $table->json('roles');

            $table->timestamp('lifted_at')->nullable();
            $table->integer('lifted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_lockdowns');
    }
}
