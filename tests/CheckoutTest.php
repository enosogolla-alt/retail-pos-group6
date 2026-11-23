<?php

declare(strict_types=1);

/**
 * Checkout transaction rules.
 *
 * Owner: Winston Enos (M4) · collected by David Muturi (M6)
 * Tasks: 4.3, 4.4, 4.5
 */
final class CheckoutTest extends DatabaseTestCase
{
    private const MILK = 1;
    private const SODA = 7;
    private const WATER = 8;

    private CheckoutService $checkout;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkout = new CheckoutService();
    }

    public function testCompletedSaleRecordsSaleItemsAndPayment(): void
    {
        $milkBefore = $this->stock(self::MILK);

        $result = $this->checkout->checkout(
            [['product_id' => self::MILK, 'qty' => 2], ['product_id' => self::SODA, 'qty' => 1]],
            ['method' => 'CASH', 'tendered' => '500'],
            0,
            self::CASHIER_ID);

        $sale = Sale::findByReceiptNo($result['receipt_no']);
        $this->assertSame('210.00', $sale['total']);
        $this->assertSame('28.97', $sale['vat']);
        $this->assertCount(2, SaleItem::forSale($result['sale_id']));
        $this->assertSame('CASH', Payment::forSale($result['sale_id'])['method']);
        $this->assertSame($milkBefore - 2, $this->stock(self::MILK));
    }

    public function testSaleRollsBackWhenStockIsShort(): void
    {
        $milkBefore = $this->stock(self::MILK);
        $waterBefore = $this->stock(self::WATER);
        $salesBefore = count(Sale::forDate(date('Y-m-d')));

        try {
            $this->checkout->checkout(
                [['product_id' => self::MILK, 'qty' => 1], ['product_id' => self::WATER, 'qty' => $waterBefore + 1]],
                ['method' => 'CASH', 'tendered' => '99999'],
                0,
                self::CASHIER_ID);
            $this->fail('A sale with short stock must be refused.');
        } catch (DomainException $exception) {
            $this->assertStringContainsString('in stock', $exception->getMessage());
        }

        $this->assertSame($milkBefore, $this->stock(self::MILK), 'Milk stock must not change when the sale fails.');
        $this->assertSame($waterBefore, $this->stock(self::WATER));
        $this->assertCount($salesBefore, Sale::forDate(date('Y-m-d')), 'No sale may be saved.');
    }

    public function testCashBelowTotalIsRejected(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Amount received is less than the total.');

        $this->checkout->checkout([['product_id' => self::MILK, 'qty' => 2]], ['method' => 'CASH', 'tendered' => '100'], 0, self::CASHIER_ID);
    }

    public function testChangeIsCalculatedCorrectly(): void
    {
        $result = $this->checkout->checkout([['product_id' => self::MILK, 'qty' => 2]], ['method' => 'CASH', 'tendered' => '200'], 0, self::CASHIER_ID);

        $this->assertSame('130.00', $result['total']);
        $this->assertSame('70.00', $result['change']);
    }

    public function testUsedMpesaCodeIsRejected(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('already been used');

        // QK7H2LM9PX is used by a seeded sale.
        $this->checkout->checkout(
            [['product_id' => self::MILK, 'qty' => 1]],
            ['method' => 'MPESA', 'phone' => '0712345678', 'mpesa_code' => 'QK7H2LM9PX'],
            0,
            self::CASHIER_ID);
    }

    public function testDiscountAboveLimitIsRejected(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('The maximum discount is 10%');

        $this->checkout->checkout([['product_id' => self::MILK, 'qty' => 1]], ['method' => 'CASH', 'tendered' => '100'], 15, self::CASHIER_ID);
    }

    public function testDiscountAtTheLimitIsAccepted(): void
    {
        $result = $this->checkout->checkout([['product_id' => self::MILK, 'qty' => 2]], ['method' => 'CASH', 'tendered' => '200'], 10, self::CASHIER_ID);

        $this->assertSame('117.00', $result['total']);
    }

    public function testCashierHistoryShowsOnlyTheirOwnSales(): void
    {
        $before = count(Sale::forCashier(self::CASHIER_ID, date('Y-m-d')));
        $this->checkout->checkout([['product_id' => self::MILK, 'qty' => 1]], ['method' => 'CASH', 'tendered' => '100'], 0, self::CASHIER_ID);

        $sales = Sale::forCashier(self::CASHIER_ID, date('Y-m-d'));
        $this->assertCount($before + 1, $sales);
        foreach ($sales as $sale) {
            $this->assertSame(self::CASHIER_ID, (int) $sale['user_id']);
        }
    }

    public function testCashierSummaryCountsOnlyTheirCompletedSales(): void
    {
        $before = Sale::summaryForDate(date('Y-m-d'), self::CASHIER_ID);
        $this->checkout->checkout([['product_id' => self::MILK, 'qty' => 2]], ['method' => 'CASH', 'tendered' => '200'], 0, self::CASHIER_ID);

        $after = Sale::summaryForDate(date('Y-m-d'), self::CASHIER_ID);
        $this->assertSame((int) $before['count'] + 1, (int) $after['count']);
        $this->assertSame(round((float) $before['cash_total'] + 130, 2), round((float) $after['cash_total'], 2));
    }
}
