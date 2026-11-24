<?php /* Stock levels and movements · Owner: Xavier Biko (M1) · Task 1.5 · Prototype: Stock */ ?>
<?php
/**
 * Variables supplied by ProductController::stock():
 * @var array $products       Active products.
 * @var array $movements      Latest stock movements.
 * @var array $totals         Units in stock and their value at cost.
 * @var int   $movementsToday Movements recorded today.
 * @var int   $lowStockCount  Products at or below their reorder level.
 */
$typeLabels = ['SALE' => 'Sale', 'RESTOCK' => 'Restock', 'DAMAGE' => 'Damage', 'CORRECTION' => 'Correction', 'VOID' => 'Void'];
$typeBadges = ['SALE' => 'badge-info', 'RESTOCK' => 'badge-success', 'DAMAGE' => 'badge-danger', 'CORRECTION' => 'badge-warning', 'VOID' => ''];
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Stock</h1>
        <p class="page-subtitle">Every change needs a reason and is recorded below. Sales and voids are recorded automatically.</p>
    </div>
</div>

<div class="stat-grid">
    <div class="card stat-card">
        <span class="stat-label">Units in stock</span>
        <span class="stat-value"><?= e(number_format($totals['units'])) ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Stock value (cost)</span>
        <span class="stat-value"><?= e(kes($totals['value'])) ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Movements today</span>
        <span class="stat-value"><?= e($movementsToday) ?></span>
    </div>
    <div class="card stat-card<?= $lowStockCount > 0 ? ' stat-card-alert' : '' ?>">
        <span class="stat-label">Low-stock items</span>
        <span class="stat-value"><?= e($lowStockCount) ?></span>
    </div>
</div>

<section class="card" aria-labelledby="levels-title">
    <div class="card-header">
        <h2 class="card-title" id="levels-title">Stock levels</h2>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Product</th>
                    <th scope="col" class="num">In stock</th>
                    <th scope="col" class="num">Reorder level</th>
                    <th scope="col">Level</th>
                    <th scope="col"><span class="visually-hidden">Action</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <?php
                    $stock = (int) $product['stock_qty'];
                    $isLow = $stock <= (int) $product['reorder_level'];
                    ?>
                    <tr>
                        <td class="cell-strong"><?= e($product['name']) ?></td>
                        <td class="num<?= $isLow ? ' cell-danger' : '' ?>"><?= e($stock) ?></td>
                        <td class="num"><?= e($product['reorder_level']) ?></td>
                        <td>
                            <?php if ($stock === 0): ?>
                                <span class="badge badge-danger">Out</span>
                            <?php elseif ($isLow): ?>
                                <span class="badge badge-warning">Low</span>
                            <?php else: ?>
                                <span class="badge badge-success">OK</span>
                            <?php endif; ?>
                        </td>
                        <td class="num">
                            <button
                                type="button"
                                class="btn btn-sm"
                                data-dialog-open="adjust-dialog"
                                data-reset
                                data-dialog-heading="Adjust stock: <?= e($product['name']) ?>"
                                data-fill="<?= e(json_encode(['product_id' => $product['id'], 'current' => $stock])) ?>"
                            >Adjust stock</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card" aria-labelledby="movements-title">
    <div class="card-header">
        <h2 class="card-title" id="movements-title">Stock movements</h2>
        <div class="form-group">
            <label class="visually-hidden" for="movement-filter">Filter by type</label>
            <select class="field" id="movement-filter" data-filter-table="movements-table" data-filter-key="type">
                <option value="">All types</option>
                <?php foreach ($typeLabels as $value => $label): ?>
                    <option value="<?= e($value) ?>"><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="table-wrap">
        <table class="table" id="movements-table">
            <thead>
                <tr>
                    <th scope="col">Time</th>
                    <th scope="col">Product</th>
                    <th scope="col">Type</th>
                    <th scope="col" class="num">Change</th>
                    <th scope="col" class="num">Stock after</th>
                    <th scope="col">Reason or receipt</th>
                    <th scope="col">Supplier</th>
                    <th scope="col">By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movements as $movement): ?>
                    <?php $change = (int) $movement['quantity']; ?>
                    <tr data-search="<?= e($movement['product']) ?>" data-type="<?= e($movement['type']) ?>">
                        <td class="cell-mono"><?= e(date('d M H:i', strtotime((string) $movement['created_at']))) ?></td>
                        <td class="cell-strong"><?= e($movement['product']) ?></td>
                        <td><span class="badge <?= e($typeBadges[$movement['type']] ?? '') ?>"><?= e($typeLabels[$movement['type']] ?? $movement['type']) ?></span></td>
                        <td class="num <?= $change > 0 ? 'cell-positive' : 'cell-danger' ?>"><?= $change > 0 ? '+' : '−' ?><?= e(abs($change)) ?></td>
                        <td class="num"><?= e($movement['stock_after']) ?></td>
                        <td><?= e($movement['reason']) ?></td>
                        <td><?= $movement['supplier'] !== null ? e($movement['supplier']) : '<span class="muted">–</span>' ?></td>
                        <td><?= e($movement['user_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="card-empty" data-empty-for="movements-table"<?= $movements !== [] ? ' hidden' : '' ?>>No movements of this type.</p>
</section>

<?php $returnTo = '/admin/stock'; require __DIR__ . '/partials/adjust_stock_dialog.php'; ?>
