<?php

declare(strict_types=1);

/**
 * Application settings template.
 *
 * Copy to config/config.php and adjust for your machine. config.php holds
 * credentials and is excluded from version control.
 */

return [
    'app' => [
        'name' => 'Retail POS',
        'debug' => true,
        'timezone' => 'Africa/Nairobi',
    ],

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'retail_pos',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],

    'mpesa' => [
        'env' => 'sandbox',
        'consumer_key' => '',
        'consumer_secret' => '',
        'shortcode' => '174379',
        'passkey' => '',
        'callback_url' => 'https://your-tunnel.ngrok-free.app/retail-pos-group6/mpesa/callback',
    ],

    'security' => [
        'max_login_attempts' => 5,
        'lockout_minutes' => 15,
        'cashier_max_discount' => 10,
    ],

    'vat_rate' => 16,
];
