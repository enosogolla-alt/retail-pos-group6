<?php
class AuditLogger { public static function log(?int $uid, string $action, string $details = ''): void { (new AuditLog())->add($uid, $action, $details); } }
