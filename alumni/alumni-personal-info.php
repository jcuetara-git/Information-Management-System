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
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
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
            <div class="card welcome-card">
                <h1>Alumni Information Management</h1>
                <p>Manage and complete your career and board exam details to update your official alumni record.</p>
            </div>

            <!-- STATUS CARD -->
            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-user-graduate" style="font-size: 48px; color: #f4b42a; margin-bottom: 15px;"></i>
                <h3>Alumni Profile Status: <strong><?= $info_filled ? 'Completed' : 'Pending Information' ?></strong></h3>
                <p style="color: #64748b; margin: 10px 0 20px 0;">
                    <?= $info_filled ? 'Your career and board exam details have been saved successfully.' : 'Please provide your career, board exam, and current job data.' ?>
                </p>
                
                <?php if (!$info_filled): ?>
                    <button type="button" class="save-btn" onclick="openModal()" style="display: inline-block; padding: 12px 30px; font-size: 15px; border: none; border-radius: 6px; background-color: #f4b42a; color: #000; cursor: pointer; font-weight: 600; transition: 0.2s;">
                        <i class="fa-solid fa-plus"></i> Add Information
                    </button>
                <?php else: ?>
                    <p style="color: #10b981; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> Information already submitted.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- ================= ADD INFO MODAL INCLUDE ================= -->
<?php include("alumni-personal-info-modal.php"); ?>

<script>
    // Modal Functions Only (Header Dropdowns are managed globally by header.php)
    function openModal() {
        document.getElementById('infoModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('infoModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('infoModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

</body>
</html>