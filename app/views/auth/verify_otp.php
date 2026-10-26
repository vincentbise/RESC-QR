<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP — <?= APP_NAME ?></title>
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

        .section-sub strong { color: var(--text-primary); }

        /* ─── OTP Input Boxes ─────────────────────────── */
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
        }

        .otp-input {
            width: 48px;
            height: 56px;
            background: var(--bg-input);
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 22px;
            font-weight: 700;
            font-family: 'Inter', monospace;
            text-align: center;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
            caret-color: var(--accent-red);
        }

        .otp-input:focus {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px rgba(204,27,43,0.15);
            transform: translateY(-2px);
        }

        .otp-input.filled {
            border-color: var(--accent-success);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
        }

        .otp-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.15);
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        /* Hidden real input for form submission */
        .otp-hidden { position: absolute; opacity: 0; pointer-events: none; }

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

        /* ─── Resend & Timer ────────────────────────── */
        .resend-row {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .resend-row button {
            background: none;
            border: none;
            color: var(--accent-red-bright);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: color 0.2s;
        }

        .resend-row button:hover { color: #fff; }
        .resend-row button:disabled {
            color: var(--text-muted);
            cursor: not-allowed;
        }

        .countdown {
            display: inline-block;
            font-variant-numeric: tabular-nums;
            min-width: 28px;
        }

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

        /* ─── Expiry progress bar ─────────────────── */
        .expiry-bar-wrap {
            margin-bottom: 24px;
        }

        .expiry-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .expiry-bar {
            height: 4px;
            background: var(--bg-input);
            border-radius: 4px;
            overflow: hidden;
        }

        .expiry-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-success), var(--accent-red));
            border-radius: 4px;
            transition: width 1s linear;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">

            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h1><?= APP_NAME ?></h1>
                <p>Email Verification</p>
            </div>

            <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert-box <?= e($flash['type']) ?>" id="flash-alert">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> alert-icon"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
            <?php endif; ?>

            <p class="section-title">Enter verification code</p>
            <p class="section-sub">
                We've sent a <?= OTP_LENGTH ?>-digit code to
                <strong><?= e(substr($email, 0, 3) . '***' . substr($email, strpos($email, '@'))) ?></strong>
            </p>

            <!-- Expiry countdown bar -->
            <div class="expiry-bar-wrap" id="expiryWrap">
                <div class="expiry-label">
                    <span>Code expires in</span>
                    <span id="expiryTime"><?= (int)(OTP_EXPIRY / 60) ?>:00</span>
                </div>
                <div class="expiry-bar">
                    <div class="expiry-bar-fill" id="expiryFill" style="width:100%"></div>
                </div>
            </div>

            <form id="otpForm" method="POST" action="<?= baseUrl('auth/verifyotp') ?>">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="otp" id="otpHidden" class="otp-hidden" maxlength="<?= OTP_LENGTH ?>">

                <div class="otp-container" id="otpContainer">
                    <?php for ($i = 0; $i < OTP_LENGTH; $i++): ?>
                    <input type="text"
                           class="otp-input"
                           maxlength="1"
                           inputmode="numeric"
                           pattern="[0-9]"
                           data-index="<?= $i ?>"
                           autocomplete="one-time-code"
                           aria-label="Digit <?= $i + 1 ?>">
                    <?php endfor; ?>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn" disabled>
                    <span class="btn-text"><i class="fas fa-check-circle" style="margin-right:6px;"></i>Verify Code</span>
                    <span class="spinner"><i class="fas fa-circle-notch fa-spin"></i> Verifying...</span>
                </button>
            </form>

            <!-- Resend OTP -->
            <div class="resend-row">
                <span id="resendText">Didn't receive the code? </span>
                <form method="POST" action="<?= baseUrl('auth/resendotp') ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <button type="submit" id="resendBtn" disabled>
                        Resend Code <span class="countdown" id="resendTimer">(60s)</span>
                    </button>
                </form>
            </div>

            <a href="<?= baseUrl('auth/forgotpassword') ?>" class="back-link" id="backLink">
                <i class="fas fa-arrow-left"></i> Start Over
            </a>

        </div>
    </div>

    <script>
    (function() {
        const OTP_LENGTH = <?= OTP_LENGTH ?>;
        const OTP_EXPIRY = <?= OTP_EXPIRY ?>;
        const inputs = document.querySelectorAll('.otp-input');
        const hidden = document.getElementById('otpHidden');
        const form = document.getElementById('otpForm');
        const submitBtn = document.getElementById('submitBtn');

        // ─── OTP input logic ─────────────────────────
        function updateHidden() {
            let val = '';
            inputs.forEach(i => val += i.value);
            hidden.value = val;
            submitBtn.disabled = val.length !== OTP_LENGTH;

            inputs.forEach(i => {
                i.classList.toggle('filled', i.value !== '');
                i.classList.remove('error');
            });
        }

        inputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                const val = e.target.value.replace(/\D/g, '');
                e.target.value = val.charAt(0) || '';
                updateHidden();
                if (val && idx < OTP_LENGTH - 1) inputs[idx + 1].focus();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    inputs[idx - 1].focus();
                    inputs[idx - 1].value = '';
                    updateHidden();
                }
                if (e.key === 'ArrowLeft' && idx > 0) inputs[idx - 1].focus();
                if (e.key === 'ArrowRight' && idx < OTP_LENGTH - 1) inputs[idx + 1].focus();
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, OTP_LENGTH);
                paste.split('').forEach((ch, i) => {
                    if (inputs[i]) inputs[i].value = ch;
                });
                updateHidden();
                const next = Math.min(paste.length, OTP_LENGTH - 1);
                inputs[next].focus();
            });

            input.addEventListener('focus', () => input.select());
        });

        // Auto-focus first input
        inputs[0].focus();

        // Submit loading state
        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // ─── Resend cooldown ─────────────────────────
        const resendBtn = document.getElementById('resendBtn');
        const resendTimer = document.getElementById('resendTimer');
        let resendCooldown = 60;

        const resendInterval = setInterval(() => {
            resendCooldown--;
            if (resendCooldown <= 0) {
                clearInterval(resendInterval);
                resendBtn.disabled = false;
                resendTimer.textContent = '';
            } else {
                resendTimer.textContent = '(' + resendCooldown + 's)';
            }
        }, 1000);

        // ─── OTP expiry countdown ────────────────────
        const expiryFill = document.getElementById('expiryFill');
        const expiryTime = document.getElementById('expiryTime');
        const expiryWrap = document.getElementById('expiryWrap');
        const sentAt = <?= isset($_SESSION['otp_sent_at']) ? $_SESSION['otp_sent_at'] : 'Math.floor(Date.now()/1000)' ?>;
        const now = Math.floor(Date.now() / 1000);
        let remaining = Math.max(0, OTP_EXPIRY - (now - sentAt));

        function updateExpiry() {
            if (remaining <= 0) {
                expiryTime.textContent = 'Expired';
                expiryFill.style.width = '0%';
                submitBtn.disabled = true;
                inputs.forEach(i => { i.disabled = true; i.classList.add('error'); });
                return;
            }
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            expiryTime.textContent = m + ':' + String(s).padStart(2, '0');
            expiryFill.style.width = ((remaining / OTP_EXPIRY) * 100) + '%';
            remaining--;
            setTimeout(updateExpiry, 1000);
        }
        updateExpiry();

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
