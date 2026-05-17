<?php
/* ============================================================
   ADMIN REPORTS (FULL SYSTEM VIEW - IMPROVED)
============================================================ */

// COUNTS
$total_users    = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_products = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$total_requests = $conn->query("SELECT COUNT(*) AS c FROM requests")->fetch_assoc()['c'];

$pending  = $conn->query("SELECT COUNT(*) AS c FROM requests WHERE status='pending'")->fetch_assoc()['c'];
$approved = $conn->query("SELECT COUNT(*) AS c FROM requests WHERE status='approved'")->fetch_assoc()['c'];
$declined = $conn->query("SELECT COUNT(*) AS c FROM requests WHERE status='declined'")->fetch_assoc()['c'];

// TOP PRODUCTS
$top_products = $conn->query("
    SELECT p.name, SUM(r.quantity) AS total
    FROM requests r
    LEFT JOIN products p ON p.id = r.product_id
    GROUP BY r.product_id
    ORDER BY total DESC
    LIMIT 5
");
?>

<style>
.page-title{
    font-family:'Syne',sans-serif;
    font-size:1.5rem;
    font-weight:800;
    margin-bottom:18px;
}
.page-title span{ color:#22c55e; }

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:14px;
}

/* CARD */
.card{
    background:#1e293b;
    border:1px solid rgba(255,255,255,0.06);
    border-radius:14px;
    padding:18px;
    transition:.2s;
    cursor:pointer;
}

.card:hover{
    transform:translateY(-3px);
    border-color:#22c55e;
}

/* VALUE */
.value{
    font-size:2rem;
    font-weight:800;
    color:#22c55e;
}

.label{
    font-size:.7rem;
    color:#94a3b8;
    text-transform:uppercase;
    letter-spacing:1px;
}

/* SECTION */
.section{
    margin-top:22px;
    background:#1e293b;
    border:1px solid rgba(255,255,255,0.06);
    border-radius:14px;
    padding:18px;
}

/* ITEM */
.item{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid rgba(255,255,255,0.06);
    font-size:.9rem;
}

.item:last-child{ border:none; }

/* HEADER */
.section-title{
    font-size:.7rem;
    text-transform:uppercase;
    letter-spacing:2px;
    color:#94a3b8;
    margin-bottom:10px;
}

/* COLOR TAGS */
.badge{
    padding:3px 10px;
    border-radius:20px;
    font-size:.7rem;
    font-weight:700;
}

.badge-pending{ background:rgba(251,191,36,0.1); color:#fbbf24; }
.badge-approved{ background:rgba(34,197,94,0.1); color:#22c55e; }
.badge-declined{ background:rgba(248,113,113,0.1); color:#f87171; }
</style>

<div class="page-title">📊 <span>Admin Reports</span></div>

<!-- MAIN STATS (CLICKABLE) -->
<div class="grid">

    <a href="dashboard.php?page=users" class="card">
        <div class="value"><?= $total_users ?></div>
        <div class="label">Total Users</div>
    </a>

    <a href="dashboard.php?page=products" class="card">
        <div class="value"><?= $total_products ?></div>
        <div class="label">Total Products</div>
    </a>

    <a href="dashboard.php?page=requests" class="card">
        <div class="value"><?= $total_requests ?></div>
        <div class="label">Total Requests</div>
    </a>

    <a href="dashboard.php?page=requests&filter=pending" class="card">
        <div class="value"><?= $pending ?></div>
        <div class="label">Pending</div>
    </a>

    <a href="dashboard.php?page=requests&filter=approved" class="card">
        <div class="value"><?= $approved ?></div>
        <div class="label">Approved</div>
    </a>

    <a href="dashboard.php?page=requests&filter=declined" class="card">
        <div class="value"><?= $declined ?></div>
        <div class="label">Declined</div>
    </a>

</div>

<!-- TOP PRODUCTS -->
<div class="section">

    <div class="section-title">🔥 Top Requested Products</div>

    <?php if ($top_products && $top_products->num_rows > 0): ?>
        <?php while($p = $top_products->fetch_assoc()): ?>
            <div class="item">
                <span><?= htmlspecialchars($p['name']) ?></span>
                <span class="badge badge-approved"><?= (int)$p['total'] ?></span>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="color:#94a3b8;">No data yet.</div>
    <?php endif; ?>

</div>