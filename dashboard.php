<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   GET LATEST USER FROM DB
========================= */
$user_id = (int)$_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

/* =========================
   ROLE SAFETY FIX
========================= */
$role = strtolower(trim($user['role'] ?? 'staff'));

if (!in_array($role, ['admin', 'manager', 'staff'])) {
    $role = 'staff';
}

/* update session */
$_SESSION['user'] = $user;
$_SESSION['user']['role'] = $role;

$page = $_GET['page'] ?? 'home';

/* =========================
   UNREAD NOTIFICATION COUNT (for topbar badge)
========================= */
$notif_stmt = $conn->prepare("
    SELECT COUNT(*) as cnt FROM notifications
    WHERE (user_id = ? AND user_id IS NOT NULL)
       OR (user_id IS NULL AND role = ?)
");
$notif_stmt->bind_param("is", $user_id, $role);
$notif_stmt->execute();
$notif_count_row = $notif_stmt->get_result()->fetch_assoc();
$notif_count = (int) ($notif_count_row['cnt'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory System</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
/* (UNCHANGED DESIGN - KEEP YOUR ORIGINAL CSS) */
* { margin:0; padding:0; box-sizing:border-box; }

:root {
    --green: #22c55e;
    --bg: #0f172a;
    --card: #1e293b;
    --text: #f1f5f9;
    --sub: #94a3b8;
    --border: rgba(255,255,255,0.06);
    --spotify: #1db954;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    display: flex;
    height: 100vh;
    overflow: hidden;
}

.sidebar {
    width: 250px;
    background: #000;
    height: 100vh;
    padding: 28px 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    border-right: 1px solid var(--border);
}

.sidebar h3 {
    font-family: 'Syne', sans-serif;
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.sidebar h3 span { color: var(--green); }

a {
    color: var(--sub);
    display: block;
    margin: 2px 0;
    text-decoration: none;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: .88rem;
}

a:hover {
    color: var(--green);
    background: rgba(34,197,94,0.08);
}

.sidebar-spacer { flex: 1; }

.logout-link {
    display: flex !important;
    align-items: center;
    gap: 10px;
    color: #fff !important;
    background: var(--spotify) !important;
    padding: 11px 16px;
    border-radius: 50px;
}

.main { flex:1; display:flex; flex-direction:column; overflow:hidden; }

.topbar {
    padding: 16px 28px;
    background:#000;
    border-bottom:1px solid var(--border);
    display:flex;
    justify-content:space-between;
}

.topbar-left { font-family:'Syne'; font-weight:700; }

.topbar-right {
    color: var(--green);
    font-weight:700;
    text-transform:uppercase;
}

.content {
    padding:28px;
    overflow-y:auto;
}
</style>
</head>

<body>

<div class="sidebar">
    <h3>Stock <span>Flow</span></h3>

    <a href="dashboard.php?page=home">🏠 Home</a>

    <?php if ($role === 'admin'): ?>
        <a href="dashboard.php?page=users">👥 Manage Users</a>
        <a href="dashboard.php?page=products">📦 Products</a>
        <a href="dashboard.php?page=overview">📊 Inventory Overview</a>
        <a href="dashboard.php?page=requests">📋 Requests</a>
        <a href="dashboard.php?page=logs">📜 Audit Logs</a>
        <a href="dashboard.php?page=notifications">🔔 Notifications</a>
    <?php endif; ?>

    <?php if ($role === 'manager'): ?>
        <a href="dashboard.php?page=products">📦 Products</a>
        <a href="dashboard.php?page=overview">📊 Inventory Overview</a>
        <a href="dashboard.php?page=requests">📋 Requests</a>
        <a href="dashboard.php?page=notifications">🔔 Notifications</a>
    <?php endif; ?>

    <?php if ($role === 'staff'): ?>
        <a href="dashboard.php?page=products">📦 View Products</a>
        <a href="dashboard.php?page=my_requests">📝 My Requests</a>
        <a href="dashboard.php?page=notifications">🔔 Notifications</a>
    <?php endif; ?>

    <div class="sidebar-spacer"></div>
    <a href="logout.php" class="logout-link"
       onclick="return confirm('Are you sure you want to logout?')">
        🚪 Logout
    </a>
</div>

<div class="main">

    <div class="topbar">
        <div class="topbar-left">
            Welcome, <?= htmlspecialchars($user['username']) ?>
        </div>
        <div class="topbar-right" style="display:flex;align-items:center;gap:14px;">
            <?php if ($notif_count > 0): ?>
            <a href="dashboard.php?page=notifications"
               style="background:rgba(251,191,36,0.15);border:1px solid rgba(251,191,36,0.3);color:#fbbf24;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;text-decoration:none;">
                🔔 <?= $notif_count ?> notification<?= $notif_count !== 1 ? 's' : '' ?>
            </a>
            <?php endif; ?>
            <span><?= htmlspecialchars(strtoupper($role)) ?></span>
        </div>
    </div>

    <div class="content">

        <?php
        switch ($page) {

            case 'users':
                if ($role === 'admin') {
                    include 'role_admin_users.php';
                } else {
                    echo '<p style="color:#94a3b8;">⛔ Access denied.</p>';
                }
                break;

            case 'products':
                if ($role === 'admin') {
                    include 'role_admin_product.php';
                } elseif ($role === 'manager') {
                    include 'role_manager_product.php';
                } elseif ($role === 'staff') {
                    include 'role_staff_products.php';
                } else {
                    echo '<p style="color:#94a3b8;">⛔ Access denied.</p>';
                }
                break;

            case 'requests':
                if (in_array($role, ['admin', 'manager'])) {
                    include 'role_manager_request.php';
                } else {
                    echo '<p style="color:#94a3b8;">⛔ Access denied.</p>';
                }
                break;

          case 'my_requests':
    if ($role === 'staff') {
        include 'role_staff_request.php';
    } else {
        echo '<p style="color:#94a3b8;">⛔ Access denied.</p>';
    }
    break;

case 'overview':
    if (in_array($role, ['admin', 'manager'])) {
        include 'role_inventory_overview.php';
    } else {
        echo '<p style="color:#94a3b8;">⛔ Access denied.</p>';
    }
    break;

case 'logs':
    if ($role === 'admin') {
        include 'role_admin_audit.php';
    } else {
        echo '<p style="color:#94a3b8;">⛔ Access denied.</p>';
    }
    break;
    case 'notifications':
    include 'role_notifications.php';
    break;
default:
    include 'role_home.php';
    break;
                
        }
        ?>

    </div>
</div>

</body>
</html>