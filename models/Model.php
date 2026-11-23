<?php

declare(strict_types=1);

/**
 * Base class for table models. Every query uses prepared statements.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.1
 */
abstract class Model
{
    protected static function db(): PDO
    {
        return db();
    }

    /**
     * @param array<int|string, mixed> $params
     * @return list<array<string, mixed>>
     */
    protected static function select(string $sql, array $params = []): array
    {
        $statement = static::db()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * @param array<int|string, mixed> $params
     * @return array<string, mixed>|null
     */
    protected static function selectOne(string $sql, array $params = []): ?array
    {
        $statement = static::db()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected static function execute(string $sql, array $params = []): int
    {
        $statement = static::db()->prepare($sql);
        $statement->execute($params);

        return $statement->rowCount();
    }

    protected static function lastId(): int
    {
        return (int) static::db()->lastInsertId();
    }
}
