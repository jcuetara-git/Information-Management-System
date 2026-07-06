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
    <title>student-dashboard</title>
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
        <p>View announcements, add your personal information and view your record.</p>
    </div>

   <!-- ANNOUNCEMENTS FEED -->
    <section class="card announcements-section">
        <h2 style="margin-bottom: 15px;"><i class="fa-solid fa-bullhorn" style="color: #f3b12b; margin-right: 0;"></i> Recent Announcements</h2>
        
        <?php
        // Your Query
        $query = "SELECT title, message, created_at, 
                  (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
                  FROM announcements 
                  WHERE status = 'published' 
                  AND (target_audience = 'all' OR target_audience = 'students' OR (target_audience = 'specific_user' AND target_user_id = ?))
                  ORDER BY created_at DESC LIMIT 5";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $_SESSION['student_no']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0): 
            while ($row = $result->fetch_assoc()): ?>
                <div class="announcement-item" style="border-bottom: 1px solid #eee; padding: 10px 0;">
                    <h3 style="font-size: 16px; margin: 0;">
                        <?= htmlspecialchars($row['title']) ?>
                        <?php if ($row['is_new']): ?>
                            <span style="background: #f4b42c; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 5px;">NEW</span>
                        <?php endif; ?>
                    </h3>
                    <p style="font-size: 14px; color: #666; margin: 5px 0;"><?= htmlspecialchars($row['message']) ?></p>
                </div>
            <?php endwhile; 
        else: ?>
            <p style='color:#777;'>No new announcements at this time.</p>
        <?php endif; ?>
    </section>
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

    <div class="button-container">
        <button class="view-btn" onclick="window.location.href='student-view-record.php'">
            <i class="fa-solid fa-file-lines"></i> View Record
        </button>
    </div>
</div>

<script src="../assets/js/script.js"></script>

</body>
</html>
