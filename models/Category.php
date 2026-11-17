<?php
class Category { public function all(): array { return q('SELECT * FROM categories ORDER BY name')->fetchAll(); } }
