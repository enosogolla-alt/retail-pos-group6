<?php
class ProductController {
    public function dashboard(): void {
        Auth::requireRole('Admin'); $rs = new ReportService();
        render('admin/dashboard', ['summary' => $rs->summary(date('Y-m-d')), 'lowStock' => (new Product())->lowStock(), 'recent' => $rs->recent(5)]);
    }
    public function alerts(): void { Auth::requireRole('Admin'); json_out((new Product())->lowStock()); }
    public function index(): void {
        Auth::requireRole('Admin');
        render('admin/products', ['products' => (new Product())->allWithCategory(), 'categories' => (new Category())->all()]);
    }
    public function create(): void {
        Auth::requireRole('Admin');
        $bc = trim($_POST['barcode'] ?? ''); $n = trim($_POST['name'] ?? ''); $price = (float)($_POST['selling_price'] ?? 0);
        $re = max(0, (int)($_POST['reorder_level'] ?? 0)); $cat = ($_POST['category_id'] ?? '') !== '' ? (int)$_POST['category_id'] : null;
        if ($bc === '' || $n === '' || $price <= 0) { flash('error', 'Barcode, name and a price above zero are required.'); redirect('admin/products'); }
        try { $id = (new Product())->create($bc, $n, $cat, $price, $re); AuditLogger::log(Auth::user()['id'], 'PRODUCT_CREATE', "$bc $n"); flash('success', 'Product added.'); }
        catch (PDOException $e) { flash('error', 'That barcode already exists.'); }
        redirect('admin/products');
    }
    public function update(string $id): void {
        Auth::requireRole('Admin'); $id = (int)$id; $m = new Product(); $old = $m->find($id);
        if (!$old) { flash('error', 'Product not found.'); redirect('admin/products'); }
        $price = (float)($_POST['selling_price'] ?? $old['selling_price']);
        if ($price <= 0) { flash('error', 'Price must be above zero.'); redirect('admin/products'); }
        $m->update($id, trim($_POST['name'] ?? $old['name']), ($_POST['category_id'] ?? '') !== '' ? (int)$_POST['category_id'] : null, $price, max(0, (int)($_POST['reorder_level'] ?? 0)));
        if ($price != (float)$old['selling_price']) (new PriceHistory())->log($id, (float)$old['selling_price'], $price, Auth::user()['id']);
        AuditLogger::log(Auth::user()['id'], 'PRODUCT_UPDATE', "id=$id"); flash('success', 'Product updated.'); redirect('admin/products');
    }
    public function deactivate(string $id): void {
        Auth::requireRole('Admin'); (new Product())->toggle((int)$id);
        AuditLogger::log(Auth::user()['id'], 'PRODUCT_TOGGLE', "id=$id"); redirect('admin/products');
    }
    public function stock(): void {
        Auth::requireRole('Admin');
        render('admin/stock', ['products' => (new Product())->allWithCategory(), 'movements' => (new StockMovement())->recent(50)]);
    }
    public function adjustStock(): void {
        Auth::requireRole('Admin');
        $pid = (int)($_POST['product_id'] ?? 0); $type = $_POST['type'] ?? ''; $qty = (int)($_POST['qty'] ?? 0); $reason = trim($_POST['reason'] ?? '');
        if (!in_array($type, ['RESTOCK', 'DAMAGE', 'CORRECTION'], true) || $qty === 0 || $reason === '') { flash('error', 'Type, a non-zero quantity and a reason are required.'); redirect('admin/stock'); }
        if ($type === 'RESTOCK') $qty = abs($qty); if ($type === 'DAMAGE') $qty = -abs($qty);
        $db = db(); $db->beginTransaction();
        try {
            $p = q('SELECT * FROM products WHERE id=? FOR UPDATE', [$pid])->fetch();
            if (!$p) throw new RuntimeException('Product not found.');
            if ($p['stock_qty'] + $qty < 0) throw new RuntimeException('Stock cannot go below zero.');
            (new Product())->adjustStock($pid, $qty);
            (new StockMovement())->create($pid, $qty, $type, $reason, Auth::user()['id']);
            AuditLogger::log(Auth::user()['id'], 'STOCK_ADJUST', "product=$pid $type $qty");
            $db->commit(); flash('success', 'Stock updated.');
        } catch (Throwable $e) { $db->rollBack(); flash('error', $e->getMessage()); }
        redirect('admin/stock');
    }
}
