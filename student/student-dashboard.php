<?php
include("../config/auth.php");
include("../config/db.php"); 

// Ensure logged in as student
if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';
$last_name  = $_SESSION['last_name'] ?? '';

// Check if student has already filled up personal information
$info_filled = false;
if (!empty($student_no)) {
    $query = "SELECT id FROM student_profile WHERE student_no = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("s", $student_no); // student_no is a varchar/string
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $info_filled = true;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>student dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            <a href="../auth/logout.php">
                <i class="fa-solid fa-sign-out-alt"></i> 
                <span class="logout-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- WELCOME -->
    <div class="card welcome-card">
        <h1>Welcome, <?= htmlspecialchars($first_name); ?>!</h1>
        <p>Manage your personal information and view your record.</p>
    </div>

    <!-- ADD INFO -->
    <?php if ($info_filled): ?>
        <div class="card add-info-card disabled">
            <div class="check-icon"></div>
            <p>Personal Information Completed</p>
        </div>
    <?php else: ?>
        <a href="student-add-info.php" class="add-info-link">
            <div class="card add-info-card">
                <div class="plus-icon"></div>
                <p>Add Personal Information</p>
            </div>
        </a>
    <?php endif; ?>

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
