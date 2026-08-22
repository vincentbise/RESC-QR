<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --bg-primary:#0e1525; --bg-card:#1a2744; --bg-input:#111a2c; --border-color:#243352; --text-primary:#f0f2f7; --text-secondary:#b0b8cc; --text-muted:#6b7a96; --accent-red:#cc1b2b; --accent-red-bright:#e52535; --accent-success:#10b981; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg-primary); color:var(--text-primary); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card-wrap { width:100%; max-width:460px; padding:20px; }
        .card { background:var(--bg-card); border-radius:20px; padding:42px 36px; border:1px solid var(--border-color); box-shadow:0 25px 60px rgba(0,0,0,0.5); }
        h1 { font-size:26px; margin-bottom:8px; }
        p.desc { color:var(--text-secondary); margin-bottom:24px; font-size:14px; line-height:1.55; }
        .form-group { margin-bottom:18px; }
        label { display:block; margin-bottom:8px; font-weight:600; font-size:13px; color:var(--text-secondary); }
        input { width:100%; padding:14px 16px; background:var(--bg-input); border:1px solid var(--border-color); border-radius:10px; color:var(--text-primary); font-size:15px; }
        input:focus { outline:none; border-color:var(--accent-red); box-shadow:0 0 0 3px rgba(204,27,43,0.15); }
        .btn { width:100%; padding:14px 16px; border:none; border-radius:10px; background:var(--accent-red); color:#fff; font-weight:700; cursor:pointer; }
        .btn:hover { background:var(--accent-red-bright); }
        .links { margin-top:16px; text-align:center; }
        .links a { color:var(--text-secondary); text-decoration:none; font-size:13px; }
        .links a:hover { color:#fff; }
        .alert { padding:12px 14px; border-radius:10px; margin-bottom:16px; font-size:14px; }
        .alert.error { background:#2e1414; border:1px solid #5c1a1a; color:#ef4444; }
        .alert.success { background:#0f2e23; border:1px solid #1a5c3e; color:var(--accent-success); }
    </style>
</head>
<body>
    <div class="card-wrap">
        <div class="card">
            <h1>Forgot Password</h1>
            <p class="desc">Enter your registered Gmail address and we’ll send a password reset link if an account exists.</p>

            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= baseUrl('auth/sendResetLink') ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <div class="form-group">
                    <label for="email">Registered Gmail Address</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required autocomplete="email">
                </div>
                <button type="submit" class="btn">Send Reset Link</button>
            </form>

            <div class="links">
                <a href="<?= baseUrl('auth/login') ?>"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
