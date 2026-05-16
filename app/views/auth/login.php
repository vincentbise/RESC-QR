<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0e1525;
            --bg-card: #1a2744;
            --bg-input: #111a2c;
            --border-color: #243352;
            --text-primary: #f0f2f7;
            --text-secondary: #b0b8cc;
            --text-muted: #6b7a96;
            --accent-red: #cc1b2b;
            --accent-red-bright: #e52535;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .login-card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 48px 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-red), var(--accent-red-bright), #8B0000);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 36px;
        }

        .login-logo img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-bottom: 16px;
        }

        .login-logo h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.5px;
        }

        .login-logo p {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .input-field {
            position: relative;
        }

        .input-field i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
        }

        .input-field input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        .input-field input::placeholder {
            color: var(--text-muted);
        }

        .input-field input:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px rgba(204, 27, 43, 0.15);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--accent-red);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            margin-top: 8px;
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            background: var(--accent-red-bright);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(204, 27, 43, 0.4);
        }

        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .btn-login .spinner { display: none; }
        .btn-login.loading .spinner { display: inline-block; }
        .btn-login.loading .btn-text { display: none; }

        .alert-box {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .alert-box.error {
            background: #2e1414;
            border: 1px solid #5c1a1a;
            color: #ef4444;
        }

        .alert-box.success {
            background: #0f2e23;
            border: 1px solid #1a5c3e;
            color: #10b981;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <img src="<?= publicUrl('img/logo.png') ?>" alt="<?= APP_NAME ?> Logo">
                <h1><?= APP_NAME ?></h1>
                <p><?= APP_FULL_NAME ?></p>
            </div>

            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert-box <?= e($flash['type']) ?>">
                    <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?>"></i>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div id="ajax-alert" class="alert-box" style="display:none;"></div>

            <form id="loginForm" method="POST" action="<?= baseUrl('auth/authenticate') ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="form-group">
                    <label for="email">Username / Email</label>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">Sign In</span>
                    <span class="spinner"><i class="fas fa-circle-notch fa-spin"></i> Signing in...</span>
                </button>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const loginBtn = document.getElementById('loginBtn');
        const ajaxAlert = document.getElementById('ajax-alert');
        loginBtn.classList.add('loading');
        loginBtn.disabled = true;
        ajaxAlert.style.display = 'none';

        const formData = new FormData(e.target);

        try {
            const response = await fetch(e.target.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            if (data.success) {
                ajaxAlert.className = 'alert-box success';
                ajaxAlert.innerHTML = '<i class="fas fa-check-circle"></i> Login successful! Redirecting...';
                ajaxAlert.style.display = 'flex';
                setTimeout(() => {
                    window.location.href = '<?= BASE_URL ?>' + data.redirect;
                }, 500);
            } else {
                ajaxAlert.className = 'alert-box error';
                ajaxAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                ajaxAlert.style.display = 'flex';
                loginBtn.classList.remove('loading');
                loginBtn.disabled = false;
            }
        } catch (err) {
            ajaxAlert.className = 'alert-box error';
            ajaxAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i> Connection error. Please try again.';
            ajaxAlert.style.display = 'flex';
            loginBtn.classList.remove('loading');
            loginBtn.disabled = false;
        }
    });
    </script>
</body>
</html>
