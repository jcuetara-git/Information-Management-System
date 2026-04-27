<?php
include("../config/db.php");

$error = "";

// Check if form is submitted
if(isset($_POST['register'])) {

    $student_no = trim($_POST['student_no']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $year_level = trim($_POST['year_level']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    // Check if passwords match 
    if($password !== $confirm){
        $error = "Passwords do not match!";
    } elseif(empty($year_level)){
        $error = "Please select a year level!";
    } else {

        // Hash the password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = "student"; // default role

        // Check if student ID already exists
        $check = $conn->prepare("SELECT student_no FROM users WHERE student_no = ?");
        $check->bind_param("s", $student_no);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $error = "Student ID already registered!";
        } else {

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (student_no, first_name, last_name, email, year_level, password, role) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss",
                $student_no,
                $first_name,
                $last_name,
                $email,
                $year_level,
                $hash,
                $role
            );

            if($stmt->execute()){
                // Redirect to login after successful registration
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="login-body">

<div class="register-card">

    <img src="../assets/logo.png" class="register-logo">

    <h1 class="register-title">Register</h1>
    <p class="subtitle">Register to get started with your account.</p>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">

        <input type="text" name="student_no" placeholder="ID Number" class="id-input" required>

        <div class="two-col">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
        </div>

        <div class="two-col">
            <input type="email" name="email" placeholder="Email Address" required>

            <select name="year_level" required>
                <option value="" disabled selected>Year Level</option>
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
            </select>
        </div>

       <div class="password-field">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span onclick="toggle('password', this)">
                <i class="fa-solid fa-eye"></i>
            </span>
        </div>

        <div class="password-field">
             <input type="password" name="confirm_password" id="confirm" placeholder="Confirm Password" required>
             <span onclick="toggle('confirm', this)">
                <i class="fa-solid fa-eye"></i>
            </span>
        </div>

        <button class="login-btn" name="register">Register</button>

    </form>

    <p class="register-text">Already have an account?</p>
    <a href="login.php" class="register-btn">Sign In</a>

</div>

<script src="../assets/js/script.js"></script>

</body>
</html>