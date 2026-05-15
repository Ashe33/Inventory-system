<?php
// Fetch all products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");

// Fetch product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id      = (int) $_GET['edit'];
    $edit_result  = $conn->query("SELECT * FROM products WHERE id=$edit_id");
    $edit_product = $edit_result->fetch_assoc();
}
?>

<style>
.page-title { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 24px; }
.page-title span { color: #22c55e; }

.alert { padding: 12px 16px; border-radius: 8px; font-size: .85rem; font-weight: 500; margin-bottom: 18px; }
.alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #22c55e; }
.alert-error   { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.2); color: #f87171; }

.form-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 24px;
}

.form-card-title {
    font-size: .68rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: #22c55e; margin-bottom: 14px;
}

.form-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
.form-group { display: flex; flex-direction: column; gap: 6px; }

.form-label {
    font-size: .65rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: #94a3b8;
}

.form-input {
    padding: 10px 14px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.06);
    background: #0f172a; color: #f1f5f9;
    font-family: 'DM Sans', sans-serif; font-size: .88rem;
    outline: none; transition: .2s;
}

.form-input:focus { border-color: #22c55e; }

.btn {
    padding: 10px 18px; border-radius: 8px; border: none;
    font-weight: 700; font-size: .82rem; cursor: pointer;
    transition: .2s; text-decoration: none;
    display: inline-block; text-align: center;
}

.btn-green { background: #22c55e; color: #000; }
.btn-green:hover { background: #16a34a; }
.btn-red { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
.btn-red:hover { background: rgba(248,113,113,0.2); }
.btn-blue { background: rgba(96,165,250,0.1); color: #60a5fa; border: 1px solid rgba(96,165,250,0.2); }
.btn-blue:hover { background: rgba(96,165,250,0.2); }
.btn-yellow { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
.btn-yellow:hover { background: rgba(251,191,36,0.2); }

.table-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; overflow: hidden; }

.table-top {
    padding: 16px 22px; border-bottom: 1px solid rgba(255,255,255,0.06);
    font-size: .68rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: #94a3b8;
}

table { width: 100%; border-collapse: collapse; }

th {
    padding: 11px 20px; text-align: left; font-size: .65rem;
    font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.06);
}

td {
    padding: 13px 20px; font-size: .88rem;
    border-bottom: 1px solid rgba(255,255,255,0.06); color: #f1f5f9;
}

tr:last-child td { border-bottom: none; }
tr:hover td { background: rgba(255,255,255,0.02); }

.qty-badge {
    background: rgba(34,197,94,0.1); color: #22c55e;
    border: 1px solid rgba(34,197,94,0.2);
    padding: 3px 12px; border-radius: 20px; font-size: .72rem; font-weight: 700;
}

.qty-low { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }

.actions-col { display: flex; gap: 8px; }

.empty-row td { text-align: center; color: #94a3b8; padding: 40px; font-size: .85rem; }

.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal { background: #1e293b; border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 30px; width: 100%; max-width: 440px; }
.modal-title { font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 800; margin-bottom: 20px; color: #f1f5f9; }
.modal-title span { color: #22c55e; }
.modal-fields { display: flex; flex-direction: column; gap: 14px; margin-bottom: 22px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
</style>

<div class="page-title">📦 <span>Products</span></div>

<!-- ALERTS -->
<?php if (isset($_GET['success'])): ?>
    <?php
    $msg = match($_GET['success']) {
        'added'   => '✅ Product added successfully.',
        'updated' => '✅ Product updated successfully.',
        'deleted' => '✅ Product deleted successfully.',
        'stocked' => '✅ Stock updated successfully.',
        default   => '✅ Done.'
    };
    ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">❌ Invalid input. Please try again.</div>
<?php endif; ?>

<!-- ADD FORM -->
<div class="form-card">
    <div class="form-card-title">Add New Product</div>
    <form method="POST" action="process/product_add.php">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Ballpen" required>
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-input" placeholder="e.g. Supplies">
            </div>
            <div class="form-group">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-input" placeholder="0" min="0" required>
            </div>
            <button type="submit" class="btn btn-green">+ Add</button>
        </div>
    </form>
</div>

<!-- TABLE -->
<div class="table-card">
    <div class="table-top">All Products</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Added</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($products && $products->num_rows > 0): ?>
            <?php $i = 1; while ($p = $products->fetch_assoc()): ?>
            <tr>
                <td style="color:#94a3b8"><?= $i++ ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td style="color:#94a3b8"><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                <td>
                    <span class="qty-badge <?= $p['quantity'] <= 5 ? 'qty-low' : '' ?>">
                        <?= $p['quantity'] ?>
                    </span>
                </td>
                <td style="color:#94a3b8; font-size:.78rem"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                <td>
                    <div class="actions-col">
                        <a href="?page=products&edit=<?= $p['id'] ?>" class="btn btn-blue" style="font-size:.75rem; padding:6px 12px">✏️ Edit</a>
                        <a href="?page=products&restock=<?= $p['id'] ?>" class="btn btn-yellow" style="font-size:.75rem; padding:6px 12px">📦 Restock</a>
                        <a href="process/product_delete.php?id=<?= $p['id'] ?>" class="btn btn-red" style="font-size:.75rem; padding:6px 12px" onclick="return confirm('Delete this product?')">🗑️ Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr class="empty-row"><td colspan="6">No products yet. Add one above.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay <?= $edit_product ? 'active' : '' ?>">
    <div class="modal">
        <div class="modal-title">✏️ Edit <span>Product</span></div>
        <?php if ($edit_product): ?>
        <form method="POST" action="process/product_edit.php">
            <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
            <div class="modal-fields">
                <div class="form-group">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($edit_product['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-input" value="<?= htmlspecialchars($edit_product['category'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-input" value="<?= $edit_product['quantity'] ?>" min="0" required>
                </div>
            </div>
            <div class="modal-actions">
                <a href="?page=products" class="btn btn-red">Cancel</a>
                <button type="submit" class="btn btn-green">Save Changes</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- RESTOCK MODAL -->
<?php
$restock_product = null;
if (isset($_GET['restock'])) {
    $restock_id     = (int) $_GET['restock'];
    $restock_result = $conn->query("SELECT * FROM products WHERE id=$restock_id");
    $restock_product = $restock_result->fetch_assoc();
}
?>
<div class="modal-overlay <?= $restock_product ? 'active' : '' ?>">
    <div class="modal">
        <div class="modal-title">📦 <span>Restock Product</span></div>
        <?php if ($restock_product): ?>
        <form method="POST" action="process/product_restock.php">
            <input type="hidden" name="id" value="<?= $restock_product['id'] ?>">
            <div class="modal-fields">
                <div class="form-group">
                    <label class="form-label">Product</label>
                    <input type="text" class="form-input" value="<?= htmlspecialchars($restock_product['name']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Current Stock</label>
                    <input type="text" class="form-input" value="<?= $restock_product['quantity'] ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Add Quantity</label>
                    <input type="number" name="add_quantity" class="form-input" placeholder="e.g. 50" min="1" required>
                </div>
            </div>
            <div class="modal-actions">
                <a href="?page=products" class="btn btn-red">Cancel</a>
                <button type="submit" class="btn btn-green">+ Add Stock</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
