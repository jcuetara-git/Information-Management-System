<?php
include("../config/db.php");

$error = "";
$message = "";
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Invalid access. No token provided.");
}

// 1. Verify token and ensure it hasn't expired
$stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ? AND token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("This link is invalid or has expired.");
}

// 2. Handle Password Update
if (isset($_POST['update_password'])) {
    $new_pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($new_pass !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (strlen($new_pass) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        
        // Update password and CLEAR token so it cannot be used again
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $hash, $token);
        
        if ($update->execute()) {
            echo "<script>alert('Password updated successfully!'); window.location='login.php';</script>";
            exit();
        } else {
            $error = "Database error. Please try again later.";
        }
    }
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
    
    <?php if($error) echo "<p class='error' style='color:red'>$error</p>"; ?>
    
    <form method="POST">
        <input type="password" name="password" placeholder="New Password" required minlength="8">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" class="login-btn" name="update_password">Update Password</button>
    </form>
</div>
</body>
</html>