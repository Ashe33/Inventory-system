<?php
/* ============================================================
   STAFF PRODUCTS (MATCHES MANAGER DESIGN EXACTLY)
   ONLY DIFFERENCE: REQUEST SYSTEM INSTEAD OF CRUD
============================================================ */

/* =========================
   HANDLE REQUEST
========================= */
if (isset($_POST['request_product'])) {

    $product_id = (int) $_POST['product_id'];
    $user_id    = (int) $user['id'];
    $qty        = (int) $_POST['quantity'];

    if ($qty <= 0) $qty = 1;

    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {

        if ($qty > $product['quantity']) {
            $qty = $product['quantity'];
        }

        $insert = $conn->prepare("
            INSERT INTO requests (user_id, product_id, quantity, status, created_at)
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        $insert->bind_param("iii", $user_id, $product_id, $qty);
        $insert->execute();

        header("Location: dashboard.php?page=products&success=requested");
        exit();
    }
}

/* =========================
   CATEGORY FILTER (SAME AS MANAGER)
========================= */
$category = $_GET['category'] ?? null;

if ($category) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
}

/* =========================
   GET CATEGORIES (SAME AS MANAGER)
========================= */
$categories = $conn->query("
    SELECT DISTINCT category 
    FROM products 
    WHERE category IS NOT NULL AND category != ''
    ORDER BY category ASC
");
?>

<style>
/* =========================
   KEEP EXACT MANAGER DESIGN
========================= */

.page-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 24px;
}
.page-title span { color: #22c55e; }

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 500;
    margin-bottom: 18px;
}

.alert-success {
    background: rgba(34,197,94,0.1);
    border: 1px solid rgba(34,197,94,0.2);
    color: #22c55e;
}

/* CATEGORY BAR (UNCHANGED FROM MANAGER) */
.category-bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.cat-btn {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid rgba(255,255,255,0.06);
    color: #94a3b8;
    background: #1e293b;
}

.cat-btn:hover {
    border-color: #22c55e;
    color: #22c55e;
}

.cat-active {
    background: #22c55e;
    color: #000;
    border: none;
}

/* TABLE (EXACT SAME STYLE) */
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

.total-badge {
    background: rgba(34,197,94,0.1);
    color: #22c55e;
    border: 1px solid rgba(34,197,94,0.2);
    padding: 3px 12px;
    border-radius: 20px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}

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

/* STOCK BADGE */
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
}

/* INPUT */
.qty-input {
    width: 60px;
    padding: 5px;
    border-radius: 6px;
    background: #0f172a;
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
}

/* BUTTON */
.btn {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 700;
    font-size: .75rem;
    border: none;
    cursor: pointer;
}

.btn-green {
    background: #22c55e;
    color: #000;
}

.btn-disabled {
    background: rgba(107,114,128,0.2);
    color: #6b7280;
}
</style>

<div class="page-title">📦 <span>Products</span></div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✅ Request sent successfully
    </div>
<?php endif; ?>

<!-- CATEGORY FILTER (EXACT SAME AS MANAGER) -->
<div class="category-bar">
    <a href="dashboard.php?page=products"
       class="cat-btn <?= !$category ? 'cat-active' : '' ?>">
        All
    </a>

    <?php while ($c = $categories->fetch_assoc()): ?>
        <a href="dashboard.php?page=products&category=<?= urlencode($c['category']) ?>"
           class="cat-btn <?= ($category == $c['category']) ? 'cat-active' : '' ?>">
            <?= htmlspecialchars($c['category']) ?>
        </a>
    <?php endwhile; ?>
</div>

<!-- TABLE -->
<div class="table-card">
    <div class="table-top">
        <span><?= $category ? htmlspecialchars($category) : "All Products" ?></span>
        <span class="total-badge"><?= $products->num_rows ?> items</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Qty</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php if ($products->num_rows > 0): ?>
            <?php $i = 1; while ($p = $products->fetch_assoc()): ?>
            <tr>
                <td style="color:#94a3b8"><?= $i++ ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td style="color:#94a3b8"><?= $p['category'] ?? 'Uncategorized' ?></td>

                <td>
                    <span class="qty-badge <?= $p['quantity'] <= 5 ? 'qty-low' : '' ?>">
                        <?= $p['quantity'] ?>
                    </span>
                </td>

                <td>
                    <form method="POST" style="display:flex; gap:6px;">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">

                        <input class="qty-input"
                               type="number"
                               name="quantity"
                               min="1"
                               max="<?= $p['quantity'] ?>"
                               value="1">
                </td>

                <td>
                    <?php if ($p['quantity'] > 0): ?>
                        <button class="btn btn-green" name="request_product">
                            Request
                        </button>
                    </form>
                    <?php else: ?>
                        <span class="btn-disabled">Out of Stock</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center; color:#94a3b8; padding:40px;">
                    No products found
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>