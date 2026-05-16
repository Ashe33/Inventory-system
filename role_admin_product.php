<?php
// Fetch all products - VIEW ONLY
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
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

.empty-row td { text-align: center; color: #94a3b8; padding: 40px; font-size: .85rem; }
</style>

<div class="page-title">📦 <span>Products</span></div>
<div style="display:inline-flex;align-items:center;gap:6px;background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.2);color:#a78bfa;font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:5px 14px;border-radius:20px;margin-bottom:24px;display:inline-block;">
    👑 Admin View — Delete only. Managers handle daily stock operations.
</div>


<div class="table-card">
    <div class="table-top">
        <span>All Products</span>
        <span class="total-badge"><?= $products ? $products->num_rows : 0 ?> items</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Added</th>
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
                    <span class="qty-badge <?= $p['quantity'] <= 5 ? 'qty-low' : '' ?>">
                        <?= $p['quantity'] ?>
                    </span>
                </td>
                <td style="color:#94a3b8; font-size:.78rem">
                    <?= date('M d, Y', strtotime($p['created_at'])) ?>
                </td>
                <td>
                    <a href="process/product_delete.php?id=<?= $p['id'] ?>"
                       class="btn btn-red"
                       style="padding:5px 12px; font-size:.72rem; border-radius:6px; background:rgba(248,113,113,0.1); color:#f87171; border:1px solid rgba(248,113,113,0.2); text-decoration:none; font-weight:700;"
                       onclick="return confirm('Delete this product permanently?')">
                        🗑️ Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr class="empty-row"><td colspan="6">No products yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
