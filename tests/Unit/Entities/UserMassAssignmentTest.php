<?php

namespace Tests\Unit\Entities;

use BB\Entities\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Membership state, door access, bans and payment setup are only ever set by
 * admin or system code, assigning each one explicitly. Keeping them out of mass
 * assignment means a request field list that lets one through can't set it.
 */
class UserMassAssignmentTest extends TestCase
{
    /**
     * @return array<string, array{string, mixed}>
     */
    public static function adminManagedFields(): array
    {
        return [
            'status' => ['status', 'active'],
            'active' => ['active', true],
            'banned' => ['banned', true],
            'banned_reason' => ['banned_reason', 'reason'],
            'banned_date' => ['banned_date', '2026-01-01 00:00:00'],
            'key_holder' => ['key_holder', true],
            'trusted' => ['trusted', true],
            'induction_completed' => ['induction_completed', true],
            'key_deposit_payment_id' => ['key_deposit_payment_id', 1],
            'payment_method' => ['payment_method', 'cash'],
            'gift' => ['gift', 'GIFTCODE'],
            'seen_at' => ['seen_at', '2026-01-01 00:00:00'],
            'hash' => ['hash', 'chosen-hash'],
        ];
    }

    #[Test]
    #[DataProvider('adminManagedFields')]
    public function an_admin_managed_field_cannot_be_mass_assigned(string $field, mixed $value)
    {
        $this->expectException(MassAssignmentException::class);

        (new User)->fill([$field => $value]);
    }
}
