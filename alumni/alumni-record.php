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

// Check if alumni has already added their records
$record_filled = false;
$stmt = $conn->prepare("SELECT id FROM alumni_profile WHERE student_no = ?");
if ($stmt) {
    $stmt->bind_param("s", $student_no);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $record_filled = true;
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
    <title>Alumni Record</title>
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
                <h1>Alumni Record Management</h1>
                <p>Manage, review, and keep your official alumni, career, and board exam records up to date.</p>
            </div>

            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-folder-open" style="font-size: 48px; color: #f4b42a; margin-bottom: 15px;"></i>
                <h3>Record Status: <strong><?= $record_filled ? 'Submitted' : 'No Records Added' ?></strong></h3>
                <p style="color: #64748b; margin: 10px 0 20px 0;">
                    <?= $record_filled ? 'Your alumni records are up to date.' : 'View your alumni record' ?>
                </p>
                
                <?php if (!$record_filled): ?>
                    <button type="button" class="save-btn" onclick="openRecordModal()" style="display: inline-block; padding: 12px 30px; font-size: 15px;">
                        <i class="fa-solid fa-eye"></i> View My Alumni Information
                    </button>
                <?php else: ?>
                    <button type="button" class="save-btn" onclick="openRecordModal()" style="display: inline-block; padding: 10px 24px; font-size: 14px; background: #f4b42a; color: #000;">
                        <i class="fa-solid fa-folder-open"></i> View My Alumni Information
                    </button>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- ================= ALUMNI RECORD MODAL INCLUDE ================= -->
<?php include("alumni-record-modal.php"); ?>

<script>
    // Modal Functions Only (Header Dropdowns are managed globally by header.php)
    function openRecordModal() {
        document.getElementById('recordModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeRecordModal() {
        document.getElementById('recordModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', function(event) {
        const recordModal = document.getElementById('recordModal');
        if (event.target === recordModal) {
            closeRecordModal();
        }
    });
</script>
</body>
</html>