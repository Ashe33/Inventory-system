<?php
/* =========================
   CATEGORY FILTER SYSTEM
========================= */
$category = $_GET['category'] ?? null;

/* =========================
   FETCH PRODUCTS
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
   FETCH CATEGORIES
========================= */
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
?>

<style>
.page-title { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 24px; }
.page-title span { color: #22c55e; }

.table-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
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

.btn {
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 700;
    font-size: .75rem;
    text-decoration: none;
    display: inline-block;
}

.btn-blue { background: rgba(96,165,250,0.1); color: #60a5fa; border: 1px solid rgba(96,165,250,0.2); }
.btn-green { background: #22c55e; color: #000; }

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
}

.actions-col { display:flex; gap:8px; }

.empty-row td {
    text-align:center;
    color:#94a3b8;
    padding:40px;
}
</style>

<div class="page-title">📦 <span>Products</span> (Admin View Only)</div>

<!-- CATEGORY FILTER -->
<div class="table-card">
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
        </tr>
        </thead>

        <tbody>
        <?php if ($products && $products->num_rows > 0): ?>
            <?php $i = 1; while ($p = $products->fetch_assoc()): ?>
            <tr>
                <td style="color:#94a3b8"><?= $i++ ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td style="color:#94a3b8"><?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?></td>

                <td>
                    <span class="qty-badge <?= $p['quantity'] <= 5 ? 'qty-low' : '' ?>">
                        <?= $p['quantity'] ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr class="empty-row">
                <td colspan="4">No products found</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>