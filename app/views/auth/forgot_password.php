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
            --accent-warning: #f59e0b;
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

        .input-field > i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            pointer-events: none;
            z-index: 1;
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
            letter-spacing: 0.5px;
        }

        .input-field input::placeholder { color: var(--text-muted); letter-spacing: normal; }

        .input-field input:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px rgba(204, 27, 43, 0.15);
        }

        .input-field input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .input-field input.field-error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }

        .field-error-msg {
            display: none;
            margin-top: 6px;
            font-size: 12px;
            color: #ef4444;
            align-items: center;
            gap: 5px;
        }

        .field-error-msg.visible {
            display: flex;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            border-radius: 6px;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 15px;
            transition: color 0.2s, background 0.2s;
            z-index: 2;
        }

        .toggle-password:hover {
            color: var(--text-secondary);
            background: rgba(255,255,255,0.06);
        }

        .toggle-password:focus-visible {
            outline: 2px solid var(--accent-red);
            outline-offset: 1px;
        }

        .toggle-password i {
            position: static;
            transform: none;
            font-size: inherit;
            color: inherit;
        }

        .input-field.has-toggle input {
            padding-right: 46px;
        }

        .password-rules {
            margin-top: 8px;
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            flex-wrap: wrap;
            gap: 6px 12px;
        }

        .password-rules span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .password-rules span i {
            font-size: 11px;
            color: var(--text-muted);
        }

        .password-rules span.met {
            color: var(--accent-success);
        }

        .password-rules span.met i {
            color: var(--accent-success);
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

        .btn-login:hover:not(:disabled) {
            background: var(--accent-red-bright);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(204, 27, 43, 0.4);
        }

        .btn-login:active { transform: translateY(0); }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            background: #555;
        }

        .btn-login .spinner { display: none; }
        .btn-login.loading .spinner { display: inline-block; }
        .btn-login.loading .btn-text { display: none; }

        .btn-secondary {
            width: 100%;
            padding: 14px;
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
            margin-top: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            border-color: var(--text-muted);
            color: var(--text-primary);
            background: rgba(255,255,255,0.03);
        }

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

        .alert-box.warning {
            background: #2e2510;
            border: 1px solid #5c4e1a;
            color: var(--accent-warning);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Success view */
        .success-view {
            display: none;
            text-align: center;
        }

        .success-view.visible {
            display: block;
        }

        .success-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #0f2e23;
            border: 1px solid #1a5c3e;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            font-size: 32px;
            color: var(--accent-success);
        }

        .success-view h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .success-view p {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 26px;
            line-height: 1.5;
        }

        #resetFormWrapper.hidden {
            display: none;
        }

        .subtext {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .subtext strong {
            color: var(--text-secondary);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 36px 24px;
                border-radius: 16px;
            }

            .login-logo img {
                width: 72px;
                height: 72px;
            }

            .login-logo h1 {
                font-size: 24px;
            }

            .password-rules {
                font-size: 11px;
            }
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

            <div id="ajax-alert" class="alert-box" style="display:none;"></div>

            <div id="resetFormWrapper">
                <p class="subtext">Enter the OTP sent to your registered email/phone, then create a new password for your account.</p>

                <form id="resetForm" method="POST" action="<?= baseUrl('auth/reset-password') ?>">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                    <div class="form-group">
    <label for="otp">OTP</label>
    <div class="input-field has-toggle">
        <i class="fas fa-shield-halved"></i>
        <input type="password" id="otp" name="otp" placeholder="Enter the OTP" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required>
        <button type="button" class="toggle-password" id="toggleOtp" aria-label="Show OTP">
            <i class="fas fa-eye" id="toggleOtpIcon"></i>
        </button>
    </div>
    <p class="field-error-msg" id="otpErrorMsg">
        <i class="fas fa-exclamation-circle"></i>
        <span id="otpErrorText"></span>
    </p>
</div>

                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <div class="input-field has-toggle">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="newPassword" name="new_password" placeholder="Enter new password" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" id="toggleNewPassword" aria-label="Show password">
                                <i class="fas fa-eye" id="toggleNewPasswordIcon"></i>
                            </button>
                        </div>
                        <p class="field-error-msg" id="newPasswordErrorMsg">
                            <i class="fas fa-exclamation-circle"></i>
                            <span id="newPasswordErrorText"></span>
                        </p>
                        <div class="password-rules" id="passwordRules">
                            <span id="ruleLength"><i class="fas fa-circle"></i> 8+ characters</span>
                            <span id="ruleUpper"><i class="fas fa-circle"></i> Uppercase letter</span>
                            <span id="ruleLower"><i class="fas fa-circle"></i> Lowercase letter</span>
                            <span id="ruleNumber"><i class="fas fa-circle"></i> Number</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="input-field has-toggle">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter new password" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" id="toggleConfirmPassword" aria-label="Show password">
                                <i class="fas fa-eye" id="toggleConfirmPasswordIcon"></i>
                            </button>
                        </div>
                        <p class="field-error-msg" id="confirmPasswordErrorMsg">
                            <i class="fas fa-exclamation-circle"></i>
                            <span id="confirmPasswordErrorText"></span>
                        </p>
                    </div>

                    <button type="submit" class="btn-login" id="resetBtn">
                        <span class="btn-text">Reset Password</span>
                        <span class="spinner"><i class="fas fa-circle-notch fa-spin"></i> Resetting...</span>
                    </button>
                </form>

                <!-- FIX: was baseUrl('auth/login'), a dead route. Now points directly
                     at the real login page file. Change 'login.php' below if your
                     login file has a different name or lives in a different folder. -->
                <a href="<?= baseUrl('login.php') ?>" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>

            <div class="success-view" id="successView">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2>Password Reset Successfull!</h2>
                <p>Your password has been updated. You can now sign in with your new password.</p>
                <!-- FIX: same as above, was baseUrl('auth/login') -->
                <a href="<?= baseUrl('login.php') ?>" class="btn-login" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const resetForm             = document.getElementById('resetForm');
        const resetBtn              = document.getElementById('resetBtn');
        const ajaxAlert             = document.getElementById('ajax-alert');
        const resetFormWrapper      = document.getElementById('resetFormWrapper');
        const successView           = document.getElementById('successView');

        const otpInput              = document.getElementById('otp');
        const newPasswordInput      = document.getElementById('newPassword');
        const confirmPasswordInput  = document.getElementById('confirmPassword');

        const otpErrorMsg           = document.getElementById('otpErrorMsg');
        const otpErrorText          = document.getElementById('otpErrorText');
        const newPasswordErrorMsg   = document.getElementById('newPasswordErrorMsg');
        const newPasswordErrorText  = document.getElementById('newPasswordErrorText');
        const confirmPasswordErrorMsg  = document.getElementById('confirmPasswordErrorMsg');
        const confirmPasswordErrorText = document.getElementById('confirmPasswordErrorText');

        const toggleNewPassword       = document.getElementById('toggleNewPassword');
        const toggleNewPasswordIcon   = document.getElementById('toggleNewPasswordIcon');
        const toggleConfirmPassword   = document.getElementById('toggleConfirmPassword');
        const toggleConfirmPasswordIcon = document.getElementById('toggleConfirmPasswordIcon');
        const toggleOtp              = document.getElementById('toggleOtp');
        const toggleOtpIcon          = document.getElementById('toggleOtpIcon');

        const ruleLength = document.getElementById('ruleLength');
        const ruleUpper  = document.getElementById('ruleUpper');
        const ruleLower  = document.getElementById('ruleLower');
        const ruleNumber = document.getElementById('ruleNumber');

        const PASSWORD_MIN_LENGTH = 8;

        function setupToggle(btn, icon, input) {
            btn.addEventListener('click', () => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
                btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        }

        setupToggle(toggleNewPassword, toggleNewPasswordIcon, newPasswordInput);
        setupToggle(toggleConfirmPassword, toggleConfirmPasswordIcon, confirmPasswordInput);
        setupToggle(toggleOtp, toggleOtpIcon, otpInput);

        function setFieldError(input, msgEl, textEl, message, doFocus = false) {
            input.classList.add('field-error');
            textEl.textContent = message;
            msgEl.classList.add('visible');
            if (doFocus) { input.focus(); if (input.select) input.select(); }
        }

        function clearFieldError(input, msgEl) {
            input.classList.remove('field-error');
            msgEl.classList.remove('visible');
        }

        function clearAllFieldErrors() {
            clearFieldError(otpInput, otpErrorMsg);
            clearFieldError(newPasswordInput, newPasswordErrorMsg);
            clearFieldError(confirmPasswordInput, confirmPasswordErrorMsg);
        }

        otpInput.addEventListener('input', () => clearFieldError(otpInput, otpErrorMsg));
        newPasswordInput.addEventListener('input', () => {
            clearFieldError(newPasswordInput, newPasswordErrorMsg);
            updatePasswordRules();
        });
        confirmPasswordInput.addEventListener('input', () => clearFieldError(confirmPasswordInput, confirmPasswordErrorMsg));

        function updatePasswordRules() {
            const val = newPasswordInput.value;
            toggleRule(ruleLength, val.length >= PASSWORD_MIN_LENGTH);
            toggleRule(ruleUpper, /[A-Z]/.test(val));
            toggleRule(ruleLower, /[a-z]/.test(val));
            toggleRule(ruleNumber, /[0-9]/.test(val));
        }

        function toggleRule(el, met) {
            el.classList.toggle('met', met);
            const icon = el.querySelector('i');
            icon.className = met ? 'fas fa-check-circle' : 'fas fa-circle';
        }

        function passwordMeetsRules(val) {
            return val.length >= PASSWORD_MIN_LENGTH
                && /[A-Z]/.test(val)
                && /[a-z]/.test(val)
                && /[0-9]/.test(val);
        }

        let alertDismissTimer = null;

        function showAlert(type, html, autoDismiss = false) {
            if (alertDismissTimer) { clearTimeout(alertDismissTimer); alertDismissTimer = null; }
            ajaxAlert.className = 'alert-box ' + type;
            ajaxAlert.innerHTML = html;
            ajaxAlert.style.transition = '';
            ajaxAlert.style.opacity   = '1';
            ajaxAlert.style.display   = 'flex';

            if (autoDismiss) {
                alertDismissTimer = setTimeout(() => {
                    ajaxAlert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    ajaxAlert.style.opacity    = '0';
                    ajaxAlert.style.transform  = 'translateY(-6px)';
                    alertDismissTimer = setTimeout(() => {
                        ajaxAlert.style.display   = 'none';
                        ajaxAlert.style.transform = '';
                    }, 400);
                }, 3000);
            }
        }

        function hideAlert() {
            ajaxAlert.style.display = 'none';
        }

        function showSuccess() {
            resetFormWrapper.classList.add('hidden');
            successView.classList.add('visible');
        }

        function validateClientSide() {
            let valid = true;
            clearAllFieldErrors();
            hideAlert();

            const otpVal      = otpInput.value.trim();
            const newPassVal  = newPasswordInput.value;
            const confirmVal  = confirmPasswordInput.value;

            if (!otpVal) {
                setFieldError(otpInput, otpErrorMsg, otpErrorText, 'Please enter the OTP sent to you.', valid);
                valid = false;
            }

            if (!newPassVal) {
                setFieldError(newPasswordInput, newPasswordErrorMsg, newPasswordErrorText, 'Please enter a new password.', valid);
                valid = false;
            } else if (!passwordMeetsRules(newPassVal)) {
                setFieldError(newPasswordInput, newPasswordErrorMsg, newPasswordErrorText, 'Password does not meet the required rules.', valid);
                valid = false;
            }

            if (!confirmVal) {
                setFieldError(confirmPasswordInput, confirmPasswordErrorMsg, confirmPasswordErrorText, 'Please confirm your new password.', valid);
                valid = false;
            } else if (newPassVal && confirmVal !== newPassVal) {
                setFieldError(confirmPasswordInput, confirmPasswordErrorMsg, confirmPasswordErrorText, 'Passwords do not match.', valid);
                valid = false;
            }

            return valid;
        }

        resetForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (resetBtn.disabled) return;

            if (!validateClientSide()) return;

            resetBtn.classList.add('loading');
            resetBtn.disabled = true;

            const formData = new FormData(e.target);

            try {
                const response = await fetch(e.target.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.success) {
                    hideAlert();
                    showSuccess();
                    return;
                }

                if (data.field === 'otp') {
                    setFieldError(otpInput, otpErrorMsg, otpErrorText, data.field_message || 'Invalid or expired OTP.', true);
                } else if (data.field === 'new_password') {
                    setFieldError(newPasswordInput, newPasswordErrorMsg, newPasswordErrorText, data.field_message || 'Password does not meet the required rules.', true);
                } else if (data.field === 'confirm_password') {
                    setFieldError(confirmPasswordInput, confirmPasswordErrorMsg, confirmPasswordErrorText, data.field_message || 'Passwords do not match.', true);
                }

                if (data.message) {
                    showAlert('error', '<i class="fas fa-exclamation-circle alert-icon"></i><span>' + data.message + '</span>');
                }

            } catch (err) {
                showAlert('error', '<i class="fas fa-wifi alert-icon"></i><span>Connection error. Please try again.</span>', true);
            }

            resetBtn.classList.remove('loading');
            resetBtn.disabled = false;
        });
    })();
    </script>
</body>
</html>