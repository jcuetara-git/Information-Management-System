<?php
session_start();
include("../config/db.php"); 

// Ensure user is logged in as alumni
if(!isset($_SESSION['role']) || $_SESSION['role'] != "alumni"){
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['firstname'] ?? $_SESSION['first_name'] ?? 'Alumni';
$last_name  = $_SESSION['lastname'] ?? $_SESSION['last_name'] ?? '';
$email      = $_SESSION['email'] ?? '';

// Check if alumni has already filled up their career/board exam information
$info_filled = false;
$stmt = $conn->prepare("SELECT id FROM alumni_profile WHERE student_no = ?");
if ($stmt) {
    $stmt->bind_param("s", $student_no);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $info_filled = true;
    }
    $stmt->close();
}

// Fetch announcements for header dropdown
$announcements = [];
$conn->query("SET time_zone = '+08:00'");
$query = "SELECT title, message, created_at, 
          (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
          FROM announcements 
          WHERE status = 'published' 
          AND (target_audience = 'all' OR target_audience = 'alumni' OR (target_audience = 'specific_user' AND target_user_id = ?))
          ORDER BY created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("s", $student_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Shared Dashboard Layout CSS -->
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    
    <!-- Externalized Stylesheet for Modal Layout -->
    <link rel="stylesheet" href="../assets/css/faculty-personal-info.css">
    
    <script>
        function confirmSubmission() {
            return confirm("Are you sure all information is correct and you want to submit your alumni record?");
        }

        function confirmCancel(event) {
            if (!confirm("Are you sure you want to cancel? Any unsaved changes will be lost.")) {
                if(event) event.preventDefault(); 
                return false;
            }
            closeModal();
            return true;
        }

        function openModal() {
            document.getElementById('infoModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('infoModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function confirmSave() {
            return confirm("Are you sure you want to save this information?");
        }

        window.onclick = function(event) {
            const modal = document.getElementById('infoModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</head>
<body>

<div class="dashboard-container">
    <!-- HEADER INCLUDE -->
    <?php include("../includes/header.php"); ?>
    
    <!-- LAYOUT -->
    <div class="dashboard-layout">
        <!-- SIDEBAR INCLUDE -->
        <?php include("../includes/alumni-sidebar.php"); ?>
        
        <main class="main-content">
            <!-- Welcome Card -->
            <div class="card welcome-card">
                <h2>Alumni Information Management</h2>
                <p>Manage and complete your career and board exam details to update your official alumni record.</p>
            </div>

            <!-- Status Card -->
            <div class="card status-card">
                <i class="fa-solid fa-id-card-clip status-icon"></i>
                <h3>Profile Status: <strong><?= $info_filled ? 'Completed' : 'Pending Information' ?></strong></h3>
                <p class="status-desc">
                    <?= $info_filled ? 'Your career and board exam details have been saved successfully.' : 'Please provide your career, board exam, and current job data.' ?>
                </p>
                
                <?php if (!$info_filled): ?>
                    <button type="button" class="save-btn status-btn" onclick="openModal()">
                        <i class="fa-solid fa-plus"></i> Add Alumni Information
                    </button>
                <?php else: ?>
                    <p class="success-text"><i class="fa-solid fa-circle-check"></i> Information already submitted.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- ================= ADD INFO MODAL ================= -->
<?php include("alumni-personal-info-modal.php"); ?>

<script src="../assets/js/script.js"></script>
</body>
</html>