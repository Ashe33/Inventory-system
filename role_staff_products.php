<?php
/* ============================================================
   STAFF PRODUCTS + REQUEST SYSTEM
   - Staff can request directly here
   - My Requests page will show requested items
============================================================ */

/* =========================
   HANDLE REQUEST
========================= */
if (isset($_POST['request_product'])) {

    $product_id = (int) $_POST['product_id'];
    $user_id    = (int) $user['id'];
    $qty        = (int) $_POST['quantity'];

    if ($qty <= 0) {
        $qty = 1;
    }

    // GET PRODUCT STOCK
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $product = $stmt->get_result()->fetch_assoc();

    if ($product && $product['quantity'] > 0) {

        // prevent exceeding stock
        if ($qty > $product['quantity']) {
            $qty = $product['quantity'];
        }

        // INSERT REQUEST
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
   CATEGORY FILTER
========================= */
$category = $_GET['category'] ?? null;

/* =========================
   FETCH PRODUCTS
========================= */
if ($category) {

    $stmt = $conn->prepare("
        SELECT * FROM products
        WHERE category = ?
        ORDER BY name ASC
    ");

    $stmt->bind_param("s", $category);
    $stmt->execute();

    $products = $stmt->get_result();

} else {

    $products = $conn->query("
        SELECT * FROM products
        ORDER BY name ASC
    ");
}

/* =========================
   FETCH CATEGORIES
========================= */
$categories = $conn->query("
    SELECT DISTINCT category
    FROM products
    WHERE category IS NOT NULL
    AND category != ''
    ORDER BY category ASC
");
?>

<style>
.page-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 8px;
}

.page-title span {
    color: #22c55e;
}

.view-notice {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(96,165,250,0.1);
    border: 1px solid rgba(96,165,250,0.2);
    color: #60a5fa;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 20px;
    margin-bottom: 20px;
}

/* CATEGORY */
.table-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 18px;
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
    font-size: .72rem;
    font-weight: 700;
}

/* BUTTONS */
.btn {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 700;
    font-size: .75rem;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    border: none;
    cursor: pointer;
}

.btn-green {
    background: #22c55e;
    color: #000;
}

.btn-green:hover {
    background: #16a34a;
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

tr:hover td {
    background: rgba(255,255,255,0.02);
}

/* STOCK BADGES */
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

.qty-zero {
    background: rgba(107,114,128,0.1);
    color: #6b7280;
    border: 1px solid rgba(107,114,128,0.2);
}

/* REQUEST INPUT */
.qty-input {
    width: 65px;
    padding: 6px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.08);
    background: #0f172a;
    color: #fff;
    outline: none;
}

.qty-input:focus {
    border-color: #22c55e;
}

.empty-row td {
    text-align: center;
    color: #94a3b8;
    padding: 40px;
}
</style>

<div class="page-title">📦 <span>Products</span></div>

<div class="view-notice">
    👁️ Browse Products & Request Items
</div>

<!-- SUCCESS -->
<?php if (isset($_GET['success']) && $_GET['success'] === 'requested'): ?>
    <div style="
        margin-bottom:16px;
        background:rgba(34,197,94,0.1);
        border:1px solid rgba(34,197,94,0.2);
        color:#22c55e;
        padding:12px 16px;
        border-radius:10px;
        font-size:.85rem;
        font-weight:700;
    ">
        ✅ Product requested successfully.
    </div>
<?php endif; ?>

<!-- CATEGORY FILTER -->
<div class="table-card">

    <div class="table-top">
        📁 Categories
    </div>

    <div style="padding:12px; display:flex; gap:10px; flex-wrap:wrap;">

        <a href="dashboard.php?page=products"
           class="btn btn-green">
            📁 All
        </a>

        <?php if ($categories && $categories->num_rows > 0): ?>

            <?php while ($c = $categories->fetch_assoc()): ?>

                <?php if (!empty($c['category'])): ?>

                    <a href="dashboard.php?page=products&category=<?= urlencode($c['category']) ?>"
                       class="btn btn-green">

                        📁 <?= htmlspecialchars($c['category']) ?>

                    </a>

                <?php endif; ?>

            <?php endwhile; ?>

        <?php endif; ?>

    </div>
</div>

<!-- PRODUCTS -->
<div class="table-card">

    <div class="table-top">

        <span>
            <?= $category ? "📁 " . htmlspecialchars($category) : "📦 All Products" ?>
        </span>

        <span class="total-badge">
            <?= $products ? $products->num_rows : 0 ?> items
        </span>

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

        <?php if ($products && $products->num_rows > 0): ?>

            <?php $i = 1; while ($p = $products->fetch_assoc()): ?>

            <tr>

                <td style="color:#94a3b8">
                    <?= $i++ ?>
                </td>

                <td>
                    <?= htmlspecialchars($p['name']) ?>
                </td>

                <td style="color:#94a3b8">
                    <?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?>
                </td>

                <td>

                    <?php if ($p['quantity'] == 0): ?>

                        <span class="qty-badge qty-zero">
                            Out of Stock
                        </span>

                    <?php elseif ($p['quantity'] <= 5): ?>

                        <span class="qty-badge qty-low">
                            <?= $p['quantity'] ?> Low
                        </span>

                    <?php else: ?>

                        <span class="qty-badge">
                            <?= $p['quantity'] ?>
                        </span>

                    <?php endif; ?>

                </td>

                <td>

                    <?php if ($p['quantity'] > 0): ?>

                    <form method="POST" style="display:flex; gap:8px;">

                        <input type="hidden"
                               name="product_id"
                               value="<?= $p['id'] ?>">

                        <input type="number"
                               name="quantity"
                               class="qty-input"
                               min="1"
                               max="<?= $p['quantity'] ?>"
                               value="1">

                </td>

                <td>

                        <button type="submit"
                                name="request_product"
                                class="btn btn-green">

                            📝 Request

                        </button>

                    </form>

                    <?php else: ?>

                        <span style="color:#6b7280;">
                            Unavailable
                        </span>

                    <?php endif; ?>

                </td>

            </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr class="empty-row">
                <td colspan="6">
                    No products found in this category.
                </td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>