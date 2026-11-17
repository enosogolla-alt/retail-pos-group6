<?php
class PriceHistory { public function log(int $pid, float $old, float $new, int $uid): void {
    q('INSERT INTO price_history(product_id,old_price,new_price,changed_by) VALUES (?,?,?,?)', [$pid, $old, $new, $uid]); } }
