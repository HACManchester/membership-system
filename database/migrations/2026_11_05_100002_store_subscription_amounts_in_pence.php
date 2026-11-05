<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * users.monthly_subscription and subscription_charge.amount move from whole
 * pounds to pence, in the same integer columns, so amounts can include pence
 * (e.g. 2250 for £22.50) and match the pence in config/membership.php.
 * payments.amount is still in pounds.
 *
 * Both directions check the data first and stop without changing anything if
 * it isn't in the units they expect. That also stops a second run: the largest
 * subscription in production is £1000, which the first run turns into 100000,
 * far above the 5000 limit.
 */
return new class extends Migration
{
    /** Pounds: well above any real subscription, well below any amount in pence */
    private const MAX_POUNDS = 5000;

    private const COLUMNS = [
        'users' => 'monthly_subscription',
        'subscription_charge' => 'amount',
    ];

    public function up(): void
    {
        $this->assertWithin(self::MAX_POUNDS, 'pounds');

        DB::transaction(function () {
            foreach (self::COLUMNS as $table => $column) {
                DB::table($table)->whereNotNull($column)->update([$column => DB::raw("{$column} * 100")]);
            }
        });
    }

    /**
     * Back to whole pounds: any pence are rounded away.
     */
    public function down(): void
    {
        $this->assertWithin(self::MAX_POUNDS * 100, 'pence');

        DB::transaction(function () {
            foreach (self::COLUMNS as $table => $column) {
                DB::table($table)->whereNotNull($column)->update([$column => DB::raw("ROUND({$column} / 100.0)")]);
            }
        });
    }

    private function assertWithin(int $max, string $units): void
    {
        foreach (self::COLUMNS as $table => $column) {
            $outOfRange = DB::table($table)
                ->whereNotNull($column)
                ->where(fn ($query) => $query->where($column, '<', 0)->orWhere($column, '>', $max))
                ->count();

            if ($outOfRange > 0) {
                throw new RuntimeException(sprintf(
                    'Expected %s.%s to hold %s from 0 to %d, but %d rows are out of range. Nothing was changed.',
                    $table,
                    $column,
                    $units,
                    $max,
                    $outOfRange,
                ));
            }
        }
    }
};
