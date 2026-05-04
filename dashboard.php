<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // same as logout.php redirect
    exit();
}

$user = $_SESSION['user'];
$page = $_GET['page'] ?? 'home';
$role = $user['role'];
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
    --green: #22c55e;
    --bg: #0f172a;
    --card: #1e293b;
    --text: #f1f5f9;
    --sub: #94a3b8;
    --border: rgba(255,255,255,0.06);
    --spotify: #1db954;
    --spotify-hover: #1ed760;
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
    transition: 0.2s;
}

a:hover {
    color: var(--green);
    background: rgba(34,197,94,0.08);
}

.sidebar-spacer { flex: 1; }

/* Spotify logout button */
.logout-link {
    display: flex !important;
    align-items: center;
    gap: 10px;
    color: #fff !important;
    background: var(--spotify) !important;
    padding: 11px 16px;
    border-radius: 50px;
    font-size: .88rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: background .2s, transform .15s;
    cursor: pointer;
}

.logout-link:hover {
    background: var(--spotify-hover) !important;
    color: #fff !important;
    transform: scale(1.03);
}

.logout-link:active { transform: scale(0.97); }

.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

.topbar {
    padding: 16px 28px;
    background: #000;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.topbar-left { font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700; }

.topbar-right {
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--green);
    background: rgba(34,197,94,0.08);
    border: 1px solid rgba(34,197,94,0.15);
    padding: 5px 14px;
    border-radius: 20px;
}

.content { padding: 28px; flex: 1; overflow-y: auto; }
.content::-webkit-scrollbar { width: 5px; }
.content::-webkit-scrollbar-track { background: transparent; }
.content::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

/* Modal */
.logout-modal {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.logout-box {
    background: #1e293b;
    padding: 32px 28px;
    width: 300px;
    border-radius: 16px;
    text-align: center;
    border: 1px solid rgba(255,255,255,0.08);
}

.logout-icon {
    width: 48px; height: 48px;
    border-radius: 50%;
    background: rgba(239,68,68,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 22px;
}

.logout-box h2 { margin-bottom: 8px; font-size: 18px; font-weight: 600; }
.logout-box p { color: #94a3b8; margin-bottom: 24px; font-size: 13px; line-height: 1.6; }

.logout-buttons { display: flex; gap: 10px; }

.logout-buttons button {
    flex: 1;
    border: none;
    padding: 11px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, transform .15s;
}

.logout-buttons button:active { transform: scale(0.97); }

.no-btn {
    background: #0f172a;
    color: #94a3b8;
    border: 1px solid rgba(255,255,255,0.08) !important;
}

.no-btn:hover { background: #1e293b; color: #f1f5f9; }
.yes-btn { background: var(--spotify); color: #fff; }
.yes-btn:hover { background: var(--spotify-hover); }
</style>
</head>

<body>

<div class="sidebar">
    <h3>Stock <span>Flow</span></h3>
    <a href="dashboard.php?page=home">🏠 Home</a>

    <?php if ($role == 'admin') { ?>
        <a href="dashboard.php?page=users">👥 Manage Users</a>
        <a href="dashboard.php?page=products">📦 Products</a>
        <a href="dashboard.php?page=requests">📋 Requests</a>
    <?php } ?>

    <?php if ($role == 'staff') { ?>
        <a href="dashboard.php?page=my_requests">📝 My Requests</a>
    <?php } ?>

    <div class="sidebar-spacer"></div>
    <a href="#" class="logout-link" onclick="openLogout()">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <div class="topbar-left">Welcome, <?= htmlspecialchars($user['username']) ?></div>
        <div class="topbar-right"><?= htmlspecialchars($role) ?></div>
    </div>

    <div class="content">
        <?php
        switch ($page) {
            case 'users':       include 'role_admin_users.php';  break;
            case 'products':    include 'role_admin_product.php'; break;
            case 'requests':    include 'role_admin_request.php'; break;
            case 'my_requests': include 'role_staff_request.php'; break;
            default:            include 'role_home.php';
        }
        ?>
    </div>
</div>

<!-- LOGOUT MODAL -->
<div class="logout-modal" id="logoutModal" style="display:none;">
    <div class="logout-box">
        <div class="logout-icon">🚪</div>
        <h2>Log out?</h2>
        <p>Are you sure you want to log out of StockFlow?</p>
        <div class="logout-buttons">
            <button class="no-btn" onclick="closeLogout()">No, stay</button>
            <button class="yes-btn" onclick="window.location.href='logout.php'">Yes, logout</button>
        </div>
    </div>
</div>

<script>
function openLogout(){ document.getElementById("logoutModal").style.display = "flex"; }
function closeLogout(){ document.getElementById("logoutModal").style.display = "none"; }
</script>

</body>
</html>