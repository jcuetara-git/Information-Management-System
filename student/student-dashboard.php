<?php
include("../config/auth.php");

// Ensure logged in as student
if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../auth/login.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? '';
$last_name  = $_SESSION['last_name'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
<title>student-dashboard</title>
<link rel="stylesheet" href="../assets/css/student-dashboard.css">
</head>

<body>

<?php if(isset($_GET['success'])): ?>
    <div class="success-overlay">
        <div class="success-box">
            <p>Student information saved successfully!</p>
            <button onclick="closeSuccess()">OK</button>
        </div>
    </div>
<?php endif; ?>

<div class="dashboard-container">

    <!-- HEADER -->
    <div class="logo-section">

        <div class="logo-left">
            <div class="logo-circle">
                <img src="../assets/logo.png">
            </div>

            <div class="logo-text">
                <h2>College of Criminal Justice</h2>
                <p>Center of Development in Criminology</p>
            </div>
        </div>

        <div class="profile-menu">
            <div class="profile-icon" onclick="toggleMenu()">👤</div>

            <div class="profile-dropdown" id="profileDropdown">
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>

    </div>

    <!-- WELCOME -->
    <div class="card welcome-card">
        <h1>Welcome, <?= htmlspecialchars($first_name); ?>!</h1>
        <p>View your profile and manage your account.</p>
    </div>

    <!-- ADD INFO -->
    <a href="student-add-info.php" class="add-info-link">
        <div class="card add-info-card">
            <div class="plus-icon">+</div>
            <p>Add Personal Information</p>
        </div>
    </a>

    <!-- VIEW RECORD -->
    <div class="button-container">
        <a href="student-view-record.php">
            <button class="view-btn">View Record</button>
        </a>
    </div>

</div>

<script src="../assets/js/script.js"></script>

</body>
</html>

