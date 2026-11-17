<?php

declare(strict_types=1);

/**
 * Daily sales figures and exports. Voided sales are excluded.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.5
 */
final class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function dailySummary(string $date): array
    {
        throw new LogicException('Not implemented: task 6.5.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function topProducts(string $date, int $limit = 5): array
    {
        throw new LogicException('Not implemented: task 6.5.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function salesByCashier(string $date): array
    {
        throw new LogicException('Not implemented: task 6.5.');
    }

    public function toCsv(string $date): string
    {
        throw new LogicException('Not implemented: task 6.5.');
    }
}
