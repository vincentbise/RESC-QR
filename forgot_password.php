<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - RESC-QR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <div class="login-card">
        <div class="logo">
            <center><img src="logo.png" alt="Logo"></center>
        </div>

        <img src="resc-qr.png" class="logo-name">

        <h1 class="title">Forgot Password</h1>

        <p class="subtitle">Enter your email and we will send a reset link.</p>

        <form action="send_reset.php" method="POST">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email..." required>

            <button class="btn" type="submit">Send Reset Link</button>
        </form>

        <a href="index.php" class="forgot">Back to Login</a>
    </div>

    <div class="right-section">
        <img src="images/background.png" alt="Background">
    </div>

</div>

</body>
</html>