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
   FIX ROLE SAFETY (IMPORTANT)
========================= */
$role = strtolower(trim($user['role'] ?? 'staff'));

/* OPTIONAL SAFETY: normalize DB value on the fly */
if (!in_array($role, ['admin', 'manager', 'staff'])) {
    $role = 'staff';
}

/* update session */
$_SESSION['user'] = $user;
$_SESSION['user']['role'] = $role;

$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory System</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
/* YOUR DESIGN — UNCHANGED */
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

/* main */
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
        <a href="dashboard.php?page=products">📦 Products (View Only)</a>
        <a href="dashboard.php?page=requests">📋 Requests (View Only)</a>
    <?php endif; ?>

    <?php if ($role === 'manager'): ?>
        <a href="dashboard.php?page=products">📦 Products</a>
        <a href="dashboard.php?page=requests">📋 Requests</a>
    <?php endif; ?>

    <?php if ($role === 'staff'): ?>
        <a href="dashboard.php?page=my_requests">📝 My Requests</a>
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
        <div class="topbar-right">
            <?= htmlspecialchars($role) ?>
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
                    include 'role_admin_products_view.php';
                } elseif ($role === 'manager') {
                    include 'role_manager_product.php';
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

            default:
                include 'role_home.php';
        }
        ?>

    </div>
</div>

</body>
</html>