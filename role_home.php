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
    position: relative;
    overflow: hidden;
    transition: .2s;
}

.stat-card:hover {
    border-color: rgba(34,197,94,0.3);
    transform: translateY(-2px);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 80px;
    background: radial-gradient(circle, rgba(34,197,94,0.08) 0%, transparent 70%);
}

.stat-icon { font-size: 1.6rem; margin-bottom: 14px; }

.stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--green);
    line-height: 1;
    margin-bottom: 6px;
}

.stat-label {
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--sub);
}

.welcome-banner {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
}

.welcome-banner::before {
    content: 'IMS';
    position: absolute;
    right: -10px; top: -10px;
    font-family: 'Syne', sans-serif;
    font-size: 6rem;
    font-weight: 800;
    color: var(--green);
    opacity: .04;
}

.welcome-tag {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--green);
    margin-bottom: 8px;
}

.welcome-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.6rem;
    font-weight: 800;
    margin-bottom: 6px;
}

.welcome-sub { font-size: .85rem; color: var(--sub); }
.welcome-logo { font-size: 3rem; opacity: .6; }

.section-title {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

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
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: .85rem;
}

.recent-item:last-child { border-bottom: none; }

.badge {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
}

.badge-pending {
    background: rgba(251,191,36,0.1);
    color: #fbbf24;
    border: 1px solid rgba(251,191,36,0.2);
}

.badge-approved {
    background: rgba(34,197,94,0.1);
    color: var(--green);
    border: 1px solid rgba(34,197,94,0.2);
}

.badge-declined {
    background: rgba(248,113,113,0.1);
    color: #f87171;
    border: 1px solid rgba(248,113,113,0.2);
}

.empty { color: var(--sub); font-size: .82rem; text-align: center; padding: 20px 0; }
</style>

<!-- WELCOME BANNER -->
<div class="welcome-banner">
    <div class="welcome-text">
        <div class="welcome-tag">Dashboard Overview</div>
        <div class="welcome-title">Welcome back, <?= htmlspecialchars($user['username']) ?></div>
        <div class="welcome-sub">Here's what's happening in your inventory today.</div>
    </div>
    <div class="welcome-logo">📦</div>
</div>

<!-- STATS -->
<div class="stats-grid">

    <?php if ($role === 'admin'): ?>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value"><?= $total_users ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-value"><?= $total_products ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-value"><?= $total_requests ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-value"><?= $pending_requests ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
    <?php endif; ?>

    <?php if ($role === 'manager'): ?>
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-value"><?= $total_products ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-value"><?= $total_requests ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-value"><?= $pending_requests ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
    <?php endif; ?>

    <?php if ($role === 'staff'): ?>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-value"><?= $my_total ?></div>
            <div class="stat-label">My Total Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-value"><?= $my_pending ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?= $my_approved ?></div>
            <div class="stat-label">Approved</div>
        </div>
    <?php endif; ?>

</div>

<!-- RECENT ACTIVITY -->
<div class="section-title">Recent Activity</div>

<div class="recent-grid">

    <!-- Recent Products -->
    <div class="recent-card">
        <div class="recent-card-title">Latest Products</div>
        <?php
        $recent_products = $conn->query("SELECT name, quantity FROM products ORDER BY created_at DESC LIMIT 5");
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

    <!-- Recent Requests -->
    <div class="recent-card">
        <div class="recent-card-title">Latest Requests</div>
        <?php
        if ($role === 'staff') {
            $uid = $user['id'];
            $recent_requests = $conn->query("SELECT r.id, u.username, r.status FROM requests r JOIN users u ON r.user_id = u.id WHERE r.user_id=$uid ORDER BY r.created_at DESC LIMIT 5");
        } else {
            $recent_requests = $conn->query("SELECT r.id, u.username, r.status FROM requests r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5");
        }
        if ($recent_requests && $recent_requests->num_rows > 0):
            while ($r = $recent_requests->fetch_assoc()):
        ?>
        <div class="recent-item">
            <span><?= htmlspecialchars($r['username']) ?> — #<?= $r['id'] ?></span>
            <span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span>
        </div>
        <?php endwhile; else: ?>
            <div class="empty">No requests yet.</div>
        <?php endif; ?>
    </div>

</div>
