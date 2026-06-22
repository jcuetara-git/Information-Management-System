<?php
session_start();
include("../config/db.php");

$error = "";

if(isset($_POST['login'])){
    $login    = trim($_POST['student_no']);
    $password = trim($_POST['password']);

    // Prepared statement for security
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_no = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_no'] = $user['student_no'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'] ?? '';
            $_SESSION['last_name']  = $user['last_name'] ?? '';

            // Dynamic Path Routing Redirection based on roles
            if($user['role'] == "admin"){
                header("Location: ../admin/admin-dashboard.php");
                exit();
            } elseif($user['role'] == "faculty") {
                header("Location: ../faculty/faculty-dashboard.php");
                exit();
            } else {
                header("Location: ../student/student-dashboard.php");
                exit();
            }
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Account not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=1.3">
</head>
<body class="login-body">

<div class="login-card"> 
    <img src="../assets/logo.png" class="logo" alt="Logo">

    <h1>Welcome back!</h1>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="auth-form">
        <input type="text" name="student_no" placeholder="ID Number or Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <div class="forgot">
            <a href="forgot-password.php">Forgot Password?</a>
        </div>

        <button type="submit" class="login-btn" name="login">Sign In</button>
    </form>

    <p class="register-text">Don't have an account?</p>
    <a href="register.php" class="register-btn">Register</a>
</div>

</body>
</html>