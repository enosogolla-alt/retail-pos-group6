<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Stock adjustment and movement rules.
 *
 * Owner: Kyle Tarus (M2) and David Muturi (M6)
 * Tasks: 2.6, 6.1, 6.4
 */
final class InventoryTest extends TestCase
{
    public function testRestockIncreasesStock(): void
    {
        $this->markTestIncomplete('Task 2.6 (Kyle Tarus).');
    }

    public function testDamageDecreasesStock(): void
    {
        $this->markTestIncomplete('Task 2.6 (Kyle Tarus).');
    }

    public function testCorrectionSetsExactCount(): void
    {
        $this->markTestIncomplete('Task 2.6 (Kyle Tarus).');
    }

    public function testStockCannotGoBelowZero(): void
    {
        $this->markTestIncomplete('Task 2.6 (Kyle Tarus).');
    }

    public function testAdjustmentRequiresReason(): void
    {
        $this->markTestIncomplete('Task 2.6 (Kyle Tarus).');
    }

    public function testSaleCreatesStockMovement(): void
    {
        $this->markTestIncomplete('Task 6.1 (David Muturi).');
    }

    public function testVoidRestoresStock(): void
    {
        $this->markTestIncomplete('Task 6.4 (David Muturi).');
    }
}
