<?php
// Stats based on role
$total_products   = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_requests   = $conn->query("SELECT COUNT(*) as count FROM requests")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status='pending'")->fetch_assoc()['count'];

if ($role === 'admin') {
    $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
}

if ($role === 'staff') {
    $uid = $user['id'];
    $my_total    = $conn->query("SELECT COUNT(*) as count FROM requests WHERE user_id=$uid")->fetch_assoc()['count'];
    $my_pending  = $conn->query("SELECT COUNT(*) as count FROM requests WHERE user_id=$uid AND status='pending'")->fetch_assoc()['count'];
    $my_approved = $conn->query("SELECT COUNT(*) as count FROM requests WHERE user_id=$uid AND status='approved'")->fetch_assoc()['count'];
}

/* =========================
   LOW STOCK ALARM (<= 10)
========================= */
$low_stock_items = $conn->query("
    SELECT name, quantity 
    FROM products 
    WHERE quantity <= 10
    ORDER BY quantity ASC
");
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
    cursor: pointer;
    transition: 0.2s;
}

.stat-card:hover {
    transform: translateY(-3px);
    border-color: var(--green);
}

.stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--green);
}

.stat-label {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--sub);
}

/* KEEP REST SAME */
.recent-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.recent-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px;
}

.recent-card-title {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--sub);
    margin-bottom: 16px;
}

.recent-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: .85rem;
}

.recent-item:last-child {
    border-bottom: none;
}

.empty {
    color: var(--sub);
    font-size: .82rem;
    text-align: center;
    padding: 20px 0;
}

.badge {
    font-size: .65rem;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
}

.badge-pending { background: rgba(251,191,36,0.1); color: #fbbf24; }
.badge-approved { background: rgba(34,197,94,0.1); color: var(--green); }
.badge-declined { background: rgba(248,113,113,0.1); color: #f87171; }

/* Alarm */
.alarm-card {
    margin-top: 20px;
    border: 1px solid rgba(248,113,113,0.3);
}

.alarm-title { color: #f87171; font-weight: 700; }

.alarm-glow { animation: pulse 1.5s infinite; }

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(248,113,113,0.4); }
    70% { box-shadow: 0 0 0 10px rgba(248,113,113,0); }
    100% { box-shadow: 0 0 0 0 rgba(248,113,113,0); }
}
</style>

<!-- STATS -->
<div class="stats-grid">

<?php if ($role === 'admin'): ?>

<a href="dashboard.php?page=users" class="stat-card">
    <div class="stat-value"><?= $total_users ?></div>
    <div class="stat-label">Total Users</div>
</a>

<a href="dashboard.php?page=products" class="stat-card">
    <div class="stat-value"><?= $total_products ?></div>
    <div class="stat-label">Total Products</div>
</a>

<a href="dashboard.php?page=requests" class="stat-card">
    <div class="stat-value"><?= $total_requests ?></div>
    <div class="stat-label">Total Requests</div>
</a>

<a href="dashboard.php?page=requests" class="stat-card">
    <div class="stat-value"><?= $pending_requests ?></div>
    <div class="stat-label">Pending Requests</div>
</a>

<?php endif; ?>

<?php if ($role === 'manager'): ?>

<a href="dashboard.php?page=products" class="stat-card">
    <div class="stat-value"><?= $total_products ?></div>
    <div class="stat-label">Total Products</div>
</a>

<a href="dashboard.php?page=requests" class="stat-card">
    <div class="stat-value"><?= $total_requests ?></div>
    <div class="stat-label">Total Requests</div>
</a>

<a href="dashboard.php?page=requests" class="stat-card">
    <div class="stat-value"><?= $pending_requests ?></div>
    <div class="stat-label">Pending Requests</div>
</a>

<?php endif; ?>

<?php if ($role === 'staff'): ?>

<a href="dashboard.php?page=my_requests" class="stat-card">
    <div class="stat-value"><?= $my_total ?></div>
    <div class="stat-label">My Requests</div>
</a>

<a href="dashboard.php?page=my_requests" class="stat-card">
    <div class="stat-value"><?= $my_pending ?></div>
    <div class="stat-label">Pending</div>
</a>

<a href="dashboard.php?page=my_requests" class="stat-card">
    <div class="stat-value"><?= $my_approved ?></div>
    <div class="stat-label">Approved</div>
</a>

<?php endif; ?>

</div>

<!-- RECENT -->
<div class="section-title">Recent Activity</div>

<div class="recent-grid">

<div class="recent-card">
    <div class="recent-card-title">Latest Products</div>

    <?php
    $recent_products = $conn->query("
        SELECT name, quantity 
        FROM products 
        ORDER BY created_at DESC 
        LIMIT 5
    ");

    if ($recent_products && $recent_products->num_rows > 0):
        while ($p = $recent_products->fetch_assoc()):
    ?>
        <div class="recent-item">
            <span><?= htmlspecialchars($p['name']) ?></span>
            <span style="color:var(--green); font-weight:600;">x<?= $p['quantity'] ?></span>
        </div>
    <?php endwhile; else: ?>
        <div class="empty">No products yet.</div>
    <?php endif; ?>
</div>

</div>

<!-- LOW STOCK ALARM -->
<?php if ($low_stock_items && $low_stock_items->num_rows > 0): ?>
<div class="recent-card alarm-glow">
    <div class="recent-card-title alarm-title">
        ⚠️ Low Stock Alarm
    </div>

    <?php while ($item = $low_stock_items->fetch_assoc()): ?>
        <div class="recent-item">
            <span><?= htmlspecialchars($item['name']) ?></span>
            <span style="color:#f87171; font-weight:700;">
                Only <?= (int)$item['quantity'] ?> left
            </span>
        </div>
    <?php endwhile; ?>

</div>
<?php else: ?>
<div class="empty" style="margin-top:15px;">
    ✅ No low stock alerts
</div>
<?php endif; ?>