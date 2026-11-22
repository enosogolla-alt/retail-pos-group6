<?php /* Cashier's own sales and shift totals · Owner: Winston Enos (M4) · Task 4.9 */ ?>
<?php
/**
 * Variables supplied by SaleController::history():
 * @var string $date    Day shown (Y-m-d).
 * @var bool   $isToday True when the day shown is today.
 * @var array  $sales   The signed-in cashier's sales on that day, voided ones included.
 * @var array  $summary Completed-sale totals for the cashier, split by payment method.
 */
?>
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">

<div class="history">
    <div class="page-header">
        <div>
            <h1 class="page-title">My sales</h1>
            <p class="page-subtitle">
                <?= $isToday ? 'Today, ' : '' ?><?= e(date('l j F Y', strtotime($date))) ?>. Voided sales are not counted in the totals.
            </p>
        </div>
        <div class="page-actions">
            <form method="get" action="<?= e(url('/my-sales')) ?>" class="filter-bar">
                <label class="visually-hidden" for="history-date">Day</label>
                <input class="field" id="history-date" type="date" name="date" value="<?= e($date) ?>" max="<?= e(date('Y-m-d')) ?>">
                <button type="submit" class="btn">Show</button>
            </form>
            <?php if (!$isToday): ?>
                <a class="btn" href="<?= e(url('/my-sales')) ?>">Today</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-grid">
        <div class="card stat-card stat-card-feature">
            <span class="stat-label">Total sold</span>
            <span class="stat-value"><?= e(kes($summary['total'])) ?></span>
        </div>
        <div class="card stat-card">
            <span class="stat-label">Sales</span>
            <span class="stat-value"><?= e($summary['count']) ?></span>
        </div>
        <div class="card stat-card">
            <span class="stat-label">Cash to hand over (<?= e($summary['cash_count']) ?>)</span>
            <span class="stat-value"><?= e(kes($summary['cash_total'])) ?></span>
        </div>
        <div class="card stat-card">
            <span class="stat-label">Received by M-Pesa (<?= e($summary['mpesa_count']) ?>)</span>
            <span class="stat-value"><?= e(kes($summary['mpesa_total'])) ?></span>
        </div>
    </div>

    <section class="card" aria-labelledby="history-title">
        <div class="card-header">
            <h2 class="card-title" id="history-title">Sales</h2>
            <?php if ((int) $summary['voided'] > 0): ?>
                <span class="muted"><?= e($summary['voided']) ?> voided by the Admin</span>
            <?php endif; ?>
        </div>
        <?php if ($sales === []): ?>
            <p class="card-empty">
                <?= $isToday ? 'No sales yet today. Completed sales appear here straight away.' : 'You made no sales on this day.' ?>
            </p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Receipt</th>
                            <th scope="col">Time</th>
                            <th scope="col" class="num">Items</th>
                            <th scope="col">Method</th>
                            <th scope="col" class="num">Total (KES)</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td class="cell-mono"><?= e($sale['receipt_no']) ?></td>
                                <td class="cell-mono"><?= e(date('H:i', strtotime((string) $sale['created_at']))) ?></td>
                                <td class="num"><?= e($sale['items']) ?></td>
                                <td><?= $sale['method'] === 'MPESA' ? 'M-Pesa' : 'Cash' ?></td>
                                <td class="num cell-strong"><?= e(money($sale['total'])) ?></td>
                                <td>
                                    <?php if ($sale['status'] === 'VOID'): ?>
                                        <span class="badge badge-danger" title="<?= e($sale['void_reason']) ?>">Voided</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-actions">
                                    <a class="btn-link" href="<?= e(url('/receipts/' . $sale['receipt_no'] . '?copy=1')) ?>">Reprint</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
