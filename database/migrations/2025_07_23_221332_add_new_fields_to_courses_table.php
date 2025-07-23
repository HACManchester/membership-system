<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->text('training_organisation_description')->nullable()->after('wait_time');
            $table->string('schedule_url')->nullable()->after('training_organisation_description');
            $table->string('quiz_url')->nullable()->after('schedule_url');
            $table->string('request_induction_url')->nullable()->after('quiz_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['training_organisation_description', 'schedule_url', 'quiz_url', 'request_induction_url']);
        });
    }
}