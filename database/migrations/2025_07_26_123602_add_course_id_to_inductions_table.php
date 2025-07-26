<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCourseIdToInductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inductions', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->after('key');
            $table->timestamp('sign_off_requested_at')->nullable()->after('trainer_user_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('restrict');
            $table->index('course_id');
            $table->index('sign_off_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inductions', function (Blueprint $table) {
            // Sqlite doesn't support dropping foreign keys. It'll all go with the column.
            if (config('database.default') !== 'sqlite') {
                $table->dropForeign(['course_id']);
                $table->dropIndex(['course_id']);
                $table->dropIndex(['sign_off_requested_at']);
            }

            $table->dropColumn(['course_id', 'sign_off_requested_at']);
        });
    }
}
