<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>
<style>
body {
    font-family: Arial;
    background: #0f172a;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background: #1e293b;
    padding: 30px;
    border-radius: 12px;
    width: 350px;
}
h2 { text-align: center; margin-bottom: 20px; }
input {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border-radius: 6px;
    border: none;
}
button {
    width: 100%;
    padding: 12px;
    background: #22c55e;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
}
button:hover { background: #16a34a; }
.msg { margin-bottom: 10px; }
.error { color: #f87171; }
.success { color: #4ade80; }
</style>
</head>
<body>

<div class="container">
    <h2>Create Account</h2>

    <?php if(isset($_SESSION['msg'])): ?>
        <div class="msg <?= $_SESSION['type'] ?>">
            <?= $_SESSION['msg']; unset($_SESSION['msg']); unset($_SESSION['type']); ?>
        </div>
    <?php endif; ?>

    <form action="process_register.php" method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
          <input type="text" name="phonenumber" placeholder="Phone (11 digits)" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button type="submit">Register</button>
    </form>

    <p style="text-align:center; margin-top:10px;">
        Already have account? <a href="login.php">Login</a>
    </p>
</div>

</body>
</html>