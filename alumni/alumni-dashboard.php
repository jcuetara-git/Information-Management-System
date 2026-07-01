<?php
include("../config/auth.php");
include("../config/db.php"); 

// Ensure logged in as alumni
if(!isset($_SESSION['role']) || $_SESSION['role'] != "alumni"){
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';
$last_name  = $_SESSION['last_name'] ?? '';

// Check if alumni has already filled up their profile information
$info_filled = false;
if (!empty($student_no)) {
    // Assuming you have an 'alumni_profile' table
    $query = "SELECT id FROM alumni_profile WHERE student_no = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("s", $student_no);
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
    <title>alumni-dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Ensure this CSS path exists or create a copy -->
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
</head>
<body>

<?php if(isset($_GET['success'])): ?>
    <div class="success-overlay">
        <div class="success-box">
            <p>Alumni information saved successfully!</p>
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
        <p>Keep your alumni records updated and stay connected.</p>
    </div>

    <!-- ADD INFO -->
    <?php if ($info_filled): ?>
        <div class="card add-info-card disabled">
            <div class="check-icon"></div>
            <p>Alumni Profile Completed</p>
        </div>
    <?php else: ?>
        <!-- Update this link to point to your new alumni-add-info.php -->
        <a href="alumni-add-info.php" class="add-info-link">
            <div class="card add-info-card">
                <div class="plus-icon"></div>
                <p>Add Alumni Profile</p>
            </div>
        </a>
    <?php endif; ?>

    <!-- VIEW RECORD -->
    <div class="button-container">
        <a href="alumni-view-record.php">
            <button class="view-btn">View My Record</button>
        </a>
    </div>

</div>

<script src="../assets/js/script.js"></script>
</body>
</html>