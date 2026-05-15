<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>OTP Verification — StockFlow IMS</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
*, *::before, *::after{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{
    --green:#22c55e;
    --green-hover:#16a34a;

    --bg:#0f172a;
    --card:#1e293b;
    --card2:#111827;

    --text:#f8fafc;
    --sub:#94a3b8;

    --error-bg:#3b1118;
    --error-border:#7f1d1d;
    --error-text:#fecaca;
}

body{
    font-family:'DM Sans',sans-serif;
    background:var(--bg);
    color:var(--text);

    min-height:100vh;

    display:flex;
    align-items:center;
    justify-content:center;

    overflow:hidden;
    position:relative;
}

/* Background glow */
body::before{
    content:'';
    position:absolute;

    width:700px;
    height:700px;

    background:
    radial-gradient(circle,
    rgba(34,197,94,0.12) 0%,
    transparent 65%);

    top:50%;
    left:50%;

    transform:translate(-50%,-50%);
}

/* Rings */
.ring{
    position:absolute;
    border-radius:50%;
    border:1px solid rgba(34,197,94,0.06);

    top:50%;
    left:50%;

    transform:translate(-50%,-50%);
}

.ring1{
    width:420px;
    height:420px;
}

.ring2{
    width:620px;
    height:620px;
}

.ring3{
    width:820px;
    height:820px;
}

/* Container */
.container{
    width:100%;
    max-width:430px;

    position:relative;
    z-index:2;
}

/* Brand */
.brand{
    text-align:center;
    margin-bottom:28px;
}

.brand-logo{
    width:60px;
    height:60px;

    margin:auto auto 14px;

    border-radius:16px;

    background:var(--green);

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:1.5rem;
}

.brand-title{
    font-family:'Syne',sans-serif;
    font-size:.8rem;
    font-weight:700;

    letter-spacing:4px;
    text-transform:uppercase;

    color:var(--sub);
}

/* Card */
.card{
    background:var(--card);

    border-radius:22px;

    padding:42px 36px;

    border:1px solid rgba(255,255,255,0.06);

    box-shadow:
    0 25px 60px rgba(0,0,0,0.45);

    position:relative;
    overflow:hidden;
}

.card::before{
    content:'OTP';

    position:absolute;
    top:-10px;
    right:-8px;

    font-family:'Syne',sans-serif;
    font-size:5.5rem;
    font-weight:800;

    color:var(--green);

    opacity:.05;
}

/* Header */
.tag{
    font-size:.65rem;
    font-weight:700;

    letter-spacing:3px;
    text-transform:uppercase;

    color:var(--green);

    margin-bottom:10px;
}

.title{
    font-family:'Syne',sans-serif;
    font-size:1.9rem;
    font-weight:800;

    margin-bottom:8px;
}

.subtitle{
    font-size:.85rem;
    color:var(--sub);

    line-height:1.6;

    margin-bottom:28px;
}

/* Error */
.error{
    background:var(--error-bg);
    border:1px solid var(--error-border);

    color:var(--error-text);

    padding:12px 14px;

    border-radius:10px;

    font-size:.82rem;

    margin-bottom:18px;
}

/* Input */
.label{
    display:block;

    font-size:.68rem;
    font-weight:700;

    letter-spacing:2px;
    text-transform:uppercase;

    color:var(--sub);

    margin-bottom:10px;
}

.otp-input{
    width:100%;

    padding:18px;

    border:none;
    outline:none;

    border-radius:14px;

    background:var(--card2);

    color:var(--green);

    font-family:'Syne',sans-serif;
    font-size:1.8rem;
    font-weight:700;

    letter-spacing:12px;

    text-align:center;

    margin-bottom:22px;

    transition:.2s;
}

.otp-input:focus{
    border:1px solid var(--green);
}

.otp-input::placeholder{
    color:#334155;
}

/* Button */
.btn{
    width:100%;

    padding:15px;

    border:none;
    border-radius:12px;

    background:var(--green);

    color:#000;

    font-size:.92rem;
    font-weight:700;

    cursor:pointer;

    transition:.2s;
}

.btn:hover{
    background:var(--green-hover);
}

/* Hint */
.hint{
    margin-top:18px;

    padding:14px;

    border-radius:10px;

    background:rgba(34,197,94,0.05);

    border:1px solid rgba(34,197,94,0.08);

    color:var(--sub);

    font-size:.75rem;

    line-height:1.5;
}
</style>
</head>

<body>

<div class="ring ring1"></div>
<div class="ring ring2"></div>
<div class="ring ring3"></div>

<div class="container">

    <div class="brand">
        <div class="brand-logo">📦</div>
        <div class="brand-title">StockFlow </div>
    </div>

    <div class="card">

        <div class="tag">Security Verification</div>

        <div class="title">Enter OTP</div>

        <div class="subtitle">
            We sent a <strong>6-digit verification code</strong>
            to your registered email.
        </div>

        <?php if(isset($_SESSION['otp_error'])): ?>

            <div class="error">
                <?php
                echo $_SESSION['otp_error'];
                unset($_SESSION['otp_error']);
                ?>
            </div>

        <?php endif; ?>

        <form action="otp_verification_process.php" method="POST">

            <label class="label">Verification Code</label>

            <input
                type="text"
                name="otp"
                maxlength="6"
                class="otp-input"
                placeholder="------"
                required
            >

            <button type="submit" class="btn">
                Verify & Continue →
            </button>

        </form>

        <div class="hint">
            Didn’t receive the code?
            Check your spam folder or wait a few seconds before trying again.
        </div>

    </div>

</div>

</body>
</html>