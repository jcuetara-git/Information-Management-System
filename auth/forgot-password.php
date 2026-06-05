<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include("../config/db.php");

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$message = "";
$error = "";

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['reset_request'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $message = "If an account is associated with this email, a reset link has been sent.";

        if ($result->num_rows > 0) {
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $update = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
            $update->bind_param("sss", $token, $expiry, $email);
            
            if ($update->execute()) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = $_ENV['SMTP_HOST'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_ENV['SMTP_USER'];
                    $mail->Password   = $_ENV['SMTP_PASS'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = $_ENV['SMTP_PORT'];

                    $mail->setFrom($_ENV['SMTP_USER'], 'System Admin');
                    $mail->addAddress($email);

                    $link = $_ENV['BASE_URL'] . "reset-password.php?token=" . $token;

                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body    = "<h3>Reset your password</h3>
                                      <p>Click the link below to change your password. This link expires in 1 hour.</p>
                                      <a href='$link'>Click here to reset password</a>";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mailer Error: {$mail->ErrorInfo}");
                }
            }
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
    <?php if($error) echo "<p style='color:red'>$error</p>"; ?>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="email" name="email" placeholder="Enter your Email Address" required>
        <button type="submit" class="login-btn" name="reset_request">Send Reset Link</button>
    </form>
    <a href="login.php" class="register-btn">Back to Login</a>
</div>
</body>
</html>