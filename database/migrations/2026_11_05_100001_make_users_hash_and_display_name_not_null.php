<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Every member gets a hash (for photo and email-confirmation URLs) and a
 * display name (required at signup) when they're created, so neither column
 * should allow null.
 *
 * change() restates the whole column, so on MySQL display_name would otherwise
 * take the table's default collation; it keeps the utf8mb4_general_ci it has in
 * production.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('hash', 50)->nullable(false)->change();
            $this->keepCollation($table->text('display_name')->nullable(false))->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('hash', 50)->nullable()->change();
            $this->keepCollation($table->text('display_name')->nullable())->change();
        });
    }

    private function keepCollation(ColumnDefinition $column): ColumnDefinition
    {
        if (DB::getDriverName() === 'mysql') {
            $column->charset('utf8mb4')->collation('utf8mb4_general_ci');
        }

        return $column;
    }
};
