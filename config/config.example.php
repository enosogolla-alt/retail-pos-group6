<?php
// Copy to config/config.php (git-ignored) and edit.
return [
    'base_url' => 'http://localhost/pos',
    'db'    => ['host' => '127.0.0.1', 'name' => 'retail_pos', 'user' => 'root', 'pass' => ''],
    'mpesa' => ['simulate' => true, 'key' => '', 'secret' => '', 'shortcode' => '174379',
                'passkey' => '', 'callback' => 'https://YOUR-PUBLIC-URL/pos/mpesa/callback',
                'env' => 'sandbox'],
    'max_failed_logins' => 5, 'lock_minutes' => 15,
];
