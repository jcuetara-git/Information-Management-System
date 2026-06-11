<?php

session_start();
include("../config/db.php");

// Check if database connection exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Error: Database connection variable (\$conn) is not properly initialized in db.php.");
}

$message = "";
$error = "";
$token = $_GET['token'] ?? $_POST['token'] ?? '';

// 1. Validate the Token
if (empty($token)) {
    die("Invalid or missing reset token.");
}

$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("This password reset link is invalid or has expired.");
}
$stmt->close();

// 2. Handle Password Reset Form Submission
if (isset($_POST['reset_password'])) {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear the token
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $hashed_password, $token);

        if ($update->execute()) {
            $message = "Your password has been successfully reset. You can now login.";
            // Optional: Redirect to login after 3 seconds
            // header("refresh:3;url=login.php");
        } else {
            $error = "Something went wrong. Please try again.";
        }
        $update->close();
    }
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/forgot-password.css">  
</head>
<body class="login-body">
<div class="login-card">
    <h1>Reset Password</h1>
    <p class="subtitle">Enter your new password below.</p>

    <?php if($message): ?>
        <p style="color:green"><?php echo htmlspecialchars($message); ?></p>
        <a href="login.php" class="login-btn" style="display:block; text-align:center; text-decoration:none; margin-top:10px;">Go to Login</a>
    <?php else: ?>
        <?php if($error): ?>
            <p style="color:red"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="reset-password.php?token=<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <input type="password" name="password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            
            <button type="submit" class="login-btn" name="reset_password">Reset Password</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
