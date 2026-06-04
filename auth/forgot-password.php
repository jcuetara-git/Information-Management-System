<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; 
include("../config/db.php");

$message = "";
$error = "";

if (isset($_POST['reset_request'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // We show a generic message regardless of email existence to prevent user enumeration
    $message = "If an account is associated with this email, a reset link has been sent.";

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32)); 
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); 

        // Store token in DB
        $update = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'j.cuetara04@gmail.com'; 
            $mail->Password   = 'ylkh csqk edup ejie'; // Use a 16-character App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('j.cuetara04@gmail.com', 'System Admin');
            $mail->addAddress($email);

            // Change 'localhost' to your domain in production
            $link = "http://localhost/ccj-sms/auth/reset-password.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "<h3>Reset your password</h3>
                              <p>Click the link below to change your password. This link expires in 1 hour.</p>
                              <a href='$link'>Click here to reset password</a>";

            $mail->send();
        } catch (Exception $e) {  
            error_log("Mailer Error: {$mail->ErrorInfo}");
            // Optional: $error = "Email could not be sent.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/forgot-password.css">  
</head>
<body class="login-body">
<div class="login-card">
    <h1>Forgot Password</h1>
    <p class="subtitle">Enter your registered email address to reset your password.</p>
    
    <?php if($message) echo "<p style='color:green'>$message</p>"; ?>
    <?php if($error) echo "<p class='error' style='color:red'>$error</p>"; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your Email Address" required>
        <button type="submit" class="login-btn" name="reset_request">Send Reset Link</button>
    </form>
    <a href="login.php" class="register-btn">Back to Login</a>
</div>
</body>
</html>