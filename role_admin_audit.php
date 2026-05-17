<?php
/* ============================================================
   AUDIT LOGS — Admin Only
   Shows all actions logged in the system with username joins
============================================================ */

$filter_action = trim($_GET['filter_action'] ?? '');
$filter_user   = trim($_GET['filter_user'] ?? '');

/* ============================================================
   BUILD QUERY WITH OPTIONAL FILTERS
============================================================ */
$where_parts = [];
$params      = [];
$types       = '';

if ($filter_action !== '') {
    $where_parts[] = "audit_logs.action = ?";
    $params[]      = $filter_action;
    $types        .= 's';
}

if ($filter_user !== '') {
    $where_parts[] = "users.username LIKE ?";
    $params[]      = "%$filter_user%";
    $types        .= 's';
}

$where_sql = count($where_parts) > 0 ? 'WHERE ' . implode(' AND ', $where_parts) : '';

$sql = "
    SELECT audit_logs.*, users.username, users.role AS user_role
    FROM audit_logs
    LEFT JOIN users ON users.id = audit_logs.user_id
    $where_sql
    ORDER BY audit_logs.created_at DESC
    LIMIT 200
";

if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $audit = $stmt->get_result();
} else {
    $audit = $conn->query($sql);
}


$actions_result = $conn->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");
$distinct_actions = [];
if ($actions_result) {
    while ($row = $actions_result->fetch_assoc()) {
        $distinct_actions[] = $row['action'];
    }
}

/* ============================================================
   ACTION COLOR MAP
============================================================ */
$action_colors = [
    'ADD_PRODUCT'      => ['bg' => 'rgba(34,197,94,0.1)',   'color' => '#22c55e'],
    'UPDATE_PRODUCT'   => ['bg' => 'rgba(96,165,250,0.1)',  'color' => '#60a5fa'],
    'DELETE_PRODUCT'   => ['bg' => 'rgba(248,113,113,0.1)', 'color' => '#f87171'],
    'RESTOCK_PRODUCT'  => ['bg' => 'rgba(167,139,250,0.1)', 'color' => '#a78bfa'],
    'APPROVE_REQUEST'  => ['bg' => 'rgba(34,197,94,0.1)',   'color' => '#22c55e'],
    'DECLINE_REQUEST'  => ['bg' => 'rgba(248,113,113,0.1)', 'color' => '#f87171'],
    'REQUEST_SUBMIT'   => ['bg' => 'rgba(251,191,36,0.1)',  'color' => '#fbbf24'],
    'APPROVE_USER'     => ['bg' => 'rgba(34,197,94,0.1)',   'color' => '#22c55e'],
    'DECLINE_USER'     => ['bg' => 'rgba(248,113,113,0.1)', 'color' => '#f87171'],
    'DELETE_USER'      => ['bg' => 'rgba(248,113,113,0.1)', 'color' => '#f87171'],
    'UPDATE_ROLE'      => ['bg' => 'rgba(167,139,250,0.1)', 'color' => '#a78bfa'],
];
?>

