<?php
/* ============================================================
   STAFF: View Products (Read-Only)
   Staff can only browse available products, no CRUD
============================================================ */

$products = $conn->query("SELECT * FROM products ORDER BY name ASC");
?>

<style>
.page-title { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 8px; }
.page-title span { color: #22c55e; }

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
    margin-bottom: 24px;
    display: inline-block;
}

.table-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; overflow: hidden; }

.table-top {
    padding: 16px 22px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    font-size: .68rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: #94a3b8;
    display: flex; align-items: center; justify-content: space-between;
}

.total-badge {
    background: rgba(34,197,94,0.1); color: #22c55e;
    border: 1px solid rgba(34,197,94,0.2);
    padding: 3px 12px; border-radius: 20px;
    font-size: .72rem; font-weight: 700;
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
    padding: 3px 12px; border-radius: 20px;
    font-size: .72rem; font-weight: 700;
}

.qty-low {
    background: rgba(248,113,113,0.1); color: #f87171;
    border: 1px solid rgba(248,113,113,0.2);
}

.qty-zero {
    background: rgba(107,114,128,0.1); color: #6b7280;
    border: 1px solid rgba(107,114,128,0.2);
}

.btn {
    padding: 6px 12px; border-radius: 8px; border: none;
    font-weight: 700; font-size: .75rem; cursor: pointer;
    transition: .2s; text-decoration: none; display: inline-block; text-align: center;
}

.btn-green { background: #22c55e; color: #000; }
.btn-green:hover { background: #16a34a; }

.empty-row td { text-align: center; color: #94a3b8; padding: 40px; font-size: .85rem; }
</style>

<div class="page-title">📦 <span>Products</span></div>
<div class="view-notice">👁️ View Only — Use My Requests to request items</div>

<div class="table-card">
    <div class="table-top">
        <span>Available Products</span>
        <span class="total-badge"><?= $products ? $products->num_rows : 0 ?> items</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Action</th>
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
                    <?php if ($p['quantity'] == 0): ?>
                        <span class="qty-badge qty-zero">Out of Stock</span>
                    <?php elseif ($p['quantity'] <= 5): ?>
                        <span class="qty-badge qty-low"><?= $p['quantity'] ?> (Low)</span>
                    <?php else: ?>
                        <span class="qty-badge"><?= $p['quantity'] ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p['quantity'] > 0): ?>
                        <a href="dashboard.php?page=my_requests" class="btn btn-green" style="font-size:.75rem;">📝 Request</a>
                    <?php else: ?>
                        <span style="color:#6b7280; font-size:.78rem;">Unavailable</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr class="empty-row"><td colspan="5">No products available yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
