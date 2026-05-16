<?php
// Inventory Overview (Analytics replacement)

/* =========================
   TOTAL PRODUCTS
========================= */
$total_products = $conn->query("
    SELECT COUNT(*) as count 
    FROM products
")->fetch_assoc()['count'];

/* =========================
   TOTAL STOCK
========================= */
$total_stock = $conn->query("
    SELECT SUM(quantity) as total 
    FROM products
")->fetch_assoc()['total'] ?? 0;

/* =========================
   LOW STOCK COUNT
========================= */
$low_stock = $conn->query("
    SELECT COUNT(*) as count 
    FROM products 
    WHERE quantity <= 5
")->fetch_assoc()['count'];

/* =========================
   TOTAL CATEGORIES
========================= */
$total_categories = $conn->query("
    SELECT COUNT(DISTINCT category) as count 
    FROM products
")->fetch_assoc()['count'];

/* =========================
   PENDING REQUESTS
========================= */
$pending_requests = $conn->query("
    SELECT COUNT(*) as count 
    FROM requests 
    WHERE status = 'pending'
")->fetch_assoc()['count'];

/* =========================
   APPROVED REQUESTS
========================= */
$approved_requests = $conn->query("
    SELECT COUNT(*) as count 
    FROM requests 
    WHERE status = 'approved'
")->fetch_assoc()['count'];

/* =========================
   RECENT LOW STOCK ITEMS
========================= */
$low_items = $conn->query("
    SELECT name, quantity 
    FROM products 
    WHERE quantity <= 5 
    ORDER BY quantity ASC 
    LIMIT 5
");
?>

<style>
.page-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 24px;
}

.page-title span {
    color: #22c55e;
}

.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.overview-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 14px;
    padding: 22px;
    transition: .2s;
}

.overview-card:hover {
    border-color: rgba(34,197,94,0.3);
    transform: translateY(-2px);
}

.overview-value {
    font-size: 2rem;
    font-weight: 800;
    color: #22c55e;
    font-family: 'Syne', sans-serif;
}

.overview-label {
    font-size: .75rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-top: 6px;
}

.alert-card {
    background: #1e293b;
    border: 1px solid rgba(248,113,113,0.2);
    border-radius: 14px;
    padding: 22px;
}

.section-title {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 16px;
    color: #f1f5f9;
}

.low-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    font-size: .88rem;
}

.low-item:last-child {
    border-bottom: none;
}

.badge-low {
    background: rgba(248,113,113,0.1);
    color: #f87171;
    border: 1px solid rgba(248,113,113,0.2);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 700;
}
</style>

<div class="page-title">📊 <span>Inventory Overview</span></div>

<!-- OVERVIEW STATS -->
<div class="overview-grid">

    <div class="overview-card">
        <div class="overview-value"><?= $total_products ?></div>
        <div class="overview-label">Total Products</div>
    </div>

    <div class="overview-card">
        <div class="overview-value"><?= $total_stock ?></div>
        <div class="overview-label">Total Stock Units</div>
    </div>

    <div class="overview-card">
        <div class="overview-value"><?= $low_stock ?></div>
        <div class="overview-label">Low Stock Items</div>
    </div>

    <div class="overview-card">
        <div class="overview-value"><?= $total_categories ?></div>
        <div class="overview-label">Categories</div>
    </div>

    <div class="overview-card">
        <div class="overview-value"><?= $pending_requests ?></div>
        <div class="overview-label">Pending Requests</div>
    </div>

    <div class="overview-card">
        <div class="overview-value"><?= $approved_requests ?></div>
        <div class="overview-label">Approved Requests</div>
    </div>

</div>

<!-- LOW STOCK ALERTS -->
<div class="alert-card">

    <div class="section-title">⚠️ Low Stock Alerts</div>

    <?php if ($low_items && $low_items->num_rows > 0): ?>

        <?php while ($item = $low_items->fetch_assoc()): ?>

            <div class="low-item">
                <span><?= htmlspecialchars($item['name']) ?></span>

                <span class="badge-low">
                    <?= $item['quantity'] ?> left
                </span>
            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div style="color:#94a3b8;font-size:.88rem;">
            No low stock items 🎉
        </div>

    <?php endif; ?>

</div>