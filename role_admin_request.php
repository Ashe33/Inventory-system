<?php
// Handle Approve
if (isset($_GET['approve'])) {
    $req_id = (int) $_GET['approve'];

    // Get request details
    $req = $conn->query("SELECT * FROM requests WHERE id=$req_id")->fetch_assoc();

    if ($req && $req['status'] == 'pending') {
        $product_id = $req['product_id'];
        $quantity   = $req['quantity'];

        // Check stock
        $product = $conn->query("SELECT * FROM products WHERE id=$product_id")->fetch_assoc();

        if ($product && $product['quantity'] >= $quantity) {
            // Deduct stock
            $conn->query("UPDATE products SET quantity = quantity - $quantity WHERE id=$product_id");

            // Update request status
            $conn->query("UPDATE requests SET status='approved' WHERE id=$req_id");

            header("Location: dashboard.php?page=requests&success=approved");
            exit();
        } else {
            header("Location: dashboard.php?page=requests&error=stock");
            exit();
        }
    }
}

// Handle Decline
if (isset($_GET['decline'])) {
    $req_id = (int) $_GET['decline'];
    $conn->query("UPDATE requests SET status='declined' WHERE id=$req_id");
    header("Location: dashboard.php?page=requests&success=declined");
    exit();
}

// Fetch all requests
$all_requests = $conn->query("
    SELECT r.*, p.name AS product_name, p.category, p.quantity AS stock_left, u.username
    FROM requests r
    JOIN products p ON r.product_id = p.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
?>

<style>
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

.alert-error {
    background: rgba(248,113,113,0.1);
    border: 1px solid rgba(248,113,113,0.2);
    color: #f87171;
}

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
}

table { width: 100%; border-collapse: collapse; }

th {
    padding: 11px 20px;
    text-align: left;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #94a3b8;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

td {
    padding: 13px 20px;
    font-size: .88rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    color: #f1f5f9;
}

tr:last-child td { border-bottom: none; }
tr:hover td { background: rgba(255,255,255,0.02); }

.badge {
    padding: 3px 12px;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.badge-pending {
    background: rgba(251,191,36,0.1);
    color: #fbbf24;
    border: 1px solid rgba(251,191,36,0.2);
}

.badge-approved {
    background: rgba(34,197,94,0.1);
    color: #22c55e;
    border: 1px solid rgba(34,197,94,0.2);
}

.badge-declined {
    background: rgba(248,113,113,0.1);
    color: #f87171;
    border: 1px solid rgba(248,113,113,0.2);
}

.actions-col { display: flex; gap: 8px; }

.btn {
    padding: 6px 12px;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    font-size: .75rem;
    cursor: pointer;
    transition: .2s;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-green { background: #22c55e; color: #000; }
.btn-green:hover { background: #16a34a; }

.btn-red {
    background: rgba(248,113,113,0.1);
    color: #f87171;
    border: 1px solid rgba(248,113,113,0.2);
}

.btn-red:hover { background: rgba(248,113,113,0.2); }

.empty-row td {
    text-align: center;
    color: #94a3b8;
    padding: 40px;
    font-size: .85rem;
}
</style>

<!-- TITLE -->
<div class="page-title">📋 All <span>Requests</span></div>

<!-- ALERTS -->
<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] == 'approved'): ?>
        <div class="alert alert-success">✅ Request approved and stock updated.</div>
    <?php elseif ($_GET['success'] == 'declined'): ?>
        <div class="alert alert-success">✅ Request declined successfully.</div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'stock'): ?>
    <div class="alert alert-error">❌ Not enough stock to approve this request.</div>
<?php endif; ?>

<!-- TABLE -->
<div class="table-card">
    <div class="table-top">All Staff Requests</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Staff</th>
                <th>Product</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Stock Left</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($all_requests && $all_requests->num_rows > 0): ?>
            <?php $i = 1; while ($r = $all_requests->fetch_assoc()): ?>
            <tr>
                <td style="color:#94a3b8"><?= $i++ ?></td>
                <td style="font-weight:600"><?= htmlspecialchars($r['username']) ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td style="color:#94a3b8"><?= htmlspecialchars($r['category'] ?? '—') ?></td>
                <td style="color:#22c55e; font-weight:600"><?= $r['quantity'] ?></td>
                <td style="color:#94a3b8"><?= $r['stock_left'] ?></td>
                <td>
                    <span class="badge badge-<?= $r['status'] ?>">
                        <?= $r['status'] ?>
                    </span>
                </td>
                <td style="color:#94a3b8; font-size:.78rem">
                    <?= date('M d, Y h:i A', strtotime($r['created_at'])) ?>
                </td>
                <td>
                    <?php if ($r['status'] == 'pending'): ?>
                    <div class="actions-col">
                        <a href="?page=requests&approve=<?= $r['id'] ?>"
                           class="btn btn-green"
                           onclick="return confirm('Approve this request?')">
                            ✅ Approve
                        </a>
                        <a href="?page=requests&decline=<?= $r['id'] ?>"
                           class="btn btn-red"
                           onclick="return confirm('Decline this request?')">
                             Decline
                        </a>
                    </div>
                    <?php else: ?>
                        <span style="color:#94a3b8; font-size:.78rem">No action</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr class="empty-row">
                <td colspan="9">No requests yet.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>