<?php /* Product management · Owner: Xavier Biko (M1) · Task 1.4 · Prototype: Products */ ?>
<?php
/**
 * Variables supplied by ProductController::index():
 * @var array $products   Every product with its category name.
 * @var array $categories Every category.
 */
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Products</h1>
        <p class="page-subtitle"><?= e(count($products)) ?> products · deactivated products are kept for past sales</p>
    </div>
    <div class="page-actions">
        <button
            type="button"
            class="btn btn-primary"
            data-dialog-open="product-dialog"
            data-reset
            data-dialog-heading="Add product"
            data-fill="<?= e(json_encode(['action' => url('/admin/products')])) ?>"
        >Add product</button>
    </div>
</div>

<div class="filter-bar">
    <div class="form-group filter-search">
        <label class="label" for="product-filter">Search</label>
        <input class="field" id="product-filter" type="search" placeholder="Name or barcode" data-filter-text="products-table">
    </div>
    <div class="form-group">
        <label class="label" for="category-filter">Category</label>
        <select class="field" id="category-filter" data-filter-table="products-table" data-filter-key="category">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['name']) ?>"><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label class="label" for="status-filter">Status</label>
        <select class="field" id="status-filter" data-filter-table="products-table" data-filter-key="status">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>

<section class="card">
    <div class="table-wrap">
        <table class="table" id="products-table">
            <thead>
                <tr>
                    <th scope="col">Barcode</th>
                    <th scope="col">Name</th>
                    <th scope="col">Category</th>
                    <th scope="col" class="num">Price (KES)</th>
                    <th scope="col" class="num">Stock</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <?php
                    $isActive = (int) $product['is_active'] === 1;
                    $isLow = $isActive && (int) $product['stock_qty'] <= (int) $product['reorder_level'];
                    ?>
                    <tr
                        data-search="<?= e($product['name'] . ' ' . $product['barcode']) ?>"
                        data-category="<?= e($product['category']) ?>"
                        data-status="<?= $isActive ? 'active' : 'inactive' ?>"
                    >
                        <td class="cell-mono"><?= e($product['barcode']) ?></td>
                        <td class="cell-strong"><?= e($product['name']) ?></td>
                        <td><?= e($product['category']) ?></td>
                        <td class="num"><?= e(money($product['selling_price'])) ?></td>
                        <td class="num<?= $isLow ? ' cell-danger' : '' ?>"><?= e($product['stock_qty']) ?></td>
                        <td>
                            <span class="badge <?= $isActive ? 'badge-success' : '' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                        </td>
                        <td class="cell-actions">
                            <button
                                type="button"
                                class="btn-link"
                                data-dialog-open="product-dialog"
                                data-dialog-heading="Edit <?= e($product['name']) ?>"
                                data-fill="<?= e(json_encode([
                                    'action' => url('/admin/products/' . $product['id']),
                                    'barcode' => $product['barcode'],
                                    'name' => $product['name'],
                                    'category_id' => $product['category_id'],
                                    'selling_price' => $product['selling_price'],
                                    'cost_price' => $product['cost_price'],
                                    'reorder_level' => $product['reorder_level'],
                                ])) ?>"
                            >Edit</button>
                            <?php if ($isActive): ?>
                                <button
                                    type="button"
                                    class="btn-link"
                                    data-dialog-open="adjust-dialog"
                                    data-reset
                                    data-dialog-heading="Adjust stock: <?= e($product['name']) ?>"
                                    data-fill="<?= e(json_encode(['product_id' => $product['id'], 'current' => $product['stock_qty']])) ?>"
                                >Adjust</button>
                            <?php endif; ?>
                            <form
                                method="post"
                                action="<?= e(url('/admin/products/' . $product['id'] . '/toggle')) ?>"
                                data-confirm="<?= e(($isActive ? 'Deactivate ' : 'Activate ') . $product['name'] . '?') ?>"
                            >
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn-link link-muted"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="card-empty" data-empty-for="products-table" hidden>No products match these filters.</p>
</section>

<dialog id="product-dialog" class="modal modal-wide" aria-labelledby="product-dialog-title">
    <form method="post" action="<?= e(url('/admin/products')) ?>">
        <?= Csrf::field() ?>
        <div class="modal-head">
            <h2 class="modal-title" id="product-dialog-title" data-dialog-heading>Add product</h2>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="label" for="product-barcode">Barcode</label>
                    <input class="field field-code" id="product-barcode" name="barcode" inputmode="numeric" pattern="\d{8,13}" maxlength="13" required>
                </div>
                <div class="form-group">
                    <label class="label" for="product-name">Name</label>
                    <input class="field" id="product-name" name="name" maxlength="120" required>
                </div>
                <div class="form-group">
                    <label class="label" for="product-category">Category</label>
                    <select class="field" id="product-category" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['id']) ?>"><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="label" for="product-price">Selling price (KES)</label>
                    <input class="field" id="product-price" name="selling_price" type="number" min="0.01" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="label" for="product-cost">Cost price (KES)</label>
                    <input class="field" id="product-cost" name="cost_price" type="number" min="0" step="0.01" value="0">
                </div>
                <div class="form-group">
                    <label class="label" for="product-reorder">Reorder level</label>
                    <input class="field" id="product-reorder" name="reorder_level" type="number" min="0" step="1" value="5" required>
                </div>
            </div>
            <p class="muted">Stock is changed only with Adjust stock. A price change is saved in the price history.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn" data-dialog-close>Cancel</button>
            <button type="submit" class="btn btn-primary">Save product</button>
        </div>
    </form>
</dialog>

<?php $returnTo = '/admin/products'; require __DIR__ . '/partials/adjust_stock_dialog.php'; ?>
