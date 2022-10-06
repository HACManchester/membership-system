<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReconcileUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // TODO: Reconcile this with the exact columns in production
        Schema::table('users', function(Blueprint $table) {
            $table->string('announce_name')->nullable();
            $table->boolean('online_only')->nullable();
            $table->boolean('newsletter')->nullable();
            $table->boolean('postFob')->nullable();
            $table->string('gift')->nullable();
            $table->date('seen_at')->nullable();

            $table->foreign('gift')->references('code')->on('gifts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropForeign(['gift']);
            $table->dropColumn([
                'announce_name',
                'online_only',
                'newsletter',
                'postFob',
                'gift',
                'seen_at',
            ]);
        });
    }
}
