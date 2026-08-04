<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoomIdToEquipment extends Migration
{
    /**
     * Run the migrations.
     *
     * The legacy `room` string column is intentionally kept for now (rollback
     * safety while the equipment pages are converted to Inertia); it is dropped
     * in the final cleanup once `room_id` is the sole source of truth.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id')->nullable()->after('room');
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('room_id');
        });
    }
}
