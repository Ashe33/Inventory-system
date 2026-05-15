<?php
// Fetch products with stock
$all_products = $conn->query("SELECT * FROM products WHERE quantity > 0 ORDER BY name ASC");

// Fetch this staff's requests
$uid         = $user['id'];
$my_requests = $conn->query("
    SELECT r.*, p.name AS product_name, p.category
    FROM requests r
    JOIN products p ON r.product_id = p.id
    WHERE r.user_id = $uid
    ORDER BY r.created_at DESC
");
?>

<style>
.page-title { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 24px; }
.page-title span { color: #22c55e; }

.alert { padding: 12px 16px; border-radius: 8px; font-size: .85rem; font-weight: 500; margin-bottom: 18px; }
.alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #22c55e; }
.alert-error   { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.2); color: #f87171; }

.form-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 22px; margin-bottom: 24px; }
.form-card-title { font-size: .68rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #22c55e; margin-bottom: 16px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end; }
.form-group { display: flex; flex-direction: column; gap: 6px; }

.form-label { font-size: .65rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #94a3b8; }

.form-input, .form-select {
    padding: 10px 14px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.06);
    background: #0f172a; color: #f1f5f9;
    font-family: 'DM Sans', sans-serif; font-size: .88rem;
    outline: none; transition: .2s;
}

.form-input:focus, .form-select:focus { border-color: #22c55e; }
.form-select option { background: #0f172a; }

.btn { padding: 10px 18px; border-radius: 8px; border: none; font-weight: 700; font-size: .82rem; cursor: pointer; transition: .2s; text-decoration: none; display: inline-block; text-align: center; }
.btn-green { background: #22c55e; color: #000; }
.btn-green:hover { background: #16a34a; }

.table-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; overflow: hidden; }

.table-top { padding: 16px 22px; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: .68rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #94a3b8; }

table { width: 100%; border-collapse: collapse; }

th { padding: 11px 20px; text-align: left; font-size: .65rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.06); }

td { padding: 13px 20px; font-size: .88rem; border-bottom: 1px solid rgba(255,255,255,0.06); color: #f1f5f9; }

tr:last-child td { border-bottom: none; }
tr:hover td { background: rgba(255,255,255,0.02); }

.badge { padding: 3px 12px; border-radius: 20px; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
.badge-pending  { background: rgba(251,191,36,0.1);  color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
.badge-approved { background: rgba(34,197,94,0.1);   color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
.badge-declined { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }

.empty-row td { text-align: center; color: #94a3b8; padding: 40px; font-size: .85rem; }
</style>

<div class="page-title">📝 My <span>Requests</span></div>

<!-- ALERTS -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✅ Request submitted successfully!</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <?php
    $err = match($_GET['error']) {
        'stock'    => '❌ Requested quantity exceeds available stock (' . ($_GET['max'] ?? '?') . ').',
        'notfound' => '❌ Product not found.',
        'invalid'  => '❌ Invalid input. Please try again.',
        default    => '❌ Something went wrong.'
    };
    ?>
    <div class="alert alert-error"><?= $err ?></div>
<?php endif; ?>

<!-- FORM -->
<div class="form-card">
    <div class="form-card-title">Submit New Request</div>
    <form method="POST" action="process/request_submit.php">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select" required>
                    <option value="">— Select Product —</option>
                    <?php if ($all_products && $all_products->num_rows > 0): ?>
                        <?php while ($prod = $all_products->fetch_assoc()): ?>
                            <option value="<?= $prod['id'] ?>">
                                <?= htmlspecialchars($prod['name']) ?> (<?= $prod['quantity'] ?> in stock)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-input" placeholder="1" min="1" required>
            </div>
            <button type="submit" class="btn btn-green">Submit →</button>
        </div>
    </form>
</div>

<!-- MY REQUESTS TABLE -->
<div class="table-card">
    <div class="table-top">My Request History</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($my_requests && $my_requests->num_rows > 0): ?>
            <?php $i = 1; while ($r = $my_requests->fetch_assoc()): ?>
            <tr>
                <td style="color:#94a3b8"><?= $i++ ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td style="color:#94a3b8"><?= htmlspecialchars($r['category'] ?? '—') ?></td>
                <td style="color:#22c55e; font-weight:600"><?= $r['quantity'] ?></td>
                <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
                <td style="color:#94a3b8; font-size:.78rem"><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr class="empty-row"><td colspan="6">No requests yet. Submit one above.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
