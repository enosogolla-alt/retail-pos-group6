<?php

declare(strict_types=1);

/**
 * Table: users.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.2, 2.8
 */
final class User extends Model
{
    public static function findById(int $id): ?array
    {
        throw new LogicException('Not implemented: task 2.8.');
    }

    public static function findByUsername(string $username): ?array
    {
        throw new LogicException('Not implemented: task 2.2.');
    }

    public static function all(): array
    {
        throw new LogicException('Not implemented: task 2.8.');
    }

    public static function create(array $data): int
    {
        throw new LogicException('Not implemented: task 2.8.');
    }

    public static function update(int $id, array $data): void
    {
        throw new LogicException('Not implemented: task 2.8.');
    }

    public static function setActive(int $id, bool $active): void
    {
        throw new LogicException('Not implemented: task 2.8.');
    }

    public static function setPassword(int $id, string $passwordHash, bool $mustChange): void
    {
        throw new LogicException('Not implemented: task 2.8.');
    }

    public static function addFailedAttempt(int $id, int $maxAttempts, int $lockMinutes): void
    {
        throw new LogicException('Not implemented: task 2.2.');
    }

    public static function resetFailedAttempts(int $id): void
    {
        throw new LogicException('Not implemented: task 2.2.');
    }
}
