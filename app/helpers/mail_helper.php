<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an OTP verification email using PHPMailer + Gmail SMTP.
 *
 * @param string $toEmail   Recipient email address
 * @param string $otpCode   The OTP code to send
 * @return bool             True on success
 * @throws Exception        On mail failure
 */
function sendOtpEmail($toEmail, $otpCode) {
    $mail = new PHPMailer(true);

    try {
        // Encoding
        $mail->CharSet = 'UTF-8';
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->Port       = MAIL_PORT;

        if (MAIL_ENCRYPTION === 'smtps') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = APP_NAME . ' | Password Reset Verification';
        $mail->Body    = buildOtpEmailHtml($otpCode);
        $mail->AltBody = "Your " . APP_NAME . " password reset code is: {$otpCode}. This code expires in " . (OTP_EXPIRY / 60) . " minutes.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        throw $e;
    }
}

/**
 * Generate a cryptographically random numeric OTP.
 *
 * @param int $length  Number of digits
 * @return string      Zero-padded OTP string
 */
function generateOTP($length = null) {
    $length = $length ?? OTP_LENGTH;
    $min = (int) str_pad('1', $length, '0');
    $max = (int) str_pad('', $length, '9');
    return str_pad((string) random_int($min, $max), $length, '0', STR_PAD_LEFT);
}

/**
 * Build the HTML email body for the OTP.
 */
function buildOtpEmailHtml($otpCode) {
    $appName = APP_NAME;
    $expiry  = OTP_EXPIRY / 60;

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#0e1525; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0e1525; padding:40px 20px;">
<tr><td align="center">
<table width="460" cellpadding="0" cellspacing="0" style="background-color:#1a2744; border-radius:16px; overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,0.4);">

<!-- Top accent bar -->
<tr><td style="height:4px; background:linear-gradient(90deg,#cc1b2b,#e52535,#8B0000);"></td></tr>

<!-- Logo / Brand -->
<tr><td style="padding:40px 40px 20px; text-align:center;">
    <div style="width:56px; height:56px; background:linear-gradient(135deg,#cc1b2b,#8B0000); border-radius:14px; display:inline-block; line-height:56px; margin-bottom:12px;">
        <span style="color:#fff; font-size:24px; font-weight:bold;">🔑</span>
    </div>
    <h1 style="color:#f0f2f7; font-size:20px; margin:0; letter-spacing:-0.3px;">{$appName}</h1>
    <p style="color:#6b7a96; font-size:13px; margin:6px 0 0;">Password Reset Request</p>
</td></tr>

<!-- Body text -->
<tr><td style="padding:0 40px 10px;">
    <p style="color:#b0b8cc; font-size:14px; line-height:1.6; margin:0 0 24px;">
        We received a request to reset your password. Use the verification code below to proceed.
        This code will expire in <strong style="color:#f0f2f7;">{$expiry} minutes</strong>.
    </p>
</td></tr>

<!-- OTP Code -->
<tr><td style="padding:0 40px 28px; text-align:center;">
    <div style="background:#111a2c; border:2px solid #243352; border-radius:12px; padding:20px; display:inline-block;">
        <span style="font-size:32px; font-weight:800; letter-spacing:10px; color:#e52535; font-family:monospace;">{$otpCode}</span>
    </div>
</td></tr>

<!-- Warning -->
<tr><td style="padding:0 40px 32px;">
    <p style="color:#6b7a96; font-size:12px; line-height:1.5; margin:0;">
        If you didn't request this, you can safely ignore this email. Never share this code with anyone.
    </p>
</td></tr>

<!-- Footer -->
<tr><td style="padding:20px 40px; border-top:1px solid #243352; text-align:center;">
    <p style="color:#4a5568; font-size:11px; margin:0;">© {$appName} · Rapid Emergency Status Checking via Quick Response</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
}
