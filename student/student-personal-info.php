<?php
include("../config/auth.php");
include("../config/db.php"); 

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';
$last_name  = $_SESSION['last_name'] ?? '';

// Check if student has already filled up personal information
$info_filled = false;
$stmt = $conn->prepare("SELECT id FROM student_profile WHERE student_no = ?");
$stmt->bind_param("s", $student_no);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $info_filled = true;
}
$stmt->close();

// Fetch announcements for dropdown
$announcements = [];
$conn->query("SET time_zone = '+08:00'");
$query = "SELECT title, message, created_at, 
          (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
          FROM announcements 
          WHERE status = 'published' 
          AND (target_audience = 'all' OR target_audience = 'students' OR (target_audience = 'specific_user' AND target_user_id = ?))
          ORDER BY created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['student_no']);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Shared Dashboard Layout CSS -->
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    
    <!-- Externalized Stylesheet for Faculty Personal Info & Modal Layout -->
    <link rel="stylesheet" href="../assets/css/faculty-personal-info.css">
    
    <script>
        function confirmSubmission() {
            return confirm("Are you sure all information is correct and you want to submit your portfolio?");
        }

        function confirmCancel(event) {
            if (!confirm("Are you sure you want to cancel? Any unsaved document attachments will be lost.")) {
                if(event) event.preventDefault(); 
                return false;
            }
            closeModal();
            return true;
        }

        function openModal() {
            document.getElementById('portfolioModalOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('portfolioModalOverlay').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    </script>
</head>
<body>

<div class="dashboard-container">
    <!-- HEADER INCLUDE -->
    <?php include("../includes/header.php"); ?>
    
    <!-- LAYOUT -->
    <div class="dashboard-layout">
        <?php include("../includes/student-sidebar.php"); ?>
        
        <main class="main-content">
            <!-- Welcome Card -->
            <div class="card welcome-card">
                <h2>Personal Information Management</h2>
                <p>Manage and complete your student profile details required by the college.</p>
            </div>

            <!-- Status Card -->
            <div class="card status-card">
                <i class="fa-solid fa-id-card-clip status-icon"></i>
                <h3>Student Profile Status: <strong><?= $info_filled ? 'Completed' : 'Pending Information' ?></strong></h3>
                <p class="status-desc">
                    <?= $info_filled ? 'Your information has been saved successfully.' : 'Please provide your personal, residential, and family background data.' ?>
                </p>
                
                <?php if (!$info_filled): ?>
                    <button type="button" class="save-btn status-btn" onclick="openInfoModal()">
                        <i class="fa-solid fa-plus"></i> Add Information
                    </button>
                <?php else: ?>
                    <p class="success-text"><i class="fa-solid fa-circle-check"></i> Personal Information already submitted.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- ================= ADD INFO MODAL ================= -->
<?php include("student-personal-info-modal.php"); ?>

<script src="../assets/js/script.js"></script>
<script>
    function openInfoModal() {
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
</script>
</body>
</html>