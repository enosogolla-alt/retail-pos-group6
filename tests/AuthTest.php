<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Sign-in, lockout and role rules.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.2, 2.3
 */
final class AuthTest extends TestCase
{
    public function testCorrectPasswordSignsIn(): void
    {
        $this->markTestIncomplete('Task 2.2 (Kyle Tarus).');
    }

    public function testWrongPasswordIsRejected(): void
    {
        $this->markTestIncomplete('Task 2.2 (Kyle Tarus).');
    }

    public function testFiveFailedAttemptsLockAccount(): void
    {
        $this->markTestIncomplete('Task 2.2 (Kyle Tarus).');
    }

    public function testCashierCannotOpenAdminPages(): void
    {
        $this->markTestIncomplete('Task 2.3 (Kyle Tarus).');
    }

    public function testPasswordsAreStoredHashed(): void
    {
        $this->markTestIncomplete('Task 2.2 (Kyle Tarus).');
    }
}
