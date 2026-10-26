<?php

// ─── Gmail SMTP Configuration ─────────────────────────────────────────
// To generate an App Password:
// 1. Go to https://myaccount.google.com/security
// 2. Enable 2-Step Verification
// 3. Go to App Passwords → Generate one for "Mail"
// 4. Paste the 16-character password below (no spaces)

define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       465);
define('MAIL_USERNAME',   'qrresc@gmail.com');
define('MAIL_PASSWORD',   'hmcejtxbyyghykcl');
define('MAIL_FROM_EMAIL', 'qrresc@gmail.com');
define('MAIL_FROM_NAME',  APP_NAME);
define('MAIL_ENCRYPTION', 'smtps');                      // 'smtps' = port 465, 'tls' = port 587

// OTP Settings
define('OTP_LENGTH',   6);
define('OTP_EXPIRY',   300);  // 5 minutes in seconds
