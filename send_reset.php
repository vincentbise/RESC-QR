<?php
<?php

require "PHPMailer/src/Exception.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include "database.php"; 

if (isset($_POST['email'])) {
    $email = $_POST['email'];


    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

   
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

  
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        
        $resetLink = "http://localhost/RESC-QR/reset_password.php?token=" . $token;

        $subject = "Password Reset Request";
        $message = "Click this link to reset your password:\n\n" . $resetLink;
        $headers = "From: no-reply@resc-qr.com";

        mail($email, $subject, $message, $headers);

        echo "Reset link sent to your email!";
    } else {
        echo "Email not found!";
    }
}
?>
