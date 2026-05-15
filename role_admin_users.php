<?php
// Fetch all users except current admin
$current_id = $user['id'];
$all_users  = $conn->query("
    SELECT * FROM users
    WHERE id != $current_id
    ORDER BY created_at DESC
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

.alert { padding: 12px 16px; border-radius: 8px; font-size: .85rem; font-weight: 500; margin-bottom: 18px; }
.alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #22c55e; }
.alert-error   { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.2); color: #f87171; }

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
    align-items: center;
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

.badge-pending  { background: rgba(251,191,36,0.1);  color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
.badge-approved { background: rgba(34,197,94,0.1);   color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
.badge-declined { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
.badge-admin    { background: rgba(139,92,246,0.1);  color: #a78bfa; border: 1px solid rgba(139,92,246,0.2); }
.badge-manager  { background: rgba(251,191,36,0.1);  color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
.badge-staff    { background: rgba(96,165,250,0.1);  color: #60a5fa; border: 1px solid rgba(96,165,250,0.2); }

.actions-col { display: flex; gap: 8px; flex-wrap: wrap; }

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

.btn-green  { background: #22c55e; color: #000; }
.btn-green:hover { background: #16a34a; }
.btn-red    { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
.btn-red:hover { background: rgba(248,113,113,0.2); }
.btn-yellow { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
.btn-yellow:hover { background: rgba(251,191,36,0.2); }
.btn-purple { background: rgba(139,92,246,0.1); color: #a78bfa; border: 1px solid rgba(139,92,246,0.2); }
.btn-purple:hover { background: rgba(139,92,246,0.2); }

.avatar {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: #22c55e;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: .85rem;
    color: #000;
}

.user-cell { display: flex; align-items: center; gap: 10px; }

.role-select {
    background: #0f172a;
    color: #f1f5f9;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 6px;
    padding: 4px 8px;
    font-size: .75rem;
    cursor: pointer;
    outline: none;
}

.role-select:focus { border-color: #22c55e; }

.empty-row td { text-align: center; color: #94a3b8; padding: 40px; font-size: .85rem; }
</style>

<div class="page-title">👥 Manage <span>Users</span></div>

<!-- ALERTS -->
<?php if (isset($_GET['success'])): ?>
    <?php
    $msg = match($_GET['success']) {
        'approved'      => '✅ User approved successfully.',
        'declined'      => '✅ User declined successfully.',
        'deleted'       => '✅ User deleted successfully.',
        'role_updated'  => '✅ User role updated successfully.',
        default         => '✅ Done.'
    };
    ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<!-- TABLE -->
<div class="table-card">
    <div class="table-top">
        <span>All Users</span>
        <span class="total-badge"><?= $all_users ? $all_users->num_rows : 0 ?> users</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($all_users && $all_users->num_rows > 0): ?>
            <?php $i = 1; while ($u = $all_users->fetch_assoc()): ?>
            <tr>
                <td style="color:#94a3b8"><?= $i++ ?></td>
                <td>
                    <div class="user-cell">
                        <div class="avatar"><?= strtoupper(substr($u['username'], 0, 1)) ?></div>
                        <?= htmlspecialchars($u['username']) ?>
                    </div>
                </td>
                <td style="color:#94a3b8"><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                <td>
                    <!-- Role assign form -->
                    <form method="POST" action="process/user_role.php" style="display:inline-flex; gap:6px; align-items:center;">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <select name="role" class="role-select">
                            <option value="staff"   <?= $u['role'] === 'staff'   ? 'selected' : '' ?>>Staff</option>
                            <option value="manager" <?= $u['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                            <option value="admin"   <?= $u['role'] === 'admin'   ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <button type="submit" class="btn btn-purple" style="padding:4px 10px; font-size:.7rem;">Save</button>
                    </form>
                </td>
                <td><span class="badge badge-<?= $u['status'] ?>"><?= $u['status'] ?></span></td>
                <td style="color:#94a3b8; font-size:.78rem">
                    <?= isset($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : '—' ?>
                </td>
                <td>
                    <div class="actions-col">
                        <?php if ($u['status'] === 'pending'): ?>
                            <a href="process/user_action.php?action=approve&id=<?= $u['id'] ?>" class="btn btn-green" onclick="return confirm('Approve this user?')">✅ Approve</a>
                            <a href="process/user_action.php?action=decline&id=<?= $u['id'] ?>" class="btn btn-yellow" onclick="return confirm('Decline this user?')">⛔ Decline</a>
                        <?php elseif ($u['status'] === 'approved'): ?>
                            <a href="process/user_action.php?action=decline&id=<?= $u['id'] ?>" class="btn btn-yellow" onclick="return confirm('Revoke this user?')">⛔ Revoke</a>
                        <?php elseif ($u['status'] === 'declined'): ?>
                            <a href="process/user_action.php?action=approve&id=<?= $u['id'] ?>" class="btn btn-green" onclick="return confirm('Approve this user?')">✅ Approve</a>
                        <?php endif; ?>
                        <a href="process/user_action.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-red" onclick="return confirm('Permanently delete this user?')">🗑️ Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr class="empty-row"><td colspan="7">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
