<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * payments.amount, fee and amount_minus_fee move from pounds, as doubles, to
 * integer pence, like every other amount.
 *
 * Both directions check the data first and stop without changing anything if
 * it isn't in the units they expect. That also stops a second run: the largest
 * payment in production is £100, which the first run turns into 10000, far
 * above the 1000 limit.
 *
 * MySQL can't roll back a column change, so the values are converted while the
 * columns can still hold either unit: up() converts and then narrows the
 * columns to integers; down() widens them to doubles and then converts.
 */
return new class extends Migration
{
    /** Pounds: well above any real payment, well below most amounts in pence */
    private const MAX_POUNDS = 1000;

    private const COLUMNS = ['amount', 'fee', 'amount_minus_fee'];

    public function up(): void
    {
        $this->assertWithin(self::MAX_POUNDS, 'pounds');

        DB::transaction(function () {
            DB::table('payments')->update(array_combine(
                self::COLUMNS,
                array_map(fn ($column) => DB::raw("ROUND({$column} * 100)"), self::COLUMNS),
            ));
        });

        Schema::table('payments', function (Blueprint $table) {
            foreach (self::COLUMNS as $column) {
                $table->integer($column)->change();
            }
        });
    }

    /**
     * Back to pounds in the DOUBLE(10, 2) columns they were.
     */
    public function down(): void
    {
        $this->assertWithin(self::MAX_POUNDS * 100, 'pence');

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // Blueprint's double() has no precision, and the columns were DOUBLE(10, 2)
            foreach (self::COLUMNS as $column) {
                DB::statement("ALTER TABLE payments MODIFY {$column} DOUBLE(10, 2) NOT NULL");
            }
        } else {
            Schema::table('payments', function (Blueprint $table) {
                foreach (self::COLUMNS as $column) {
                    $table->double($column)->change();
                }
            });
        }

        DB::transaction(function () {
            DB::table('payments')->update(array_combine(
                self::COLUMNS,
                array_map(fn ($column) => DB::raw("{$column} / 100.0"), self::COLUMNS),
            ));
        });
    }

    private function assertWithin(int $max, string $units): void
    {
        foreach (self::COLUMNS as $column) {
            $outOfRange = DB::table('payments')
                ->where(fn ($query) => $query->where($column, '<', 0)->orWhere($column, '>', $max))
                ->count();

            if ($outOfRange > 0) {
                throw new RuntimeException(sprintf(
                    'Expected payments.%s to hold %s from 0 to %d, but %d rows are out of range. Nothing was changed.',
                    $column,
                    $units,
                    $max,
                    $outOfRange,
                ));
            }
        }
    }
};
