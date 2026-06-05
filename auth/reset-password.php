<?php
session_start();
include("../config/db.php");

$error = "";
$message = "";
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header("Location: forgot-password.php");
    exit();
}

// 1. Verify token and ensure it hasn't expired
$stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ? AND token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $error = "This link is invalid or has expired.";
} else {
    // 2. Handle Password Update
    if (isset($_POST['update_password'])) {
        // CSRF Token Validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF token validation failed.");
        }

        $new_pass = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        if ($new_pass !== $confirm) {
            $error = "Passwords do not match!";
        } elseif (strlen($new_pass) < 12) {
            $error = "Password must be at least 12 characters and include uppercase, lowercase, numbers, and special characters.";
        } elseif (!preg_match('/[A-Z]/', $new_pass) || !preg_match('/[a-z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass) || !preg_match('/[^A-Za-z0-9]/', $new_pass)) {
            $error = "Password must include uppercase, lowercase, numbers, and special characters.";
        } else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            
            // Update password and CLEAR token so it cannot be used again
            $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
            $update->bind_param("ss", $hash, $token);
            
            if ($update->execute()) {
                $_SESSION['success_message'] = "Password updated successfully!";
                header("Location: login.php");
                exit();
            } else {
                $error = "Database error. Please try again later.";
                error_log("Database Error: " . $conn->error);
            }
        }
    }
}

// CSRF Token Generation (if not already set)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/forgot-password.css">
</head>
<body class="login-body">
<div class="login-card">
    <h1>Create New Password</h1>
    
    <?php if($error) echo "<p class='error-msg'>$error</p>"; ?>
    
    <?php if(!$error || $error !== "This link is invalid or has expired."): ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="password" name="password" placeholder="New Password" required minlength="12">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" class="login-btn" name="update_password">Update Password</button>
    </form>
    <?php else: ?>
        <p><a href="forgot-password.php">Request a new reset link</a></p>
    <?php endif; ?>
</div>
</body>
</html>
