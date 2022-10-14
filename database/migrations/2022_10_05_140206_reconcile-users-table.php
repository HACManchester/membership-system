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
        Schema::table('users', function (Blueprint $table) {
            // Reconcile existing columns
            $table->text('display_name')->nullable()->change();
            $table->boolean('trusted')->default(1)->change();

            // Add new columns
            $table->text('announce_name')->nullable()->charset('utf32')->collation('utf32_unicode_520_ci')->after('display_name');
            $table->timestamp('seen_at')->nullable();
            $table->string('find_us')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('online_only')->nullable(); // Should be boolean?
            $table->boolean('postFob')->nullable()->default(0);
            $table->string('gift', 20);
            $table->integer('newsletter'); // Should be boolean?

            // Timestamps still have different defaults but there seems to be no
            // nice way to solve that in Laravel 5.1. It shouldn't cause any
            // meaningful discrepancies between local and live testing.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->change();
            $table->boolean('trusted')->default(null)->change();

            $table->dropColumn([
                'announce_name',
                'online_only',
                'newsletter',
                'postFob',
                'gift',
                'seen_at',
                'find_us',
                'deleted_at',
            ]);
        });
    }
}
