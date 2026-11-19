<?php
// CLI: php database/seed_users.php   (run from project root, after config/config.php exists)
require __DIR__ . '/../core/helpers.php'; require __DIR__ . '/../config/database.php';
foreach ([['admin','Shop Admin','Admin@123','Admin'], ['cashier','Jane Cashier','Cashier@123','Cashier']] as [$u,$n,$p,$r])
    q('INSERT IGNORE INTO users(username,full_name,password_hash,role) VALUES (?,?,?,?)', [$u,$n,password_hash($p, PASSWORD_DEFAULT),$r]);
echo "Seeded admin / cashier.\n";
