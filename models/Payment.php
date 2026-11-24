<?php

declare(strict_types=1);

/**
 * Table: payments.
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.4, 4.5
 */
final class Payment extends Model
{
    /**
     * @param array{sale_id: int, method: string, amount_tendered: string, change_given: string, mpesa_code: ?string, phone: ?string} $data
     */
    public static function create(array $data): int
    {
        self::execute(
            'INSERT INTO payments (sale_id, method, amount_tendered, change_given, mpesa_code, phone) VALUES (?, ?, ?, ?, ?, ?)',
            [$data['sale_id'], $data['method'], $data['amount_tendered'], $data['change_given'], $data['mpesa_code'], $data['phone']]
        );

        return self::lastId();
    }

    public static function forSale(int $saleId): ?array
    {
        return self::selectOne('SELECT * FROM payments WHERE sale_id = ?', [$saleId]);
    }

    public static function mpesaCodeExists(string $code): bool
    {
        return self::selectOne('SELECT id FROM payments WHERE mpesa_code = ?', [$code]) !== null;
    }
}
