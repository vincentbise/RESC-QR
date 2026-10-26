<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — <?= APP_NAME ?></title>
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

        .input-field i.field-icon {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-field input {
            width: 100%;
            padding: 13px 44px 13px 42px;
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

        .input-field:focus-within i.field-icon { color: var(--accent-red); }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
            transition: color 0.2s;
            padding: 4px;
        }

        .toggle-password:hover { color: var(--text-primary); }

        /* Strength meter */
        .strength-meter {
            display: flex;
            gap: 4px;
            margin-top: 8px;
        }

        .strength-meter .bar {
            height: 3px;
            flex: 1;
            background: var(--border-color);
            border-radius: 3px;
            transition: background 0.3s;
        }

        .strength-label {
            font-size: 11px;
            margin-top: 4px;
            color: var(--text-muted);
            transition: color 0.3s;
        }

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

        .password-requirements {
            margin-top: 8px;
            padding: 10px 14px;
            background: var(--bg-input);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .req-item {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
            transition: color 0.2s;
        }

        .req-item:last-child { margin-bottom: 0; }
        .req-item i { font-size: 10px; width: 14px; text-align: center; }
        .req-item.met { color: var(--accent-success); }
        .req-item.met i { color: var(--accent-success); }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">

            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-lock-open"></i>
                </div>
                <h1><?= APP_NAME ?></h1>
                <p>Set New Password</p>
            </div>

            <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert-box <?= e($flash['type']) ?>" id="flash-alert">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> alert-icon"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
            <?php endif; ?>

            <p class="section-title">Create a new password</p>
            <p class="section-sub">Your identity has been verified. Please choose a strong new password for your account.</p>

            <form id="resetForm" method="POST" action="<?= baseUrl('auth/resetpassword') ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="input-field">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Enter new password" required minlength="8" autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="strength-meter" id="strengthMeter">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                    <div class="strength-label" id="strengthLabel"></div>
                    <div class="password-requirements" id="requirements">
                        <div class="req-item" id="reqLength"><i class="fas fa-circle"></i> At least 8 characters</div>
                        <div class="req-item" id="reqUpper"><i class="fas fa-circle"></i> An uppercase letter</div>
                        <div class="req-item" id="reqLower"><i class="fas fa-circle"></i> A lowercase letter</div>
                        <div class="req-item" id="reqNumber"><i class="fas fa-circle"></i> A number</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <div class="input-field">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm new password" required minlength="8" autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="password_confirm" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-save" style="margin-right:6px;"></i>Reset Password</span>
                    <span class="spinner"><i class="fas fa-circle-notch fa-spin"></i> Resetting...</span>
                </button>
            </form>

            <a href="<?= baseUrl('auth/login') ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>

        </div>
    </div>

    <script>
    (function() {
        const form = document.getElementById('resetForm');
        const pwd = document.getElementById('password');
        const confirm = document.getElementById('password_confirm');
        const submitBtn = document.getElementById('submitBtn');
        const bars = document.querySelectorAll('#strengthMeter .bar');
        const label = document.getElementById('strengthLabel');

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                btn.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        });

        // Password strength checker
        function checkStrength(val) {
            let score = 0;
            const checks = {
                reqLength: val.length >= 8,
                reqUpper: /[A-Z]/.test(val),
                reqLower: /[a-z]/.test(val),
                reqNumber: /[0-9]/.test(val),
            };

            Object.entries(checks).forEach(([id, met]) => {
                const el = document.getElementById(id);
                el.classList.toggle('met', met);
                el.querySelector('i').className = met ? 'fas fa-check' : 'fas fa-circle';
                if (met) score++;
            });

            const colors = ['#ef4444', '#f97316', '#eab308', '#10b981'];
            const labels = ['Weak', 'Fair', 'Good', 'Strong'];

            bars.forEach((bar, i) => {
                bar.style.background = i < score ? colors[score - 1] : 'var(--border-color)';
            });

            label.textContent = val ? labels[score - 1] || '' : '';
            label.style.color = val ? colors[score - 1] || '' : '';

            return score;
        }

        pwd.addEventListener('input', () => checkStrength(pwd.value));

        // Form submit
        form.addEventListener('submit', function(e) {
            if (pwd.value !== confirm.value) {
                e.preventDefault();
                confirm.style.borderColor = '#ef4444';
                confirm.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
                return;
            }
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        confirm.addEventListener('input', () => {
            confirm.style.borderColor = '';
            confirm.style.boxShadow = '';
        });

        // Flash auto-dismiss
        const flashAlert = document.getElementById('flash-alert');
        if (flashAlert) setTimeout(() => {
            flashAlert.style.transition = 'opacity 0.4s ease';
            flashAlert.style.opacity = '0';
            setTimeout(() => flashAlert.remove(), 400);
        }, 4000);
    })();
    </script>
</body>
</html>
