<?php /* Adjust stock dialog shared by the dashboard, products and stock screens · Owner: Xavier Biko (M1) · Task 1.5 */ ?>
<?php
/**
 * @var string $returnTo  Page to come back to after saving.
 * @var array  $suppliers Active suppliers, offered when the change is a restock.
 */
$suppliers ??= [];
?>
<dialog id="adjust-dialog" class="modal" aria-labelledby="adjust-title">
    <form method="post" action="<?= e(url('/stock/adjust')) ?>" data-stock-preview>
        <?= Csrf::field() ?>
        <input type="hidden" name="return" value="<?= e($returnTo) ?>">
        <input type="hidden" name="product_id" value="">
        <input type="hidden" name="current" value="0">

        <div class="modal-head">
            <h2 class="modal-title" id="adjust-title" data-dialog-heading>Adjust stock</h2>
            <p class="page-subtitle">Currently <strong data-current-stock>0</strong> in stock</p>
        </div>

        <div class="modal-body">
            <div class="form-group">
                <label class="label" for="adjust-type">Type</label>
                <select class="field" id="adjust-type" name="type">
                    <option value="RESTOCK">Restock (add)</option>
                    <option value="DAMAGE">Damage or loss (remove)</option>
                    <option value="CORRECTION">Correction (set exact count)</option>
                </select>
            </div>
            <div class="form-group" data-supplier-field>
                <label class="label" for="adjust-supplier">Supplier</label>
                <select class="field" id="adjust-supplier" name="supplier_id" required>
                    <option value="">Choose the supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= e($supplier['id']) ?>"><?= e($supplier['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($suppliers === []): ?>
                    <p class="field-hint">No active suppliers. <a href="<?= e(url('/admin/suppliers')) ?>">Add one first.</a></p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="label" for="adjust-quantity" data-quantity-label>Quantity to add</label>
                <input class="field" id="adjust-quantity" name="quantity" type="number" min="0" step="1" inputmode="numeric" required>
            </div>
            <div class="form-group">
                <label class="label" for="adjust-reason">Reason (required)</label>
                <input class="field" id="adjust-reason" name="reason" type="text" maxlength="255" placeholder="e.g. Weekly delivery" required>
            </div>
            <p class="preview-line">New stock level: <strong data-new-stock>–</strong></p>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn" data-dialog-close>Cancel</button>
            <button type="submit" class="btn btn-primary">Save change</button>
        </div>
    </form>
</dialog>
