<?php

declare(strict_types=1);

/**
 * Table: suppliers. Deliveries are the restock movements linked to a supplier.
 *
 * Owner: David Muturi (M6)
 * Tasks: 6.6
 */
final class Supplier extends Model
{
    public static function findById(int $id): ?array
    {
        return self::selectOne('SELECT * FROM suppliers WHERE id = ?', [$id]);
    }

    /**
     * Every supplier with its delivery count and last delivery date.
     */
    public static function all(): array
    {
        return self::select(
            "SELECT s.*,
                    COUNT(m.id) AS deliveries,
                    MAX(m.created_at) AS last_delivery
               FROM suppliers s
               LEFT JOIN stock_movements m ON m.supplier_id = s.id AND m.type = 'RESTOCK'
              GROUP BY s.id
              ORDER BY s.is_active DESC, s.name"
        );
    }

    public static function active(): array
    {
        return self::select('SELECT id, name FROM suppliers WHERE is_active = 1 ORDER BY name');
    }

    public static function nameExists(string $name, ?int $exceptId = null): bool
    {
        return self::selectOne(
            'SELECT id FROM suppliers WHERE name = ? AND id <> ?',
            [$name, $exceptId ?? 0]
        ) !== null;
    }

    public static function create(string $name, string $contactPerson, string $phone, string $email, string $address): int
    {
        self::execute(
            'INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)',
            [$name, $contactPerson, $phone, $email, $address]
        );

        return self::lastId();
    }

    public static function update(int $id, string $name, string $contactPerson, string $phone, string $email, string $address): void
    {
        self::execute(
            'UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, email = ?, address = ? WHERE id = ?',
            [$name, $contactPerson, $phone, $email, $address, $id]
        );
    }

    public static function setActive(int $id, bool $active): void
    {
        self::execute('UPDATE suppliers SET is_active = ? WHERE id = ?', [$active ? 1 : 0, $id]);
    }

    /**
     * Most recent deliveries from one supplier, newest first.
     */
    public static function deliveries(int $id, int $limit = 20): array
    {
        $limit = max(1, min($limit, 100));

        return self::select(
            "SELECT m.created_at, m.quantity, m.reason, p.name AS product, u.full_name AS user_name
               FROM stock_movements m
               JOIN products p ON p.id = m.product_id
               JOIN users u ON u.id = m.user_id
              WHERE m.supplier_id = ? AND m.type = 'RESTOCK'
              ORDER BY m.created_at DESC, m.id DESC
              LIMIT " . $limit,
            [$id]
        );
    }
}
