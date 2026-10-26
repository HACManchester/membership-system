<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The settings table was created with no primary key, so nothing stopped a key
 * appearing twice. This makes `key` the primary key, and adds timestamps and
 * `updated_by` so the admin page can say who last changed a setting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->timestamps();
            $table->unsignedInteger('updated_by')->nullable();
            $table->primary('key');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn(['created_at', 'updated_at', 'updated_by']);
        });
    }
};
