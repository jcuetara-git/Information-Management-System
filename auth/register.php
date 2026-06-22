<?php
session_start();
include("../config/db.php");

$error = "";

if(isset($_POST['register'])) {
    $student_no = trim($_POST['student_no']); // Acts as ID Number for both Roles
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    
    // 1. Capture the selected role and fall back to student if empty or tampered with
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'student';
    if (empty($role) || !in_array($role, ['student', 'faculty'])) {
        $role = 'student';
    }
    
    // 2. Set year level to null for faculty, otherwise capture it for students
    $year_level = ($role === 'faculty') ? null : (isset($_POST['year_level']) ? trim($_POST['year_level']) : '');
    
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    // Validation
    if($password !== $confirm){
        $error = "Passwords do not match!";
    } elseif($role === 'student' && empty($year_level)){
        $error = "Please select a year level!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Check if ID is already taken
        $check = $conn->prepare("SELECT student_no FROM users WHERE student_no = ?");
        $check->bind_param("s", $student_no);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $error = "ID Number is already registered!";
        } else {
            // 3. Insert into database with the explicitly defined role
            $stmt = $conn->prepare("INSERT INTO users (student_no, first_name, last_name, email, year_level, password, role) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $student_no, $first_name, $last_name, $email, $year_level, $hash, $role);

            if($stmt->execute()){
                header("Location: login.php?success=" . urlencode("Account registered successfully!"));
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=1.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="login-body">

<div class="register-card">

    <img src="../assets/logo.png" class="register-logo" alt="Logo">

    <h1 class="register-title">Register</h1>
    <p class="subtitle">Register to get started with your account.</p>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="auth-form">

        <div class="form-group">
            <select name="role" id="roleSelector" onchange="toggleYearLevel(this.value)" required>
                <option value="student" selected>Register as Student</option>
                <option value="faculty">Register as Faculty Member</option>
            </select>
        </div>

        <input type="text" name="student_no" placeholder="ID Number" class="id-input" required>

        <div class="two-col">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
        </div>

        <div class="two-col">
            <input type="email" name="email" placeholder="Email Address" required>

            <select name="year_level" id="yearLevelSelector" required>
                <option value="" disabled selected>Year Level</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
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

        <button type="submit" class="login-btn" name="register">Register</button>

    </form>

    <p class="register-text">Already have an account?</p>
    <a href="login.php" class="register-btn">Sign In</a>

</div>

<script src="../assets/js/script.js"></script>
<script>
    // Disables and styles the year level field when 'faculty' is selected
    function toggleYearLevel(role) {
        const yearSelector = document.getElementById('yearLevelSelector');
        if (role === 'faculty') {
            yearSelector.selectedIndex = 0;
            yearSelector.disabled = true;
            yearSelector.style.opacity = '0.5';
            yearSelector.style.cursor = 'not-allowed';
            yearSelector.removeAttribute('required');
        } else {
            yearSelector.disabled = false;
            yearSelector.style.opacity = '1';
            yearSelector.style.cursor = 'default';
            yearSelector.setAttribute('required', 'required');
        }
    }
</script>

</body>
</html>