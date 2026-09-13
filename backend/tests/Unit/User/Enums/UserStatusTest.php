<?php

declare(strict_types=1);

namespace Tests\Unit\User\Enums;

use App\Enums\UserStatus;
use Tests\TestCase;

/**
 * @internal
 */
class UserStatusTest extends TestCase
{
    public function testEnumValuesAndCases(): void
    {
        $this->assertSame('active', UserStatus::Active->value);
        $this->assertSame('suspended', UserStatus::Suspended->value);

        $values = UserStatus::values();

        $this->assertContains('active', $values);
        $this->assertContains('suspended', $values);
        $this->assertCount(2, $values);
    }

    public function testIsActiveHelper(): void
    {
        $this->assertTrue(UserStatus::Active->isActive());
        $this->assertFalse(UserStatus::Suspended->isActive());
    }

    public function testIsSuspendedHelper(): void
    {
        $this->assertTrue(UserStatus::Suspended->isSuspended());
        $this->assertFalse(UserStatus::Active->isSuspended());
    }

    public function testLabelHelper(): void
    {
        $this->assertSame('Aktif', UserStatus::Active->label());
        $this->assertSame('Ditangguhkan', UserStatus::Suspended->label());
    }

    public function testTryFrom(): void
    {
        $this->assertSame(UserStatus::Active, UserStatus::tryFrom('active'));
        $this->assertSame(UserStatus::Suspended, UserStatus::tryFrom('suspended'));
        $this->assertNull(UserStatus::tryFrom('invalid_status'));
    }
}
