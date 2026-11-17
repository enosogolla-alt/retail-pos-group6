<?php

declare(strict_types=1);

/**
 * Checkout terminal, payments, receipts and voids.
 *
 * Owner: Winston Enos (M4)
 * Interface: Stacy Kwamboka (M3), Andria Shikwe (M5)
 * Receipts and voids: David Muturi (M6)
 * Tasks: 3.1-3.6, 4.1-4.6, 5.1-5.4, 6.2, 6.4
 */
final class SaleController
{
    public function checkout(): void
    {
    }

    public function findProduct(): void
    {
    }

    public function createSale(): void
    {
    }

    public function sendMpesaPrompt(): void
    {
    }

    public function mpesaStatus(): void
    {
    }

    public function mpesaCallback(): void
    {
    }

    public function receipt(string $receiptNo): void
    {
    }

    public function voidSale(string $id): void
    {
    }
}
