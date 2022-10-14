<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReconcileSubscriptionCharge extends Migration
{
    public function __construct()
    {
        /**
         * Hack to allow changes to this table, as it contains an 'enum' field
         * @see https://stackoverflow.com/a/42107554
         */
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_charge', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->change();

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
        Schema::table('subscription_charge', function (Blueprint $table) {
            $table->date('payment_date')->nullable(false)->change();
        });
    }
}
