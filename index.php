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

/* MAIN CONTAINER */
.container {
    text-align: center;
    max-width: 500px;
}

/* LOGO */
.logo {
    font-size: 3rem;
    margin-bottom: 20px;
}

/* TITLE */
.title {
    font-family: 'Syne';
    font-size: 3rem;
    font-weight: 800;
}

.title span {
    color: var(--green);
}

/* DESCRIPTION */
.desc {
    margin-top: 15px;
    color: var(--sub);
    line-height: 1.6;
}

/* BUTTONS */
.actions {
    margin-top: 30px;
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn {
    padding: 14px 20px;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
    transition: 0.2s;
}

/* LOGIN BUTTON */
.btn-login {
    background: var(--green);
    color: black;
}

.btn-login:hover {
    background: #16a34a;
}

/* REGISTER BUTTON */
.btn-register {
    background: transparent;
    border: 2px solid var(--green);
    color: var(--green);
}

.btn-register:hover {
    background: var(--green);
    color: black;
}

.footer {
    margin-top: 40px;
    font-size: 0.8rem;
    color: #64748b;
}
</style>
</head>

<body>

<div class="container">
    <div class="logo">📦</div>

    <div class="title">
        Stock <span>Flow</span>
    </div>

    <p class="desc">
        Manage your products, track inventory, and monitor your business easily.
        Fast. Simple. Efficient.
    </p>

    <div class="actions">
        <a href="login.php" class="btn btn-login">Login</a>
        <a href="register.php" class="btn btn-register">Create Account</a>
    </div>

    </div>
</div>

</body>
</html>