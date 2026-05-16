<?php
/* ============================================================
   NOTIFICATIONS PAGE
   - Staff: sees their own personal notifications + staff-role notifications
   - Manager: sees manager-role notifications
   - Admin: sees all admin-role notifications
   Rules:
   - Personal (user_id = X): shows only to that user
   - Role-based (user_id = NULL, role = 'staff'/'manager'/'admin'): shows to all in that role
============================================================ */

$user_id = (int) $user['id'];
$role    = $user['role'];

/* ============================================================
   FETCH NOTIFICATIONS (prepared statement for safety)
============================================================ */
$stmt = $conn->prepare("
    SELECT * FROM notifications
    WHERE (user_id = ? AND user_id IS NOT NULL)
       OR (user_id IS NULL AND role = ?)
    ORDER BY created_at DESC
    LIMIT 100
");
$stmt->bind_param("is", $user_id, $role);
$stmt->execute();
$notifications = $stmt->get_result();

/* ============================================================
   COUNT UNREAD (optional — for display)
============================================================ */
$total = $notifications->num_rows;

/* ============================================================
   TYPE STYLING MAP
============================================================ */
$type_styles = [
    'success' => ['bg' => 'rgba(34,197,94,0.08)',  'border' => 'rgba(34,197,94,0.2)',   'color' => '#22c55e', 'icon' => '✅'],
    'warning' => ['bg' => 'rgba(251,191,36,0.08)', 'border' => 'rgba(251,191,36,0.2)',  'color' => '#fbbf24', 'icon' => '⚠️'],
    'error'   => ['bg' => 'rgba(248,113,113,0.08)','border' => 'rgba(248,113,113,0.2)', 'color' => '#f87171', 'icon' => '❌'],
    'info'    => ['bg' => 'rgba(96,165,250,0.08)', 'border' => 'rgba(96,165,250,0.2)',  'color' => '#60a5fa', 'icon' => 'ℹ️'],
];
?>

<style>
.page-title { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 24px; }
.page-title span { color: #22c55e; }

.notif-count {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2);
    color: #22c55e; font-size: .72rem; font-weight: 700;
    padding: 3px 12px; border-radius: 20px; margin-left: 10px;
}

.notif-list { display: flex; flex-direction: column; gap: 10px; }

.notif-item {
    padding: 14px 18px;
    border-radius: 10px;
    border: 1px solid;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.notif-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }

.notif-body { flex: 1; }

.notif-message { font-size: .88rem; color: #f1f5f9; line-height: 1.5; }

.notif-meta {
    font-size: .72rem; color: #94a3b8; margin-top: 4px;
    display: flex; gap: 12px;
}

.notif-empty {
    text-align: center; padding: 60px 20px; color: #94a3b8; font-size: .9rem;
    background: #1e293b; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);
}

.notif-type-badge {
    font-size: .62rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.5px; padding: 2px 8px; border-radius: 10px;
}
</style>

<div class="page-title">
    🔔 <span>Notifications</span>
    <span class="notif-count"><?= $total ?> total</span>
</div>

<?php if ($total > 0): ?>
<div class="notif-list">
    <?php while ($n = $notifications->fetch_assoc()):
        $t     = $n['type'] ?? 'info';
        $style = $type_styles[$t] ?? $type_styles['info'];
        $scope = ($n['user_id'] !== null) ? 'Personal' : 'Broadcast';
    ?>
    <div class="notif-item" style="
        background: <?= $style['bg'] ?>;
        border-color: <?= $style['border'] ?>;
    ">
        <div class="notif-icon"><?= $style['icon'] ?></div>
        <div class="notif-body">
            <div class="notif-message"><?= htmlspecialchars($n['message']) ?></div>
            <div class="notif-meta">
                <span><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></span>
                <span class="notif-type-badge" style="background:<?= $style['bg'] ?>; color:<?= $style['color'] ?>; border:1px solid <?= $style['border'] ?>;">
                    <?= strtoupper($t) ?>
                </span>
                <span style="color:#475569"><?= $scope ?></span>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php else: ?>
<div class="notif-empty">
    🔕 No notifications yet.<br>
    <span style="font-size:.8rem; margin-top:6px; display:block;">
        You'll see alerts here when requests are approved/declined or inventory changes occur.
    </span>
</div>
<?php endif; ?>
