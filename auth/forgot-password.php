<?php

session_start();
// Use aliases to prevent VS Code "red" errors
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// 1. Load Dependencies
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die("Error: vendor/autoload.php not found. Please run 'composer require phpmailer/phpmailer vlucas/phpdotenv' in your terminal.");
}

// 2. Load Environment Variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// 3. Database Connection
$dbConfigPath = __DIR__ . '/../config/db.php';
if (file_exists($dbConfigPath)) {
    require_once $dbConfigPath;
} else {
    die("Error: Database configuration file not found at $dbConfigPath");
}

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
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            $message = "If an account is associated with this email, a reset link has been sent.";

            if ($stmt->num_rows > 0) {
                $token = bin2hex(random_bytes(32));
                $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

                $update = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
                if ($update) {
                    $update->bind_param("sss", $token, $expiry, $email);
                    
                    if ($update->execute()) {
                        $mail = new PHPMailer(true);
                        try {
                            // --- ADD THESE DEBUG LINES ---
                            $mail->SMTPDebug = 2; 
                            $mail->Debugoutput = 'html';
                            // -----------------------------

                            $mail->isSMTP();
                            $mail->Host       = $_ENV['SMTP_HOST'] ?? '';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
                            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
                            
                            $smtpPort = (int)($_ENV['SMTP_PORT'] ?? 587);
                            $mail->Port = $smtpPort;
                            
                            // Mailtrap specific: Port 2525 and 587 usually use STARTTLS
                            $mail->SMTPSecure = ($smtpPort === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

                            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com', 'System Admin');
                            $mail->addAddress($email);

                            $mail->isHTML(true);
                            $mail->Subject = 'Password Reset Request';
                            $mail->Body    = "<h3>Reset your password</h3><p>Click the link below...</p>";

                            $mail->SMTPOptions = array(
                                'ssl' => array(
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                    'allow_self_signed' => true
                                )
                            );
                            $mail->send();
                            $message = "If an account is associated with this email, a reset link has been sent.";
                        } catch (PHPMailerException $e) {
                            // This will now show you exactly what went wrong!
                            echo "Mailer Error: " . $mail->ErrorInfo;
                            exit; 
                        }
                    }

                    $update->close();
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/forgot-password.css">  
</head>
<body class="login-body">
<div class="login-card">
    <h1>Forgot Password</h1>
    <p class="subtitle">Enter your registered email address to reset your password.</p>
    
    <?php if($message): ?>
        <p style="color:green"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <?php if($error): ?>
        <p style="color:red"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="email" name="email" placeholder="Enter your Email Address" required>
        <button type="submit" class="login-btn" name="reset_request">Send Reset Link</button>
    </form>
    <a href="login.php" class="register-btn">Back to Login</a>
</div>
</body>
</html>
