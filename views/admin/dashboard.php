<?php /* Admin dashboard · Owner: Xavier Biko (M1) · Task 1.3 · Prototype: Dashboard */ ?>
<?php
/**
 * Variables supplied by ProductController::dashboard():
 * @var array $summary     Today's totals from ReportService::dailySummary().
 * @var array $lowStock    Active products at or below their reorder level.
 * @var array $recentSales Latest five sales.
 */
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Today, <?= e(date('l j F Y')) ?></p>
    </div>
</div>

<div class="stat-grid">
    <div class="card stat-card stat-card-feature">
        <span class="stat-label">Today's sales</span>
        <span class="stat-value"><?= e(kes($summary['total'])) ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Transactions</span>
        <span class="stat-value"><?= e($summary['count']) ?></span>
    </div>
    <div class="card stat-card">
        <span class="stat-label">Cash · M-Pesa</span>
        <span class="stat-value"><?= e($summary['cash_count']) ?> · <?= e($summary['mpesa_count']) ?></span>
    </div>
    <div class="card stat-card<?= $lowStock !== [] ? ' stat-card-alert' : '' ?>">
        <span class="stat-label">Low-stock items</span>
        <span class="stat-value"><?= e(count($lowStock)) ?></span>
    </div>
</div>

<section class="card" aria-labelledby="low-stock-title">
    <div class="card-header">
        <h2 class="card-title" id="low-stock-title">Low-stock alerts <span class="badge badge-count"><?= e(count($lowStock)) ?></span></h2>
        <a class="btn-link" href="<?= e(url('/admin/stock')) ?>">Open stock</a>
    </div>
    <?php if ($lowStock === []): ?>
        <p class="card-empty">All products are above their reorder level.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col" class="num">In stock</th>
                        <th scope="col" class="num">Reorder level</th>
                        <th scope="col"><span class="visually-hidden">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStock as $product): ?>
                        <tr>
                            <td class="cell-strong"><?= e($product['name']) ?></td>
                            <td class="num cell-danger"><?= e($product['stock_qty']) ?></td>
                            <td class="num"><?= e($product['reorder_level']) ?></td>
                            <td class="num">
                                <button
                                    type="button"
                                    class="btn btn-sm"
                                    data-dialog-open="adjust-dialog"
                                    data-reset
                                    data-dialog-heading="Restock <?= e($product['name']) ?>"
                                    data-fill="<?= e(json_encode(['product_id' => $product['id'], 'current' => $product['stock_qty'], 'type' => 'RESTOCK'])) ?>"
                                >Restock</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="card" aria-labelledby="recent-title">
    <div class="card-header">
        <h2 class="card-title" id="recent-title">Recent sales</h2>
        <a class="btn-link" href="<?= e(url('/admin/reports')) ?>">View all reports</a>
    </div>
    <?php if ($recentSales === []): ?>
        <p class="card-empty">No sales yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Receipt</th>
                        <th scope="col">Time</th>
                        <th scope="col">Cashier</th>
                        <th scope="col">Method</th>
                        <th scope="col" class="num">Total (KES)</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSales as $sale): ?>
                        <tr>
                            <td class="cell-mono"><a href="<?= e(url('/receipts/' . $sale['receipt_no'] . '?copy=1')) ?>"><?= e($sale['receipt_no']) ?></a></td>
                            <td><?= e(date('d M, H:i', strtotime((string) $sale['created_at']))) ?></td>
                            <td><?= e($sale['cashier']) ?></td>
                            <td><?= $sale['method'] === 'MPESA' ? 'M-Pesa' : 'Cash' ?></td>
                            <td class="num cell-strong"><?= e(money($sale['total'])) ?></td>
                            <td>
                                <?php if ($sale['status'] === 'VOID'): ?>
                                    <span class="badge badge-danger">Voided</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php $returnTo = '/admin/dashboard'; require __DIR__ . '/partials/adjust_stock_dialog.php'; ?>
