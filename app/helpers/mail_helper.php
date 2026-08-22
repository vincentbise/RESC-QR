<?php

function sendPasswordResetEmail($recipientEmail, $recipientName, $resetUrl) {
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $safeName = e($recipientName ?: 'User');
    $safeUrl = filter_var($resetUrl, FILTER_SANITIZE_URL);
    $subject = APP_NAME . ' Password Reset Request';

    $message = '
    <html>
    <body style="font-family: Arial, sans-serif; color: #111;">
        <p>Hello ' . $safeName . ',</p>
        <p>We received a request to reset your password for your ' . e(APP_NAME) . ' account.</p>
        <p>
            <a href="' . e($safeUrl) . '" style="display:inline-block;padding:12px 18px;background:#cc1b2b;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;">
                Reset Password
            </a>
        </p>
        <p>If the button does not work, open this link:</p>
        <p><a href="' . e($safeUrl) . '">' . e($safeUrl) . '</a></p>
        <p>This link will expire in 30 minutes and can only be used once.</p>
        <p>If you did not request this, you can ignore this message.</p>
    </body>
    </html>';

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: no-reply@resc-qr.local'
    ];

    return @mail($recipientEmail, $subject, $message, implode("\r\n", $headers));
}
