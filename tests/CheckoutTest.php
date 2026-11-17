<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Checkout transaction rules.
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.3, 4.4, 4.5
 */
final class CheckoutTest extends TestCase
{
    public function testCompletedSaleRecordsSaleItemsAndPayment(): void
    {
        $this->markTestIncomplete('Task 4.5 (Winston Enos).');
    }

    public function testSaleRollsBackWhenStockIsShort(): void
    {
        $this->markTestIncomplete('Task 4.5 (Winston Enos).');
    }

    public function testCashBelowTotalIsRejected(): void
    {
        $this->markTestIncomplete('Task 4.4 (Winston Enos).');
    }

    public function testChangeIsCalculatedCorrectly(): void
    {
        $this->markTestIncomplete('Task 4.4 (Winston Enos).');
    }

    public function testUsedMpesaCodeIsRejected(): void
    {
        $this->markTestIncomplete('Task 4.4 (Winston Enos).');
    }

    public function testCashierDiscountAboveLimitIsRejected(): void
    {
        $this->markTestIncomplete('Task 4.3 (Winston Enos).');
    }
}
