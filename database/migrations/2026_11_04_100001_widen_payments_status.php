<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * payments.status held 20 characters, too few for GoCardless's
 * "pending_customer_approval", which was stored cut short as
 * "pending_customer_app". This widens the column and repairs those rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 40)->change();
        });

        DB::table('payments')
            ->where('status', 'pending_customer_app')
            ->update(['status' => 'pending_customer_approval']);
    }

    public function down(): void
    {
        // Cut anything longer back to 20 characters first, as it was stored before,
        // so narrowing the column doesn't fail or truncate silently
        DB::table('payments')
            ->whereRaw('LENGTH(status) > 20')
            ->update(['status' => DB::raw('SUBSTR(status, 1, 20)')]);

        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });
    }
};
