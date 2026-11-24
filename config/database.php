<?php

declare(strict_types=1);

/**
 * Shared PDO connection.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.1
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $settings = config('db');
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $settings['host'],
        $settings['port'],
        $settings['name'],
        $settings['charset']
    );

    $pdo = new PDO($dsn, $settings['user'], $settings['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Keeps NOW() and stored times in Nairobi time, matching PHP's date().
    $pdo->exec("SET time_zone = '+03:00'");

    return $pdo;
}
