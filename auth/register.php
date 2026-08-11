<?php
session_start();
include("../config/db.php");

$error = "";

if(isset($_POST['register'])) {
    $student_no = trim($_POST['student_no']); 
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    
    // 1. Capture and validate the selected role
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'student';
    if (empty($role) || !in_array($role, ['student', 'faculty', 'alumni'])) {
        $role = 'student';
    }
    
    // 2. Explicitly force year_level to PHP null for faculty/alumni so it binds correctly as NULL in SQL
    if ($role === 'faculty' || $role === 'alumni') {
        $year_level = null;
    } else {
        $year_level = !empty($_POST['year_level']) ? trim($_POST['year_level']) : '';
    }
    
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
            // 3. Insert into database
            $stmt = $conn->prepare("INSERT INTO users (student_no, first_name, last_name, email, year_level, password, role) VALUES (?,?,?,?,?,?,?)");
            
            // Note: Since $year_level can be null, bind it with 's' or handle types properly. 
            // In MySQLi, passing a PHP null with string type "s" automatically translates to an SQL NULL.
            $stmt->bind_param("sssssss", $student_no, $first_name, $last_name, $email, $year_level, $hash, $role);

            if($stmt->execute()){
                header("Location: login.php?success=" . urlencode("Account registered successfully!"));
                exit();
            } else {
                $error = "Database Error: " . $stmt->error;
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
        <p class="error" style="color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; font-size: 14px; text-align: center; margin-bottom: 15px;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="auth-form">

        <div class="form-group">
            <select name="role" id="roleSelector" onchange="toggleYearLevel(this.value)" required>
                <option value="" disabled <?= empty($_POST['role']) ? 'selected' : '' ?>>Register As</option>
                <option value="student" <?= (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : '' ?>>Student</option>
                <option value="faculty" <?= (isset($_POST['role']) && $_POST['role'] == 'faculty') ? 'selected' : '' ?>>Faculty Member</option>
                <option value="alumni" <?= (isset($_POST['role']) && $_POST['role'] == 'alumni') ? 'selected' : '' ?>>Alumnus</option>
            </select>
        </div>

        <input type="text" name="student_no" placeholder="ID Number" class="id-input" value="<?= isset($_POST['student_no']) ? htmlspecialchars($_POST['student_no']) : '' ?>" required>

        <div class="two-col">
            <input type="text" name="first_name" placeholder="First Name" value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>" required>
            <input type="text" name="last_name" placeholder="Last Name" value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>" required>
        </div>

        <div class="two-col">
            <input type="email" name="email" placeholder="Email Address" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>

            <select name="year_level" id="yearLevelSelector" required>
                <option value="" disabled <?= empty($_POST['year_level']) ? 'selected' : '' ?>>Year Level</option>
                <option value="1" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '1') ? 'selected' : '' ?>>1</option>
                <option value="2" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '2') ? 'selected' : '' ?>>2</option>
                <option value="3" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '3') ? 'selected' : '' ?>>3</option>
                <option value="4" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '4') ? 'selected' : '' ?>>4</option>
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
    // Disables and styles the year level field when 'faculty' or 'alumni' is selected
    function toggleYearLevel(role) {
        const yearSelector = document.getElementById('yearLevelSelector');
        // Updated to include alumni in the check
        if (role === 'faculty' || role === 'alumni') {
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

    // Run on page load to keep disabled state if form fails and reloads with faculty/alumni selected
    window.onload = function() {
        const roleSelector = document.getElementById('roleSelector');
        if(roleSelector.value) {
            toggleYearLevel(roleSelector.value);
        }
    };
</script>

</body>
</html>