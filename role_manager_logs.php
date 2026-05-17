<?php
/* ============================================================
   MANAGER ACTIVITY LOGS
============================================================ */

/* =========================
   FETCH RECENT ACTIVITIES
========================= */

$logs = $conn->query("
    SELECT 
        requests.*,
        users.name AS staff_name,
        products.name AS product_name,
        products.category
    FROM requests
    LEFT JOIN users ON requests.user_id = users.id
    LEFT JOIN products ON requests.product_id = products.id
    ORDER BY requests.created_at DESC
");

/* =========================
   OVERVIEW COUNTS
========================= */

$total_logs = $conn->query("
    SELECT COUNT(*) AS total FROM requests
")->fetch_assoc()['total'];

$pending_logs = $conn->query("
    SELECT COUNT(*) AS total 
    FROM requests 
    WHERE status='pending'
")->fetch_assoc()['total'];

$approved_logs = $conn->query("
    SELECT COUNT(*) AS total 
    FROM requests 
    WHERE status='approved'
")->fetch_assoc()['total'];

$declined_logs = $conn->query("
    SELECT COUNT(*) AS total 
    FROM requests 
    WHERE status='declined'
")->fetch_assoc()['total'];
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

/* OVERVIEW */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px,1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.stat-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 14px;
    padding: 22px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: #22c55e;
    font-family: 'Syne', sans-serif;
}

.stat-label {
    font-size: .75rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-top: 6px;
}

/* TABLE */
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
    font-size: .72rem;
    font-weight: 700;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    padding: 12px 20px;
    text-align: left;
    font-size: .65rem;
    color: #94a3b8;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

td {
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    color: #f1f5f9;
    font-size: .88rem;
}

tr:hover td {
    background: rgba(255,255,255,0.02);
}

/* STATUS */
.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 700;
}

.pending {
    background: rgba(251,191,36,0.1);
    color: #fbbf24;
}

.approved {
    background: rgba(34,197,94,0.1);
    color: #22c55e;
}

.declined {
    background: rgba(248,113,113,0.1);
    color: #f87171;
}

.empty-row td {
    text-align: center;
    color: #94a3b8;
    padding: 40px;
}
</style>

<div class="page-title">
    📜 <span>Activity Logs</span>
</div>

<!-- OVERVIEW -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-number"><?= $total_logs ?></div>
        <div class="stat-label">Total Activities</div>
    </div>

    <div class="stat-card">
        <div class="stat-number"><?= $pending_logs ?></div>
        <div class="stat-label">Pending</div>
    </div>

    <div class="stat-card">
        <div class="stat-number"><?= $approved_logs ?></div>
        <div class="stat-label">Approved</div>
    </div>

    <div class="stat-card">
        <div class="stat-number"><?= $declined_logs ?></div>
        <div class="stat-label">Declined</div>
    </div>

</div>

<!-- LOG TABLE -->
<div class="table-card">

    <div class="table-top">
        <span>Recent Staff Activities</span>

        <span class="total-badge">
            <?= $logs ? $logs->num_rows : 0 ?> logs
        </span>
    </div>

    <table>

        <thead>
            <tr>
                <th>#</th>
                <th>Staff</th>
                <th>Product</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>

        <?php if ($logs && $logs->num_rows > 0): ?>

            <?php $i = 1; while($log = $logs->fetch_assoc()): ?>

            <tr>

                <td style="color:#94a3b8;">
                    <?= $i++ ?>
                </td>

                <td>
                    👤 <?= htmlspecialchars($log['staff_name'] ?? 'Unknown User') ?>
                </td>

                <td>
                    📦 <?= htmlspecialchars($log['product_name'] ?? 'Deleted Product') ?>
                </td>

                <td style="color:#94a3b8;">
                    <?= htmlspecialchars($log['category'] ?? '—') ?>
                </td>

                <td>
                    <?= $log['quantity'] ?>
                </td>

                <td>
                    <span class="badge <?= strtolower($log['status']) ?>">
                        <?= ucfirst($log['status']) ?>
                    </span>
                </td>

                <td style="color:#94a3b8; font-size:.78rem;">
                    <?= date('M d, Y h:i A', strtotime($log['created_at'])) ?>
                </td>

            </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr class="empty-row">
                <td colspan="7">
                    No activity logs found.
                </td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>