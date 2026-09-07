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
            --accent-success: #10b981;
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

        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--accent-red), #8B0000);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(204, 27, 43, 0.35);
        }

        .brand-icon i { color: #fff; font-size: 26px; }

        .brand h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.3px;
        }

        .brand p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .section-sub {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .input-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-field i {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-field input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            background: var(--bg-input);
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .input-field input::placeholder { color: var(--text-muted); }

        .input-field input:focus {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px rgba(204,27,43,0.15);
        }

        .input-field:focus-within i { color: var(--accent-red); }

        .btn-submit {
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

        .btn-submit:hover:not(:disabled) {
            background: var(--accent-red-bright);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(204, 27, 43, 0.4);
        }

        .btn-submit:active { transform: translateY(0); }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            background: #555;
        }

        .btn-submit .spinner { display: none; }
        .btn-submit.loading .spinner { display: inline-block; }
        .btn-submit.loading .btn-text { display: none; }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 24px;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover { color: var(--text-primary); }
        .back-link i { font-size: 12px; }

        .alert-box {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .alert-box .alert-icon { flex-shrink: 0; margin-top: 1px; }

        .alert-box.error {
            background: #2e1414;
            border: 1px solid #5c1a1a;
            color: #ef4444;
        }

        .alert-box.success {
            background: #0f2e23;
            border: 1px solid #1a5c3e;
            color: var(--accent-success);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">

            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1><?= APP_NAME ?></h1>
                <p>Account Recovery</p>
            </div>

            <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert-box <?= e($flash['type']) ?>" id="flash-alert">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> alert-icon"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
            <?php endif; ?>

            <p class="section-title">Forgot your password?</p>
            <p class="section-sub">Enter the email address linked to your account and we'll send you a one-time code to reset your password.</p>

            <form id="forgotForm" method="POST" action="<?= baseUrl('auth/sendotp') ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="form-group">
                    <label for="fp-email">Email Address</label>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="fp-email" name="email" placeholder="Enter your registered email" required autocomplete="email">
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-paper-plane" style="margin-right:6px;"></i>Send OTP</span>
                    <span class="spinner"><i class="fas fa-circle-notch fa-spin"></i> Sending...</span>
                </button>
            </form>

            <a href="<?= baseUrl('auth/login') ?>" class="back-link" id="backToLoginLink">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>

        </div>
    </div>

    <script>
    document.getElementById('forgotForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });

    const flashAlert = document.getElementById('flash-alert');
    if (flashAlert) setTimeout(() => {
        flashAlert.style.transition = 'opacity 0.4s ease';
        flashAlert.style.opacity = '0';
        setTimeout(() => flashAlert.remove(), 400);
    }, 4000);
    </script>
</body>
</html>
