<?php
/* =========================
   CATEGORY FILTER SYSTEM
========================= */
$category = $_GET['category'] ?? null;

/* =========================
   FETCH PRODUCTS (FILTERED OR ALL)
========================= */
if ($category) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
}

/* =========================
   FETCH DISTINCT CATEGORIES
========================= */
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");

/* =========================
   EDIT PRODUCT (FIXED)
========================= */
$edit_product = null;

if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
}
?>

<style>
/* ===== YOUR ORIGINAL DESIGN (UNCHANGED) ===== */

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
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #22c55e;
    margin-bottom: 14px;
}

.form-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
.form-group { display: flex; flex-direction: column; gap: 6px; }

.form-label {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #94a3b8;
}

.form-input {
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.06);
    background: #0f172a;
    color: #f1f5f9;
    font-family: 'DM Sans', sans-serif;
    font-size: .88rem;
}

.form-input:focus { border-color: #22c55e; }

.btn {
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    font-size: .82rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-green { background: #22c55e; color: #000; }
.btn-blue { background: rgba(96,165,250,0.1); color: #60a5fa; border: 1px solid rgba(96,165,250,0.2); }
.btn-red { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }

.table-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
}

.table-top {
    padding: 16px 22px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #94a3b8;
    display: flex;
    justify-content: space-between;
}

table { width: 100%; border-collapse: collapse; }

th {
    padding: 11px 20px;
    text-align: left;
    font-size: .65rem;
    font-weight: 700;
    color: #94a3b8;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

td {
    padding: 13px 20px;
    font-size: .88rem;
    color: #f1f5f9;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.qty-badge {
    background: rgba(34,197,94,0.1);
    color: #22c55e;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 700;
}

.qty-low {
    background: rgba(248,113,113,0.1);
    color: #f87171;
    border: 1px solid rgba(248,113,113,0.2);
}

.actions-col { display:flex; gap:8px; }

.empty-row td {
    text-align:center;
    color:#94a3b8;
    padding:40px;
}
</style>

<div class="page-title">📦 <span>Products</span></div>

<!-- =========================
   EDIT FORM (ADDED FIX)
========================= -->
<?php if ($edit_product): ?>
<div class="form-card">
    <div class="form-card-title">Edit Product</div>

    <form method="POST" action="process/product_edit.php">
        <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-input"
                       value="<?= htmlspecialchars($edit_product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-input"
                       value="<?= htmlspecialchars($edit_product['category']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-input"
                       value="<?= $edit_product['quantity'] ?>" min="0" required>
            </div>

            <button class="btn btn-green">Update</button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ADD PRODUCT FORM -->
<div class="form-card">
    <div class="form-card-title">Add New Product</div>

    <form method="POST" action="process/product_add.php">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-input" min="0" required>
            </div>

            <button class="btn btn-green">+ Add</button>
        </div>
    </form>
</div>

<!-- CATEGORY BUTTONS -->
<div class="table-card" style="margin-bottom:20px;">
    <div class="table-top">📁 Categories</div>

    <div style="padding:15px; display:flex; gap:10px; flex-wrap:wrap;">
        <a href="dashboard.php?page=products" class="btn btn-blue">All</a>

        <?php while ($c = $categories->fetch_assoc()): ?>
            <?php if (!empty($c['category'])): ?>
                <a href="dashboard.php?page=products&category=<?= urlencode($c['category']) ?>"
                   class="btn btn-green">
                    📁 <?= htmlspecialchars($c['category']) ?>
                </a>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</div>

<!-- TABLE -->
<div class="table-card">
    <div class="table-top">
        <?= $category ? "📁 " . htmlspecialchars($category) : "📦 All Products" ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php $i = 1; while ($p = $products->fetch_assoc()): ?>
        <tr>
            <td style="color:#94a3b8"><?= $i++ ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?></td>

            <td>
                <span class="qty-badge <?= $p['quantity'] <= 5 ? 'qty-low' : '' ?>">
                    <?= $p['quantity'] ?>
                </span>
            </td>

            <td>
                <div class="actions-col">
                    <a href="dashboard.php?page=products&edit=<?= $p['id'] ?>"
                       class="btn btn-blue">Edit</a>

                    <a href="process/product_delete.php?id=<?= $p['id'] ?>"
                       class="btn btn-red">Delete</a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>