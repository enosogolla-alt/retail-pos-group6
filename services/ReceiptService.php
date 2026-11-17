<?php

declare(strict_types=1);

/**
 * Receipt data for display, printing and reprints.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.2
 */
final class ReceiptService
{
    /**
     * @return array<string, mixed>
     */
    public function build(string $receiptNo): array
    {
        throw new LogicException('Not implemented: task 6.2.');
    }

    /**
     * Same data as build(), marked as a copy and recorded in the audit log.
     *
     * @return array<string, mixed>
     */
    public function reprint(string $receiptNo, int $userId): array
    {
        throw new LogicException('Not implemented: task 6.2.');
    }
}
