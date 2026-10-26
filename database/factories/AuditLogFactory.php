<?php

namespace Database\Factories;

use BB\Entities\AuditLog;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A member's change to their own record, in the format UserAuditObserver logs.
 *
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'admin_id' => null,
            'action' => 'user_updated',
            'description' => 'status [is active, was setting-up]',
        ];
    }

    public function byAdmin(): static
    {
        return $this->state(['admin_id' => User::factory()->admin()]);
    }
}