<style>
.page-title { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 20px; }
.page-title span { color: #22c55e; }

.filter-bar {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-label { font-size: .68rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #94a3b8; }

.filter-input, .filter-select {
    padding: 7px 12px; border-radius: 7px;
    border: 1px solid rgba(255,255,255,0.06);
    background: #0f172a; color: #f1f5f9;
    font-size: .82rem; outline: none;
}

.filter-select option { background: #0f172a; }
.filter-input:focus, .filter-select:focus { border-color: #22c55e; }

.btn-filter {
    padding: 7px 16px; border-radius: 7px; border: none;
    background: #22c55e; color: #000; font-weight: 700;
    font-size: .8rem; cursor: pointer;
}

.btn-reset {
    padding: 7px 16px; border-radius: 7px;
    border: 1px solid rgba(255,255,255,0.1);
    background: transparent; color: #94a3b8; font-size: .8rem;
    text-decoration: none; display: inline-block;
}

.table-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; overflow: hidden; }

.table-top {
    padding: 16px 22px; border-bottom: 1px solid rgba(255,255,255,0.06);
    font-size: .68rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; color: #94a3b8;
    display: flex; align-items: center; justify-content: space-between;
}

.total-badge {
    background: rgba(34,197,94,0.1); color: #22c55e;
    border: 1px solid rgba(34,197,94,0.2);
    padding: 3px 12px; border-radius: 20px; font-size: .72rem; font-weight: 700;
}

table { width: 100%; border-collapse: collapse; }

th {
    padding: 11px 20px; text-align: left; font-size: .65rem;
    font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.06);
}

td {
    padding: 12px 20px; font-size: .85rem;
    border-bottom: 1px solid rgba(255,255,255,0.06); color: #f1f5f9;
}

tr:last-child td { border-bottom: none; }
tr:hover td { background: rgba(255,255,255,0.02); }

.action-badge {
    padding: 3px 10px; border-radius: 6px;
    font-size: .68rem; font-weight: 700; letter-spacing: 1px;
    text-transform: uppercase; white-space: nowrap;
}

.desc-cell { font-size: .8rem; color: #94a3b8; max-width: 380px; }

.empty-row td { text-align: center; color: #94a3b8; padding: 40px; font-size: .85rem; }
</style>

<div class="page-title">📜 <span>Audit Logs</span></div>

<!-- FILTER BAR -->
<form method="GET" action="dashboard.php">
    <input type="hidden" name="page" value="logs">
    <div class="filter-bar">
        <span class="filter-label">Filter:</span>

        <select name="filter_action" class="filter-select">
            <option value="">— All Actions —</option>
            <?php foreach ($distinct_actions as $a): ?>
                <option value="<?= htmlspecialchars($a) ?>" <?= $filter_action === $a ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="filter_user" class="filter-input"
               placeholder="Search username..." value="<?= htmlspecialchars($filter_user) ?>">

        <button type="submit" class="btn-filter">🔍 Filter</button>
        <a href="dashboard.php?page=logs" class="btn-reset">✕ Reset</a>
    </div>
</form>

<!-- TABLE -->
<div class="table-card">
    <div class="table-top">
        <span>Action History</span>
        <span class="total-badge"><?= $audit ? $audit->num_rows : 0 ?> records</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($audit && $audit->num_rows > 0): ?>
            <?php while ($log = $audit->fetch_assoc()):
                $action = $log['action'] ?? '';
                $color  = $action_colors[$action] ?? ['bg' => 'rgba(100,116,139,0.1)', 'color' => '#94a3b8'];
            ?>
            <tr>
                <td style="font-weight:600">
                    <?= htmlspecialchars($log['username'] ?? 'System') ?>
                    <?php if (!empty($log['user_role'])): 
                        $role_colors = [
                            'admin'   => ['bg' => 'rgba(248,113,113,0.15)', 'color' => '#f87171'],
                            'manager' => ['bg' => 'rgba(167,139,250,0.15)', 'color' => '#a78bfa'],
                            'staff'   => ['bg' => 'rgba(96,165,250,0.15)',  'color' => '#60a5fa'],
                        ];
                        $rc = $role_colors[$log['user_role']] ?? ['bg' => 'rgba(148,163,184,0.15)', 'color' => '#94a3b8'];
                    ?>
                    <span style="
                        display:inline-block; margin-left:5px;
                        padding:1px 7px; border-radius:5px; font-size:.62rem;
                        font-weight:700; letter-spacing:1px; text-transform:uppercase;
                        background:<?= $rc['bg'] ?>; color:<?= $rc['color'] ?>;
                    "><?= htmlspecialchars($log['user_role']) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="action-badge" style="background:<?= $color['bg'] ?>; color:<?= $color['color'] ?>; border:1px solid <?= $color['bg'] ?>;">
                        <?= htmlspecialchars($action) ?>
                    </span>
                </td>
                <td class="desc-cell">
                    <?= htmlspecialchars($log['description'] ?? '—') ?>
                </td>
                <td style="color:#94a3b8; font-size:.78rem; white-space:nowrap;">
                    <?= isset($log['created_at']) ? date('M d, Y h:i A', strtotime($log['created_at'])) : '—' ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr class="empty-row"><td colspan="4">No audit logs found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
