<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Stock Flow</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --green: #22c55e;
    --bg: #0f172a;
    --card: #1e293b;
    --text: #f1f5f9;
    --sub: #94a3b8;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.container {
    width: 380px;
    background: var(--card);
    padding: 40px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 15px 40px rgba(0,0,0,0.4);
}

.logo { font-size: 2.5rem; margin-bottom: 10px; }

.title {
    font-family: 'Syne';
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 5px;
}

.title span { color: var(--green); }
.subtitle { color: var(--sub); font-size: 0.9rem; margin-bottom: 25px; }

input {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border: none;
    border-radius: 8px;
    background: #0b1220;
    color: var(--text);
    outline: none;
}

input:focus { border: 1px solid var(--green); }

button {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    border: none;
    border-radius: 8px;
    background: var(--green);
    color: black;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
}

button:hover { background: #16a34a; }

.footer { margin-top: 15px; font-size: 0.85rem; color: var(--sub); }
.footer a { color: var(--green); text-decoration: none; }

.alert {
    padding: 10px 14px;
    margin-bottom: 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    text-align: left;
    line-height: 1.5;
}

.alert-error { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
.alert-success { background: rgba(34,197,94,0.12); color: #4ade80; border: 1px solid rgba(34,197,94,0.2); }
</style>
</head>

<body>

<div class="container">

    <div class="logo">📦</div>
    <div class="title">Stock <span>Flow</span></div>
    <div class="subtitle">Login to your inventory system</div>

    <?php
    if (!empty($_SESSION['login_error'])) {
        echo "<div class='alert alert-error'>" . htmlspecialchars($_SESSION['login_error']) . "</div>";
        unset($_SESSION['login_error']);
    }

    if (!empty($_SESSION['msg'])) {
        $class = ($_SESSION['type'] == 'success') ? 'alert-success' : 'alert-error';
        echo "<div class='alert {$class}'>" . htmlspecialchars($_SESSION['msg']) . "</div>";
        unset($_SESSION['msg']);
        unset($_SESSION['type']);
    }
    ?>

    <form action="process_login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="footer">
        Don't have an account? <a href="register.php">Create one</a>
    </div>

</div>

</body>
</html>