<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   GET USER FROM DATABASE
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
   ROLE SAFETY
========================= */
$role = strtolower(trim($user['role'] ?? 'staff'));

if (!in_array($role, ['admin', 'manager', 'staff'])) {
    $role = 'staff';
}

$_SESSION['user'] = $user;
$_SESSION['user']['role'] = $role;

$page = $_GET['page'] ?? 'home';

/* =========================
   NOTIFICATION COUNT (FIXED)
========================= */
$notif_stmt = $conn->prepare("
    SELECT COUNT(*) as cnt FROM notifications
    WHERE is_read = 0
    AND (
        (user_id = ? AND user_id IS NOT NULL)
        OR (user_id IS NULL AND role = ?)
    )
");
$notif_stmt->bind_param("is", $user_id, $role);
$notif_stmt->execute();
$notif_count_row = $notif_stmt->get_result()->fetch_assoc();
$notif_count = (int)($notif_count_row['cnt'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory System</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
* { margin:0; padding:0; box-sizing:border-box; }

:root {
    --green:#22c55e;
    --bg:#0f172a;
    --card:#1e293b;
    --text:#f1f5f9;
    --sub:#94a3b8;
    --border:rgba(255,255,255,0.06);
}

body {
    font-family:'DM Sans', sans-serif;
    background:var(--bg);
    color:var(--text);
    display:flex;
    height:100vh;
    overflow:hidden;
}

.sidebar {
    width:250px;
    background:#000;
    padding:28px 20px;
    display:flex;
    flex-direction:column;
    border-right:1px solid var(--border);
}

.sidebar h3 {
    font-family:'Syne';
    font-weight:800;
    margin-bottom:28px;
    border-bottom:1px solid var(--border);
    padding-bottom:20px;
}

.sidebar h3 span { color:var(--green); }

a {
    color:var(--sub);
    text-decoration:none;
    padding:10px 14px;
    border-radius:8px;
    font-size:.88rem;
}

a:hover {
    color:var(--green);
    background:rgba(34,197,94,0.08);
}

.sidebar-spacer { flex:1; }

.main {
    flex:1;
    display:flex;
    flex-direction:column;
}

.topbar {
    padding:16px 28px;
    background:#000;
    border-bottom:1px solid var(--border);
    display:flex;
    justify-content:space-between;
}

.topbar-left { font-weight:700; font-family:'Syne'; }

.topbar-right { color:var(--green); font-weight:700; }

.content {
    padding:28px;
    overflow-y:auto;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>Stock <span>Flow</span></h3>

    <a href="dashboard.php?page=home">🏠 Home</a>

    <?php if ($role === 'admin'): ?>
        <a href="dashboard.php?page=users">👥 Manage Users</a>
        <a href="dashboard.php?page=products">📦 Products</a>
        <a href="dashboard.php?page=overview">📊 Overview</a>
        <a href="dashboard.php?page=requests">📋 Requests</a>
        <a href="dashboard.php?page=logs">📜 Logs</a>
        <a href="dashboard.php?page=notifications">🔔 Notifications</a>
        <a href="dashboard.php?page=reports">📜 Reports</a>
    <?php endif; ?>

    <?php if ($role === 'manager'): ?>
        <a href="dashboard.php?page=products">📦 Products</a>
        <a href="dashboard.php?page=overview">📊 Overview</a>
        <a href="dashboard.php?page=requests">📋 Requests</a>
        <a href="dashboard.php?page=notifications">🔔 Notifications</a>
        <a href="dashboard.php?page=logs">📜 Activity Logs</a>
    <?php endif; ?>

    <?php if ($role === 'staff'): ?>
        <a href="dashboard.php?page=products">📦 View Products</a>
        <a href="dashboard.php?page=my_requests">📝 My Requests</a>
        <a href="dashboard.php?page=notifications">🔔 Notifications</a>
        <a href="dashboard.php?page=history">📜 Request History</a>

    <?php endif; ?>

  <div class="sidebar-spacer"></div>

<div style="border-top: 1px solid #2a7a4b; margin: 10px 15px;"></div>

<a href="logout.php"
   style="color: #ff4d4d; margin-top: 5px;"
   onclick="return confirm('Are you sure you want to sign out?')">
   🚪 Sign Out
</a>
</div>

<!-- MAIN -->
<div class="main">

    <div class="topbar">
        <div class="topbar-left">
            Welcome, <?= htmlspecialchars($user['username']) ?>
        </div>

        <div class="topbar-right">

            <?php if ($notif_count > 0): ?>
                <a href="dashboard.php?page=notifications"
                   style="color:inherit; text-decoration:none;">
                    🔔 <?= $notif_count ?> Notifications
                </a>
            <?php else: ?>
                <a href="dashboard.php?page=notifications"
                   style="color:inherit; text-decoration:none;">
                    🔔 Notifications
                </a>
            <?php endif; ?>

            | <?= strtoupper($role) ?>
        </div>
    </div>

    <div class="content">

        <?php
        switch ($page) {

            case 'users':
                if ($role === 'admin') include 'role_admin_users.php';
                else echo "Access denied";
                break;

            case 'products':
                if ($role === 'admin') include 'role_admin_product.php';
                elseif ($role === 'manager') include 'role_manager_product.php';
                else include 'role_staff_products.php';
                break;

            case 'requests':
                include 'role_manager_request.php';
                break;

            case 'my_requests':
                include 'role_staff_request.php';
                break;

            case 'overview':
                include 'role_inventory_overview.php';
                break;

            case 'logs':
                include 'role_admin_audit.php';
                break;

            case 'notifications':
                include 'role_notifications.php';
                break;

                case 'history':
    include 'role_staff_history.php';
    break;
    
    case 'reports':
        include "role_admin_reports.php";
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