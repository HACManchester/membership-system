<?php

namespace Tests\Unit\Providers;

use BB\Entities\ProfileData;
use Illuminate\Database\Eloquent\MassAssignmentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    #[Test]
    public function mass_assigning_an_attribute_that_is_not_fillable_throws_outside_production()
    {
        $this->expectException(MassAssignmentException::class);

        new ProfileData(['tagline' => 'Allowed', 'profile_photo' => true]);
    }
}
